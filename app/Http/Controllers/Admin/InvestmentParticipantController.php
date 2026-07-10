<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Services\InvestmentAccrualService;
use App\Services\InvestmentPlanCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestmentParticipantController extends Controller
{
    public function store(Request $request, Investment $investment): RedirectResponse
    {
        $this->authorize('manageParticipants', $investment);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);

        $contribution = $this->planContributionAmount($investment);
        $tagged = 0;

        DB::transaction(function () use ($investment, $validated, $contribution, &$tagged): void {
            foreach ($validated['user_ids'] as $userId) {
                InvestmentParticipant::updateOrCreate(
                    [
                        'investment_id' => $investment->id,
                        'user_id' => (int) $userId,
                    ],
                    [
                        'contribution_amount' => $contribution,
                    ]
                );

                $tagged++;
            }
        });

        $this->activateInvestmentIfDraft($investment);
        $this->syncPlanTotals($investment);
        $this->syncAccrualsIfEligible($investment);

        return back()->with('status', __('Tagged :count investor(s) at :amount each.', [
            'count' => $tagged,
            'amount' => $contribution,
        ]));
    }

    public function tagAllInvestors(Request $request, Investment $investment): RedirectResponse
    {
        $this->authorize('manageParticipants', $investment);

        $contribution = $this->planContributionAmount($investment);

        $alreadyTaggedIds = $investment->participants()->pluck('user_id')->all();

        $investorIds = User::query()
            ->where('is_active', true)
            ->whereNotIn('id', $alreadyTaggedIds)
            ->pluck('id');

        if ($investorIds->isEmpty()) {
            $this->activateInvestmentIfDraft($investment);
            $this->syncPlanTotals($investment);
            $this->syncAccrualsIfEligible($investment);

            return back()->with('status', __('No additional investors to tag.'));
        }

        DB::transaction(function () use ($investment, $investorIds, $contribution): void {
            foreach ($investorIds as $userId) {
                InvestmentParticipant::create([
                    'investment_id' => $investment->id,
                    'user_id' => $userId,
                    'contribution_amount' => $contribution,
                ]);
            }
        });

        $this->activateInvestmentIfDraft($investment);
        $this->syncPlanTotals($investment);
        $this->syncAccrualsIfEligible($investment);

        return back()->with('status', __('Tagged :count investors at :amount each.', [
            'count' => $investorIds->count(),
            'amount' => $contribution,
        ]));
    }

    public function update(Request $request, Investment $investment, InvestmentParticipant $participant): RedirectResponse
    {
        $this->authorize('manageParticipants', $investment);

        if ($participant->investment_id !== $investment->id) {
            abort(404);
        }

        $validated = $request->validate([
            'contribution_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $participant->update($validated);

        $this->syncPlanTotals($investment);
        $this->syncAccrualsIfEligible($investment);

        return back()->with('status', 'Participant updated.');
    }

    public function destroy(Investment $investment, InvestmentParticipant $participant): RedirectResponse
    {
        $this->authorize('manageParticipants', $investment);

        if ($participant->investment_id !== $investment->id) {
            abort(404);
        }

        $participant->delete();

        $investment->refresh();
        if ($investment->participants()->doesntExist()) {
            $investment->periods()->delete();
            $this->syncPlanTotals($investment);

            return back()->with('status', 'Participant removed. Cleared posted accruals because no investors remain.');
        }

        $this->syncPlanTotals($investment);
        $this->syncAccrualsIfEligible($investment);

        return back()->with('status', 'Participant removed.');
    }

    private function planContributionAmount(Investment $investment): string
    {
        if ($investment->contribution_per_investor === null || (float) $investment->contribution_per_investor <= 0) {
            throw ValidationException::withMessages([
                'contribution' => __('Set contribution per investor on the investment plan before tagging.'),
            ]);
        }

        return number_format((float) $investment->contribution_per_investor, 2, '.', '');
    }

    private function activateInvestmentIfDraft(Investment $investment): void
    {
        $investment->refresh();

        if ($investment->status === Investment::STATUS_DRAFT) {
            $investment->update(['status' => Investment::STATUS_ACTIVE]);
        }
    }

    private function syncPlanTotals(Investment $investment): void
    {
        app(InvestmentPlanCalculationService::class)->syncStoredTotals($investment->fresh());
    }

    private function syncAccrualsIfEligible(Investment $investment): void
    {
        if (! config('investment.auto_accrue_on_save', true)) {
            return;
        }

        $investment->refresh();

        if ($investment->status !== Investment::STATUS_ACTIVE || ! $investment->is_active) {
            return;
        }

        if ($investment->participants()->doesntExist()) {
            return;
        }

        app(InvestmentAccrualService::class)->syncMonthsThrough($investment, $investment->lastAccrualMonthInclusive());
    }
}

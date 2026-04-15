<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Services\InvestmentAccrualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestmentParticipantController extends Controller
{
    public function store(Request $request, Investment $investment): RedirectResponse
    {
        $this->authorize('manageParticipants', $investment);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'contribution_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        InvestmentParticipant::updateOrCreate(
            [
                'investment_id' => $investment->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'contribution_amount' => $validated['contribution_amount'],
            ]
        );

        $this->activateInvestmentIfDraft($investment);
        $this->syncAccrualsIfEligible($investment);

        return back()->with('status', 'Participant saved.');
    }

    public function tagAllInvestors(Request $request, Investment $investment): RedirectResponse
    {
        $this->authorize('manageParticipants', $investment);

        $validated = $request->validate([
            'bulk_contribution_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $alreadyTaggedIds = $investment->participants()->pluck('user_id')->all();

        $investorIds = User::query()
            ->whereNotIn('id', $alreadyTaggedIds)
            ->pluck('id');

        if ($investorIds->isEmpty()) {
            $this->activateInvestmentIfDraft($investment);
            $this->syncAccrualsIfEligible($investment);

            return back()->with('status', __('No additional investors to tag. Everyone is already on this investment.'));
        }

        $amount = $validated['bulk_contribution_amount'];

        DB::transaction(function () use ($investment, $investorIds, $amount): void {
            foreach ($investorIds as $userId) {
                InvestmentParticipant::create([
                    'investment_id' => $investment->id,
                    'user_id' => $userId,
                    'contribution_amount' => $amount,
                ]);
            }
        });

        $this->activateInvestmentIfDraft($investment);
        $this->syncAccrualsIfEligible($investment);

        return back()->with('status', __('Tagged :count investors at once.', ['count' => $investorIds->count()]));
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

        return back()->with('status', 'Participant removed.');
    }

    /**
     * Tagged investors should only appear on active pools: promote draft → active when someone is tagged.
     */
    private function activateInvestmentIfDraft(Investment $investment): void
    {
        $investment->refresh();

        if ($investment->status === Investment::STATUS_DRAFT) {
            $investment->update(['status' => Investment::STATUS_ACTIVE]);
        }
    }

    /**
     * Ensures missing months are recorded after participant changes (especially draft → active),
     * in addition to the model listeners in AppServiceProvider.
     */
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

        app(InvestmentAccrualService::class)->syncMonthsThrough($investment, Investment::accrualThroughInclusive());
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use App\Services\InvestmentAccrualService;
use Carbon\Carbon;
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
            'include_previous_profit' => ['nullable', 'boolean'],
            'profit_carry_since_month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $sinceMonth = $this->resolveProfitCarrySinceMonth($validated['profit_carry_since_month'] ?? null);

        $carryForwardProfit = $request->boolean('include_previous_profit')
            ? $this->cumulativeProfitShareForUser((int) $validated['user_id'], $investment->id, $sinceMonth)
            : 0.0;

        $finalContribution = (float) $validated['contribution_amount'] + $carryForwardProfit;

        InvestmentParticipant::updateOrCreate(
            [
                'investment_id' => $investment->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'contribution_amount' => number_format($finalContribution, 2, '.', ''),
            ]
        );

        $this->activateInvestmentIfDraft($investment);
        $this->syncAccrualsIfEligible($investment);

        $status = $carryForwardProfit > 0
            ? __('Participant saved. Added cumulative rolled-in profit :amount to contribution.', [
                'amount' => number_format($carryForwardProfit, 2, '.', ''),
            ])
            : __('Participant saved.');

        return back()->with('status', $status);
    }

    public function tagAllInvestors(Request $request, Investment $investment): RedirectResponse
    {
        $this->authorize('manageParticipants', $investment);

        $validated = $request->validate([
            'bulk_contribution_amount' => ['required', 'numeric', 'min:0.01'],
            'profit_carry_since_month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $sinceMonth = $this->resolveProfitCarrySinceMonth($validated['profit_carry_since_month'] ?? null);

        $alreadyTaggedIds = $investment->participants()->pluck('user_id')->all();

        $investorIds = User::query()
            ->whereNotIn('id', $alreadyTaggedIds)
            ->pluck('id');

        if ($investorIds->isEmpty()) {
            $this->activateInvestmentIfDraft($investment);
            $this->syncAccrualsIfEligible($investment);

            return back()->with('status', __('No additional investors to tag. Everyone is already on this investment.'));
        }

        $baseAmount = (float) $validated['bulk_contribution_amount'];

        DB::transaction(function () use ($investment, $investorIds, $baseAmount, $sinceMonth): void {
            foreach ($investorIds as $userId) {
                $carryForwardProfit = $this->cumulativeProfitShareForUser((int) $userId, $investment->id, $sinceMonth);
                $finalContribution = $baseAmount + $carryForwardProfit;

                InvestmentParticipant::create([
                    'investment_id' => $investment->id,
                    'user_id' => $userId,
                    'contribution_amount' => number_format($finalContribution, 2, '.', ''),
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

        $investment->refresh();
        if ($investment->participants()->doesntExist()) {
            // No tagged investors means the pool has no principal, so posted accrual
            // snapshots are stale and should be cleared.
            $investment->periods()->delete();

            return back()->with('status', 'Participant removed. Cleared posted accruals because no investors remain.');
        }

        $this->syncAccrualsIfEligible($investment);

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

        app(InvestmentAccrualService::class)->syncMonthsThrough($investment, $investment->lastAccrualMonthInclusive());
    }

    private function resolveProfitCarrySinceMonth(?string $requestMonth): ?Carbon
    {
        $raw = $requestMonth ?: config('investment.profit_carry_since_month');
        if ($raw === null || $raw === '') {
            return null;
        }

        return Carbon::createFromFormat('Y-m', $raw)->startOfMonth();
    }

    /**
     * Sum of posted profit shares for this user on other investments from $sinceMonth
     * (inclusive). When $sinceMonth is null, all posted months on other investments count.
     */
    private function cumulativeProfitShareForUser(int $userId, int $excludeInvestmentId, ?Carbon $sinceMonth): float
    {
        $onlyActive = (bool) config('investment.profit_carry_only_active_investments', true);

        $sum = InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->whereHas('period', function ($q) use ($excludeInvestmentId, $sinceMonth, $onlyActive): void {
                $q->where('investment_id', '!=', $excludeInvestmentId);
                if ($sinceMonth !== null) {
                    $q->whereDate('month', '>=', $sinceMonth);
                }
                if ($onlyActive) {
                    $q->whereHas('investment', fn ($inv) => $inv->where('is_active', true));
                }
            })
            ->sum('profit_share');

        return (float) $sum;
    }
}

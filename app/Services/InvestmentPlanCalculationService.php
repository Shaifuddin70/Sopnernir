<?php

namespace App\Services;

use App\Models\Investment;

class InvestmentPlanCalculationService
{
    public function principalForInvestment(Investment $investment): float
    {
        $investment->loadMissing('participants');

        return (float) $investment->participants->sum('contribution_amount');
    }

    /**
     * Push the plan “contribution each” amount onto every tagged investor row.
     *
     * @return int Number of participant rows updated
     */
    public function syncParticipantContributionsFromPlan(Investment $investment): int
    {
        if ($investment->contribution_per_investor === null || (float) $investment->contribution_per_investor <= 0) {
            return 0;
        }

        $amount = number_format((float) $investment->contribution_per_investor, 2, '.', '');

        return $investment->participants()->update([
            'contribution_amount' => $amount,
        ]);
    }

    public function syncStoredTotals(Investment $investment): Investment
    {
        $principal = $this->principalForInvestment($investment);

        $investment->update([
            'total_invested_amount' => number_format($principal, 2, '.', ''),
        ]);

        return $investment->fresh();
    }
}

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

    public function syncStoredTotals(Investment $investment): Investment
    {
        $principal = $this->principalForInvestment($investment);

        $investment->update([
            'total_invested_amount' => number_format($principal, 2, '.', ''),
        ]);

        return $investment->fresh();
    }
}

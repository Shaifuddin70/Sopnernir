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

    public function projectedTotalProfit(Investment $investment, ?float $principal = null): float
    {
        $principal ??= $this->principalForInvestment($investment);
        if ($principal <= 0) {
            return 0.0;
        }

        $rate = (float) $investment->default_monthly_rate_pct;
        $months = $investment->planAccrualMonthCount();

        if ($months <= 0 || $rate <= 0) {
            return 0.0;
        }

        return round($principal * ($rate / 100) * $months, 2);
    }

    public function syncStoredTotals(Investment $investment): Investment
    {
        $principal = $this->principalForInvestment($investment);
        $totalProfit = $this->projectedTotalProfit($investment, $principal);

        $investment->update([
            'total_invested_amount' => number_format($principal, 2, '.', ''),
            'total_profit_amount' => number_format($totalProfit, 2, '.', ''),
        ]);

        return $investment->fresh();
    }
}

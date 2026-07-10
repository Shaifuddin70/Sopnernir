<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorPortfolioProfitTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_lists_profit_by_month_and_per_investment_total(): void
    {
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Alpha Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $investor->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '500.00',
        ]);

        $period = InvestmentPeriod::create([
            'investment_id' => $investment->id,
            'month' => '2026-02-01',
            'applied_rate_pct' => '1.5000',
            'principal_snapshot' => '1000.00',
            'profit_amount' => '15.00',
        ]);

        InvestmentPeriodUser::create([
            'investment_period_id' => $period->id,
            'user_id' => $investor->id,
            'contribution_snapshot' => '500.00',
            'profit_share' => '7.50',
        ]);

        $this->actingAs($investor);

        $this->get(route('investments.index'))
            ->assertOk()
            ->assertSee(__('Investment summary'), false)
            ->assertSee(__('Posted profit by month'), false)
            ->assertSee('Alpha Pool', false)
            ->assertSee('7.50', false);

        $this->get(route('investments.show', $investment))
            ->assertOk()
            ->assertSee(__('My profit by month (this pool)'), false)
            ->assertSee('7.50', false);
    }

    public function test_portfolio_lists_pools_when_tagged_but_no_accruals_yet(): void
    {
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Beta Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $investor->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '500.00',
        ]);

        $this->actingAs($investor);

        $this->get(route('investments.index'))
            ->assertOk()
            ->assertSee(__('Investment summary'), false);
    }
}

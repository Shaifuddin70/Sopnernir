<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestorMonthlyPayment;
use App\Models\User;
use App\Services\InvestmentPlanCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentPlanCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_profit_is_calculated_from_rate_and_plan_months(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Rate pool',
            'default_monthly_rate_pct' => '2.0000',
            'contribution_per_investor' => '5000.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-03-31',
            'total_profit_amount' => '0.00',
            'total_invested_amount' => '0.00',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '5000.00',
        ]);

        app(InvestmentPlanCalculationService::class)->syncStoredTotals($investment->fresh());

        $investment->refresh();
        $this->assertSame('5000.00', $investment->total_invested_amount);
        // 5000 × 2% × 3 months = 300.00
        $this->assertSame('300.00', $investment->total_profit_amount);
    }

    public function test_creating_investment_calculates_totals_after_tagging_paid_investors(): void
    {
        $admin = User::factory()->admin()->create();
        $paidInvestor = User::factory()->create();

        InvestorMonthlyPayment::create([
            'user_id' => $paidInvestor->id,
            'month' => '2026-06-01',
            'paid_at' => now(),
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'Auto totals pool',
            'deed_no' => 'AUTO-001',
            'total_invested_amount' => '99999.00',
            'contribution_per_investor' => '10000.00',
            'default_monthly_rate_pct' => '1.5',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-08-31',
            'status' => 'draft',
            'is_active' => '1',
            'payment_month' => '2026-06',
        ])->assertRedirect();

        $investment = Investment::query()->where('title', 'Auto totals pool')->first();
        $this->assertNotNull($investment);
        $this->assertSame('10000.00', $investment->total_invested_amount);
        // 10000 × 1.5% × 3 months = 450.00
        $this->assertSame('450.00', $investment->total_profit_amount);
    }
}

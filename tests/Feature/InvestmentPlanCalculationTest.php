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

    public function test_sync_stores_invested_total_and_preserves_planned_profit(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Total profit pool',
            'default_monthly_rate_pct' => '0',
            'contribution_per_investor' => '5000.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-03-31',
            'total_profit_amount' => '30000.00',
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
        $this->assertSame('30000.00', $investment->total_profit_amount);
    }

    public function test_creating_investment_stores_total_profit_and_syncs_invested_after_tagging(): void
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
            'contribution_per_investor' => '10000.00',
            'total_profit_amount' => '45000.00',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-08-31',
            'status' => 'draft',
            'is_active' => '1',
            'payment_month' => '2026-06',
        ])->assertRedirect();

        $investment = Investment::query()->where('title', 'Auto totals pool')->first();
        $this->assertNotNull($investment);
        $this->assertSame('10000.00', $investment->total_invested_amount);
        $this->assertSame('45000.00', $investment->total_profit_amount);
        $this->assertSame('0.0000', $investment->default_monthly_rate_pct);
    }

    public function test_editing_contribution_each_updates_tagged_investor_amounts(): void
    {
        $admin = User::factory()->admin()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Sync contribution pool',
            'deed_no' => 'SYNC-001',
            'default_monthly_rate_pct' => '0',
            'contribution_per_investor' => '5000.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
            'total_profit_amount' => '12000.00',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $first->id,
            'contribution_amount' => '5000.00',
        ]);
        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $second->id,
            'contribution_amount' => '5000.00',
        ]);

        $this->actingAs($admin)->patch(route('admin.investments.update', $investment), [
            'title' => 'Sync contribution pool',
            'deed_no' => 'SYNC-001',
            'notes' => '',
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
            'total_profit_amount' => '12000.00',
            'contribution_per_investor' => '7500.00',
            'status' => 'active',
            'is_active' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $investment->refresh()->load('participants');

        $this->assertSame('7500.00', (string) $investment->contribution_per_investor);
        $this->assertTrue($investment->participants->every(
            fn ($participant) => (string) $participant->contribution_amount === '7500.00'
        ));
        $this->assertSame('15000.00', (string) $investment->total_invested_amount);
    }
}

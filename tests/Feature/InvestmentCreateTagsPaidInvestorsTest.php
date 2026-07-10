<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestorMonthlyPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentCreateTagsPaidInvestorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_investment_tags_only_investors_paid_for_selected_month(): void
    {
        $admin = User::factory()->admin()->create();
        $paidInvestor = User::factory()->create(['email' => 'paid@example.com']);
        $unpaidInvestor = User::factory()->create(['email' => 'unpaid@example.com']);

        InvestorMonthlyPayment::create([
            'user_id' => $paidInvestor->id,
            'month' => '2026-06-01',
            'paid_at' => now(),
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'June pool',
            'deed_no' => 'JUNE-001',
            'notes' => null,
            'contribution_per_investor' => '5000.00',
            'default_monthly_rate_pct' => '1.5',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-12-31',
            'status' => 'draft',
            'is_active' => '1',
            'payment_month' => '2026-06',
        ])->assertRedirect();

        $investment = Investment::query()->where('title', 'June pool')->first();
        $this->assertNotNull($investment);
        $this->assertSame('2026-06-01', $investment->tagged_payment_month?->toDateString());

        $taggedIds = InvestmentParticipant::query()
            ->where('investment_id', $investment->id)
            ->pluck('user_id')
            ->all();

        $this->assertContains($paidInvestor->id, $taggedIds);
        $this->assertNotContains($unpaidInvestor->id, $taggedIds);
        $this->assertSame(Investment::STATUS_ACTIVE, $investment->status);
    }

    public function test_payment_month_is_required_when_creating_investment(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'Missing month pool',
            'deed_no' => 'NO-MONTH',
            'contribution_per_investor' => '5000.00',
            'default_monthly_rate_pct' => '1.5',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-12-31',
            'status' => 'draft',
            'is_active' => '1',
        ])->assertSessionHasErrors('payment_month');
    }
}

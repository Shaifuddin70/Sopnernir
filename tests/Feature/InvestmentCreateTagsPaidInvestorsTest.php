<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestorMonthlyPayment;
use App\Models\SpecialInstallment;
use App\Models\SpecialInstallmentPayment;
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
            'total_profit_amount' => '30000.00',
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
            'total_profit_amount' => '30000.00',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-12-31',
            'status' => 'draft',
            'is_active' => '1',
        ])->assertSessionHasErrors('payment_month');
    }

    public function test_creating_investment_from_special_installment_tags_paid_investors(): void
    {
        $admin = User::factory()->admin()->create();
        $paidInvestor = User::factory()->create(['email' => 'special-paid@example.com']);
        $unpaidInvestor = User::factory()->create(['email' => 'special-unpaid@example.com']);

        $installment = SpecialInstallment::query()->create([
            'title' => 'Eid collection',
            'amount' => '1500.00',
            'created_by' => $admin->id,
        ]);

        SpecialInstallmentPayment::query()->create([
            'special_installment_id' => $installment->id,
            'user_id' => $paidInvestor->id,
            'paid_at' => now(),
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.investments.index', [
                'new' => 1,
                'special_installment' => $installment->id,
            ]))
            ->assertOk()
            ->assertSee('Eid collection', false)
            ->assertSee('1500.00', false)
            ->assertSee(__('From special installment'), false);

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'Eid pool',
            'deed_no' => 'EID-001',
            'notes' => null,
            'contribution_per_investor' => '1500.00',
            'total_profit_amount' => '12000.00',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-12-31',
            'status' => 'draft',
            'is_active' => '1',
            'special_installment_id' => $installment->id,
        ])->assertRedirect();

        $investment = Investment::query()->where('title', 'Eid pool')->first();
        $this->assertNotNull($investment);
        $this->assertSame($installment->id, $investment->special_installment_id);
        $this->assertNull($investment->tagged_payment_month);

        $taggedIds = InvestmentParticipant::query()
            ->where('investment_id', $investment->id)
            ->pluck('user_id')
            ->all();

        $this->assertContains($paidInvestor->id, $taggedIds);
        $this->assertNotContains($unpaidInvestor->id, $taggedIds);
        $this->assertSame(Investment::STATUS_ACTIVE, $investment->status);
    }
}

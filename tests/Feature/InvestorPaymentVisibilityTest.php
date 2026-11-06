<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestorMonthlyPayment;
use App\Models\MonthlyPaymentDefault;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorPaymentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_month_shows_the_investment_amount(): void
    {
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'March Pool',
            'deed_no' => '300',
            'contribution_per_investor' => '5000.00',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'is_active' => true,
            'created_by' => $investor->id,
            'period_start' => '2026-03-01',
            'tagged_payment_month' => '2026-03-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '5000.00',
        ]);

        InvestorMonthlyPayment::create([
            'user_id' => $investor->id,
            'month' => '2026-03-01',
            'paid_at' => '2026-03-06 10:00:00',
            'recorded_by' => $investor->id,
        ]);

        MonthlyPaymentDefault::create([
            'month' => '2026-03-01',
            'amount' => '1500.00',
        ]);

        $this->actingAs($investor)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('March 2026', false)
            ->assertSee('1,500.00', false)
            ->assertDontSee('March Pool', false)
            ->assertDontSee('5,000.00', false);
    }

    public function test_paid_month_shows_without_a_pool(): void
    {
        $investor = User::factory()->create();

        InvestorMonthlyPayment::create([
            'user_id' => $investor->id,
            'month' => '2026-04-01',
            'paid_at' => '2026-04-20 10:00:00',
            'recorded_by' => $investor->id,
        ]);

        $this->actingAs($investor)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('April 2026', false)
            ->assertDontSee(__('No payments yet.'), false);
    }

    public function test_unpaid_month_hides_the_investment_amount(): void
    {
        $investor = User::factory()->create();

        Investment::create([
            'title' => 'Hidden Pool',
            'contribution_per_investor' => '8000.00',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'is_active' => true,
            'created_by' => $investor->id,
            'period_start' => '2026-04-01',
            'tagged_payment_month' => '2026-04-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        $this->actingAs($investor)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertDontSee('Hidden Pool', false)
            ->assertDontSee('8000.00', false)
            ->assertSee(__('No payments yet.'), false);
    }

    public function test_paid_month_shows_the_default_amount_before_a_pool_exists(): void
    {
        $investor = User::factory()->create();

        MonthlyPaymentDefault::create([
            'month' => '2026-05-01',
            'amount' => '1500.00',
        ]);

        InvestorMonthlyPayment::create([
            'user_id' => $investor->id,
            'month' => '2026-05-01',
            'paid_at' => '2026-05-02 09:00:00',
            'recorded_by' => $investor->id,
        ]);

        $this->actingAs($investor)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('1,500.00', false)
            ->assertDontSee(__('Waiting for the pool'), false);
    }

    public function test_unpaid_month_hides_the_default_amount(): void
    {
        $investor = User::factory()->create();

        MonthlyPaymentDefault::create([
            'month' => '2026-05-01',
            'amount' => '1500.00',
        ]);

        $this->actingAs($investor)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertDontSee('1500.00', false);
    }
}

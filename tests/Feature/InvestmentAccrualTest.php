<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentAccrualTest extends TestCase
{
    use RefreshDatabase;

    public function test_accrual_splits_profit_proportionally(): void
    {
        $admin = User::factory()->admin()->create();
        $a = User::factory()->create(['email' => 'a@example.com']);
        $b = User::factory()->create(['email' => 'b@example.com']);

        $investment = Investment::create([
            'title' => 'Test pool',
            'default_monthly_rate_pct' => '1.5000',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        $investment->forceFill(['created_at' => Carbon::parse('2026-01-05')])->saveQuietly();

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $a->id,
            'contribution_amount' => '3000.00',
        ]);
        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $b->id,
            'contribution_amount' => '7000.00',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-03',
        ]);

        $response->assertSessionHasNoErrors();

        $investment->refresh()->load('periods.periodUsers');

        $this->assertCount(1, $investment->periods);
        $period = $investment->periods->first();
        $this->assertEquals('150.00', $period->profit_amount);
        $this->assertEquals('10000.00', $period->principal_snapshot);

        $byUser = $period->periodUsers->keyBy('user_id');
        $this->assertEquals('45.00', (string) $byUser->get($a->id)->profit_share);
        $this->assertEquals('105.00', (string) $byUser->get($b->id)->profit_share);
    }

    public function test_accrual_accepts_optional_rate_override(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Rate override pool',
            'default_monthly_rate_pct' => '1.0000',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-04',
            'applied_rate_pct' => '2.5',
        ])->assertSessionHasNoErrors();

        $investment->refresh()->load('periods');
        $this->assertCount(1, $investment->periods);
        $period = $investment->periods->first();
        $this->assertEquals('25.00', $period->profit_amount);
        $this->assertEquals('2.5000', (string) $period->applied_rate_pct);
    }

    public function test_all_tagged_investors_share_each_monthly_accrual(): void
    {
        $admin = User::factory()->admin()->create();
        $early = User::factory()->create(['email' => 'early@example.com']);
        $late = User::factory()->create(['email' => 'late@example.com']);

        $investment = Investment::create([
            'title' => 'Shared pool',
            'default_monthly_rate_pct' => '1.5000',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        $investment->forceFill(['created_at' => Carbon::parse('2026-01-05')])->saveQuietly();

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $early->id,
            'contribution_amount' => '7000.00',
        ]);
        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $late->id,
            'contribution_amount' => '3000.00',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-03',
        ])->assertSessionHasNoErrors();

        $investment->refresh()->load('periods.periodUsers');
        $this->assertCount(1, $investment->periods);
        $march = $investment->periods->first();
        $this->assertEquals('10000.00', $march->principal_snapshot);
        $this->assertEquals('150.00', $march->profit_amount);
        $this->assertCount(2, $march->periodUsers);
        $byUser = $march->periodUsers->keyBy('user_id');
        $this->assertEquals('105.00', (string) $byUser->get($early->id)->profit_share);
        $this->assertEquals('45.00', (string) $byUser->get($late->id)->profit_share);
    }

    public function test_duplicate_accrual_month_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        $investment->forceFill(['created_at' => Carbon::parse('2026-01-05')])->saveQuietly();

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-02',
        ])->assertSessionHasNoErrors();

        $this->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-02',
        ])->assertSessionHasErrors('accrual');
    }

    public function test_accrual_before_first_accrual_month_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-06-01',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-03',
        ])->assertSessionHasErrors('accrual');
    }

    public function test_first_accrual_month_follows_explicit_period_start_even_before_creation_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00'));

        $admin = User::factory()->admin()->create();

        $investment = Investment::create([
            'title' => 'Backdated pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->assertSame(
            '2026-01-01',
            $investment->fresh()->firstAccrualMonthStart()->toDateString()
        );

        Carbon::setTestNow();
    }
}

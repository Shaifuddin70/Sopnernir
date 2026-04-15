<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_for_investor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Your portfolio'), false);
    }

    public function test_dashboard_shows_platform_overview_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Platform overview'), false);
    }

    public function test_dashboard_shows_profit_vs_capital_and_return_for_tagged_user(): void
    {
        $user = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Solo pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $user->id,
            'period_start' => '2026-01-01',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $user->id,
            'contribution_amount' => '2000.00',
        ]);

        $period = InvestmentPeriod::create([
            'investment_id' => $investment->id,
            'month' => '2026-02-01',
            'applied_rate_pct' => '1.5000',
            'principal_snapshot' => '2000.00',
            'profit_amount' => '30.00',
        ]);

        InvestmentPeriodUser::create([
            'investment_period_id' => $period->id,
            'user_id' => $user->id,
            'contribution_snapshot' => '2000.00',
            'profit_share' => '30.00',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Return'), false)
            ->assertSee('1.50', false);
    }

    public function test_dashboard_lists_top_investors_after_accrual(): void
    {
        $admin = User::factory()->admin()->create();
        $a = User::factory()->create(['email' => 'rank-a@example.com', 'name' => 'Rank A']);
        $b = User::factory()->create(['email' => 'rank-b@example.com', 'name' => 'Rank B']);

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
            'user_id' => $a->id,
            'contribution_amount' => '1000.00',
        ]);
        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $b->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-03',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rank A', false)
            ->assertSee(__('Top investors'), false);
    }
}

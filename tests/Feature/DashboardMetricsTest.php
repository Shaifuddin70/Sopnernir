<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Services\InvestmentDailyProfitService;
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
            ->assertSee(__('Platform'), false)
            ->assertSee(__('Your portfolio'), false)
            ->assertSee(__('Profit trend (6 months)'), false)
            ->assertSee(__('Investment status'), false)
            ->assertSee(__('This month payments'), false);
    }

    public function test_dashboard_shows_platform_overview_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Platform'), false)
            ->assertSee(__('Investment status'), false);
    }

    public function test_dashboard_shows_profit_vs_capital_and_return_for_tagged_user(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $user = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Solo pool',
            'default_monthly_rate_pct' => '0.0000',
            'total_invested_amount' => '2000.00',
            'total_profit_amount' => '365.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $user->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $user->id,
            'contribution_amount' => '2000.00',
        ]);

        $summary = app(InvestmentDailyProfitService::class)->portfolioSummaryForUser($user);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Profit til today'), false)
            ->assertSee($summary['profit_til_today'], false)
            ->assertSee($summary['total_capital'], false)
            ->assertSee(__('Return'), false);

        Carbon::setTestNow();
    }

    public function test_dashboard_lists_top_investors_by_profit_til_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $a = User::factory()->create(['email' => 'rank-a@example.com', 'name' => 'Rank A']);
        $b = User::factory()->create(['email' => 'rank-b@example.com', 'name' => 'Rank B']);

        $investment = Investment::create([
            'title' => 'Pool',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '36500.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

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

        $summaryA = app(InvestmentDailyProfitService::class)->portfolioSummaryForUser($a);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rank A', false)
            ->assertSee(__('Top investors'), false)
            ->assertSee(__('By profit til today'), false)
            ->assertSee($summaryA['profit_til_today'], false);

        Carbon::setTestNow();
    }
}

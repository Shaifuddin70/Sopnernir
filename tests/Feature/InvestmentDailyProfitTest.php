<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Services\InvestmentDailyProfitService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentDailyProfitTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_profit_spreads_projected_total_over_plan_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10'));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'April 2026',
            'deed_no' => '2219',
            'default_monthly_rate_pct' => '0',
            'total_profit_amount' => '30000.00',
            'status' => Investment::STATUS_ACTIVE,
            'is_active' => true,
            'created_by' => $admin->id,
            'period_start' => '2026-05-07',
            'deed_completion_deadline' => '2027-04-30',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '160000.00',
        ]);

        $service = app(InvestmentDailyProfitService::class);
        $pool = $service->poolProjection($investment->fresh('participants'));

        $this->assertNotNull($pool);
        $this->assertSame('2026-05-07', $pool['start_date']);
        $this->assertSame('2027-04-30', $pool['end_date']);
        $this->assertSame(358, $pool['plan_days']);
        $this->assertSame(64, $pool['days_elapsed']);
        $this->assertSame('30000.00', number_format($pool['projected_profit'], 2, '.', ''));

        $expectedTilToday = round($pool['projected_profit'] * ($pool['days_elapsed'] / $pool['plan_days']), 2);
        $this->assertSame(number_format($expectedTilToday, 2, '.', ''), number_format($pool['profit_til_today'], 2, '.', ''));

        $summary = $service->portfolioSummaryForUser($investor);
        $this->assertSame('160000.00', $summary['total_capital']);
        $this->assertSame('30000.00', $summary['total_projected_profit']);
        $this->assertSame(number_format($expectedTilToday, 2, '.', ''), $summary['profit_til_today']);

        $this->actingAs($investor)
            ->get(route('investments.index'))
            ->assertOk()
            ->assertSee(__('Investment summary'), false)
            ->assertSee(__('Profit til today'), false)
            ->assertSee('April 2026', false)
            ->assertSee($summary['profit_til_today'], false);

        Carbon::setTestNow();
    }

    public function test_phone_access_shows_platform_daily_summary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10'));

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['phone' => '01700000001']);

        $investment = Investment::create([
            'title' => 'May 2026',
            'deed_no' => '2263',
            'default_monthly_rate_pct' => '0',
            'total_profit_amount' => '28000.00',
            'status' => Investment::STATUS_ACTIVE,
            'is_active' => true,
            'created_by' => $admin->id,
            'period_start' => '2026-05-18',
            'deed_completion_deadline' => '2027-04-30',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $user->id,
            'contribution_amount' => '160000.00',
        ]);

        $this->post(route('phone-access.store'), ['phone' => '01700000001'])
            ->assertRedirect(route('phone-access.investments.index'));

        $platform = app(InvestmentDailyProfitService::class)->platformSummary();

        $this->get(route('phone-access.investments.index'))
            ->assertOk()
            ->assertSee(__('Per person profit'), false)
            ->assertSee($platform['profit_til_today'], false);

        Carbon::setTestNow();
    }
}

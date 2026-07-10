<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Services\InvestmentAccrualService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentDailyAccrualTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_posted_monthly_accruals_follow_daily_profit_spread(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10'));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Daily spread pool',
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
            'contribution_amount' => '10000.00',
        ]);

        app(InvestmentAccrualService::class)->syncMonthsThrough(
            $investment->fresh(),
            Carbon::parse('2026-07-01')
        );

        $investment->refresh()->load('periods');

        $this->assertCount(3, $investment->periods);

        $may = $investment->periods->firstWhere(fn ($p) => $p->month->format('Y-m') === '2026-05');
        $june = $investment->periods->firstWhere(fn ($p) => $p->month->format('Y-m') === '2026-06');
        $july = $investment->periods->firstWhere(fn ($p) => $p->month->format('Y-m') === '2026-07');

        $this->assertNotNull($may);
        $this->assertNotNull($june);
        $this->assertNotNull($july);

        $this->assertSame(
            number_format($investment->poolProfitForMonth(Carbon::parse('2026-05-01')), 2, '.', ''),
            (string) $may->profit_amount
        );
        $this->assertSame(
            number_format($investment->poolProfitForMonth(Carbon::parse('2026-06-01')), 2, '.', ''),
            (string) $june->profit_amount
        );
        $this->assertGreaterThan((float) $may->profit_amount, (float) $june->profit_amount);

        $postedTotal = (float) $investment->periods->sum('profit_amount');
        $expectedThroughJuly = $investment->cumulativeProfitThroughDate(Carbon::parse('2026-07-31'));
        $this->assertSame(number_format($expectedThroughJuly, 2, '.', ''), number_format($postedTotal, 2, '.', ''));
    }

    public function test_full_plan_accruals_sum_to_total_profit_amount(): void
    {
        Carbon::setTestNow(Carbon::parse('2028-01-01'));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Completed pool',
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
            'contribution_amount' => '10000.00',
        ]);

        app(InvestmentAccrualService::class)->syncMonthsThrough(
            $investment->fresh(),
            Carbon::parse('2027-04-01')
        );

        $postedTotal = (float) $investment->fresh()->periods()->sum('profit_amount');
        $this->assertSame('30000.00', number_format($postedTotal, 2, '.', ''));
    }
}

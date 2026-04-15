<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AccrueMonthlyInvestmentsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_command_backfills_all_missing_months_through_current_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-02 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Backfill pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => null,
        ]);
        $investment->forceFill(['created_at' => Carbon::parse('2026-01-08 10:00:00', config('app.timezone'))])->saveQuietly();

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '10000.00',
        ]);

        Artisan::call('investments:accrue-monthly');
        Artisan::call('investments:accrue-monthly');

        $investment->refresh()->load('periods');
        $this->assertCount(5, $investment->periods);

        $months = $investment->periods->pluck('month')->map(fn ($d) => $d->format('Y-m'))->sort()->values()->all();
        $this->assertSame(['2026-01', '2026-02', '2026-03', '2026-04', '2026-05'], $months);
    }
}

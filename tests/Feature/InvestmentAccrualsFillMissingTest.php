<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class InvestmentAccrualsFillMissingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Config::set('investment.auto_accrue_on_save', false);
        parent::tearDown();
    }

    public function test_admin_fill_missing_creates_months_through_server_month(): void
    {
        Config::set('investment.auto_accrue_on_save', false);
        Carbon::setTestNow(Carbon::parse('2026-04-12 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Jan pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-02-01',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '5000.00',
        ]);

        $this->assertSame(0, InvestmentPeriod::query()->where('investment_id', $investment->id)->count());

        $this->actingAs($admin)->post(
            route('admin.investments.accruals.fill-missing', $investment)
        )->assertSessionHasNoErrors()->assertRedirect();

        $investment->refresh()->load('periods');
        $months = $investment->periods->pluck('month')->map(fn ($d) => $d->format('Y-m'))->sort()->values()->all();
        $this->assertSame(['2026-02', '2026-03', '2026-04'], $months);
    }
}

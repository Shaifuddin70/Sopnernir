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

class InvestmentAdminUpdateBackfillsAccrualsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Config::set('investment.auto_accrue_on_save', false);
        parent::tearDown();
    }

    public function test_admin_investment_update_backfills_monthly_accruals_even_when_auto_observer_disabled(): void
    {
        Config::set('investment.auto_accrue_on_save', false);
        Carbon::setTestNow(Carbon::parse('2026-04-20 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Jan pool',
            'notes' => '',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-02-01',
        ]);
        $investment->forceFill(['created_at' => Carbon::parse('2026-01-05')])->saveQuietly();

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '5000.00',
        ]);

        $this->assertSame(0, InvestmentPeriod::query()->where('investment_id', $investment->id)->count());

        $this->actingAs($admin)->patch(route('admin.investments.update', $investment), [
            'title' => 'Jan pool',
            'notes' => 'saved settings',
            'period_start' => '2026-02',
            'default_monthly_rate_pct' => '1.5',
            'status' => 'active',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $investment->refresh()->load('periods');
        $months = $investment->periods->pluck('month')->map(fn ($d) => $d->format('Y-m'))->sort()->values()->all();
        $this->assertSame(['2026-02', '2026-03', '2026-04'], $months);

        foreach ($investment->periods as $period) {
            $this->assertDatabaseHas('investment_period_users', [
                'investment_period_id' => $period->id,
                'user_id' => $investor->id,
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\User;
use App\Services\InvestmentAccrualService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentPlanCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_accruals_stop_after_plan_completion_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15'));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Timed pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-06-30',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $service = app(InvestmentAccrualService::class);
        $created = $service->fillMissingMonthsThrough($investment, Investment::accrualThroughInclusive());

        $this->assertSame(6, $created);
        $this->assertFalse(
            InvestmentPeriod::query()
                ->where('investment_id', $investment->id)
                ->whereDate('month', '2026-07-01')
                ->exists()
        );
        $this->assertSame('2026-06-01', $investment->lastAccrualMonthInclusive()->toDateString());

        Carbon::setTestNow();
    }

    public function test_manual_accrual_after_plan_end_month_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Timed pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-03-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.accruals.store', $investment), [
            'month' => '2026-04',
        ])->assertSessionHasErrors('accrual');
    }

    public function test_past_completion_date_closes_pool_on_scheduled_command(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01'));

        $admin = User::factory()->admin()->create();

        $investment = Investment::create([
            'title' => 'Ended pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'is_active' => true,
            'created_by' => $admin->id,
            'deed_completion_deadline' => '2026-06-15',
        ]);

        $this->artisan('investments:accrue-monthly')->assertSuccessful();

        $investment->refresh();
        $this->assertSame(Investment::STATUS_CLOSED, $investment->status);
        $this->assertFalse($investment->is_active);

        Carbon::setTestNow();
    }
}

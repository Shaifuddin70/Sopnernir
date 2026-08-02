<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentProfitWithdrawal;
use App\Models\InvestmentProfitWithdrawalBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_withdraw_profit_through_a_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Withdraw pool',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '365.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.investments.profit-withdrawals.store', $investment), [
                'through_date' => '2026-04-15',
            ])
            ->assertRedirect();

        $withdrawal = InvestmentProfitWithdrawal::query()->first();
        $this->assertNotNull($withdrawal);
        $this->assertNotNull($withdrawal->batch_id);
        $this->assertSame(1, InvestmentProfitWithdrawalBatch::query()->count());
        $this->assertSame('2026-04-15', $withdrawal->through_date->toDateString());
        $this->assertGreaterThan(0, (float) $withdrawal->amount);
        $this->assertEquals(1, $withdrawal->lines()->count());
        $this->assertEquals($investor->id, $withdrawal->lines()->first()->user_id);

        $net = app(\App\Services\InvestmentDailyProfitService::class)
            ->poolProjection($investment->fresh());
        $this->assertSame(0.0, (float) $net['profit_til_today']);
        $this->assertEqualsWithDelta((float) $withdrawal->amount, (float) $net['withdrawn'], 0.02);

        $summary = app(\App\Services\InvestmentDailyProfitService::class)
            ->portfolioSummaryForUser($investor);
        $this->assertSame('0.00', $summary['profit_til_today']);

        Carbon::setTestNow();
    }

    public function test_second_withdrawal_only_takes_newly_available_profit(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Staged withdraw',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '365.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.investments.profit-withdrawals.store', $investment), [
                'through_date' => '2026-02-01',
            ])
            ->assertRedirect();

        $firstAmount = (float) InvestmentProfitWithdrawal::query()->value('amount');

        $this->actingAs($admin)
            ->post(route('admin.investments.profit-withdrawals.store', $investment), [
                'through_date' => '2026-04-15',
            ])
            ->assertRedirect();

        $second = InvestmentProfitWithdrawal::query()->latest('id')->first();
        $this->assertGreaterThan(0, (float) $second->amount);
        $this->assertEqualsWithDelta(
            (float) $second->profit_through_date - $firstAmount,
            (float) $second->amount,
            0.02
        );

        Carbon::setTestNow();
    }

    public function test_admin_can_bulk_withdraw_selected_investments(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $a = User::factory()->create();
        $b = User::factory()->create();

        $one = Investment::create([
            'title' => 'Pool A',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '365.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);
        $two = Investment::create([
            'title' => 'Pool B',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '730.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $one->id,
            'user_id' => $a->id,
            'contribution_amount' => '500.00',
        ]);
        InvestmentParticipant::create([
            'investment_id' => $two->id,
            'user_id' => $b->id,
            'contribution_amount' => '500.00',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.investments.profit-withdrawals.bulk'), [
                'investment_ids' => [$one->id, $two->id],
                'through_date' => '2026-04-15',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('count', 2);

        $this->assertEquals(1, InvestmentProfitWithdrawalBatch::query()->count());
        $this->assertEquals(2, InvestmentProfitWithdrawal::query()->count());
        $this->assertEquals(2, InvestmentProfitWithdrawalBatch::query()->value('pools_count'));

        Carbon::setTestNow();
    }

    public function test_investor_cannot_withdraw_profits(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Locked pool',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '100.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '100.00',
        ]);

        $this->actingAs($investor)
            ->post(route('admin.investments.profit-withdrawals.store', $investment), [
                'through_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_admin_can_view_withdrawal_records_index(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create(['name' => 'Record Investor']);

        $investment = Investment::create([
            'title' => 'History pool',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '365.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.investments.profit-withdrawals.store', $investment), [
                'through_date' => '2026-04-15',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.profit-withdrawals.index'))
            ->assertOk()
            ->assertSee(__('Profit withdrawals'), false)
            ->assertSee(__('Withdrawn by pool'), false)
            ->assertSee(__('Total withdrawn'), false)
            ->assertSee('History pool', false)
            ->assertDontSee('Record Investor', false);

        $batch = InvestmentProfitWithdrawalBatch::query()->first();
        $this->assertNotNull($batch);

        $this->actingAs($admin)
            ->get(route('admin.profit-withdrawals.show', $batch))
            ->assertOk()
            ->assertSee('History pool', false)
            ->assertSee(__('Pools in this withdrawal'), false);

        Carbon::setTestNow();
    }

    public function test_preview_returns_available_amounts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Preview pool',
            'default_monthly_rate_pct' => '0.0000',
            'total_profit_amount' => '365.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
            'deed_completion_deadline' => '2026-12-31',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.investments.profit-withdrawals.preview', [
                'investment_ids' => [$investment->id],
                'through_date' => '2026-04-15',
            ]))
            ->assertOk()
            ->assertJsonPath('withdrawable_count', 1);

        Carbon::setTestNow();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class InvestmentAutoAccrualOnSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Config::set('investment.auto_accrue_on_save', false);
        parent::tearDown();
    }

    public function test_saving_participant_backfills_through_current_month_when_auto_accrue_enabled(): void
    {
        Config::set('investment.auto_accrue_on_save', true);
        Carbon::setTestNow(Carbon::parse('2026-05-02 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Auto pool',
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

        $investment->refresh()->load('periods');
        $months = $investment->periods->pluck('month')->map(fn ($d) => $d->format('Y-m'))->sort()->values()->all();
        $this->assertSame(['2026-01', '2026-02', '2026-03', '2026-04', '2026-05'], $months);
    }

    public function test_adding_first_participant_in_april_records_months_through_april_for_january_start_draft_pool(): void
    {
        Config::set('investment.auto_accrue_on_save', true);
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'January pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_DRAFT,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        $investment->forceFill(['created_at' => Carbon::parse('2026-04-10')])->saveQuietly();

        $this->actingAs($admin);

        $this->post(route('admin.investments.participants.store', $investment), [
            'user_id' => $investor->id,
            'contribution_amount' => '5000',
        ])->assertSessionHasNoErrors();

        $investment->refresh()->load('periods');
        $this->assertCount(4, $investment->periods);

        $months = $investment->periods->pluck('month')->map(fn ($d) => $d->format('Y-m'))->sort()->values()->all();
        $this->assertSame(['2026-01', '2026-02', '2026-03', '2026-04'], $months);
    }

    public function test_tag_all_when_everyone_already_tagged_still_syncs_accruals(): void
    {
        Config::set('investment.auto_accrue_on_save', true);
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Full pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        $investment->forceFill(['created_at' => Carbon::parse('2026-04-10')])->saveQuietly();

        foreach (User::query()->orderBy('id')->pluck('id') as $userId) {
            InvestmentParticipant::create([
                'investment_id' => $investment->id,
                'user_id' => $userId,
                'contribution_amount' => '1000.00',
            ]);
        }

        $investment->periods()->delete();
        $this->assertSame(0, $investment->periods()->count());

        $this->actingAs($admin);
        $this->post(route('admin.investments.participants.tag-all', $investment), [
            'bulk_contribution_amount' => '100.00',
        ])->assertSessionHasNoErrors();

        $investment->refresh();
        $this->assertGreaterThanOrEqual(4, $investment->periods()->count());
    }

    public function test_adding_more_participants_recalculates_existing_posted_months(): void
    {
        Config::set('investment.auto_accrue_on_save', true);
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);
        $u1 = User::factory()->create(['email' => 'u1@example.com']);
        $u2 = User::factory()->create(['email' => 'u2@example.com']);

        $investment = Investment::create([
            'title' => 'Jan pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-02-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.store', $investment), [
            'user_id' => $u1->id,
            'contribution_amount' => '5000.00',
        ])->assertSessionHasNoErrors();

        $investment->refresh()->load('periods.periodUsers');
        $this->assertCount(3, $investment->periods);
        $this->assertTrue($investment->periods->every(fn ($period) => (string) $period->principal_snapshot === '5000.00'));
        $this->assertTrue($investment->periods->every(fn ($period) => $period->periodUsers->count() === 1));

        $this->post(route('admin.investments.participants.store', $investment), [
            'user_id' => $admin->id,
            'contribution_amount' => '5000.00',
        ])->assertSessionHasNoErrors();

        $this->post(route('admin.investments.participants.store', $investment), [
            'user_id' => $u2->id,
            'contribution_amount' => '5000.00',
        ])->assertSessionHasNoErrors();

        $investment->refresh()->load('periods.periodUsers');
        foreach ($investment->periods as $period) {
            $this->assertSame('15000.00', (string) $period->principal_snapshot);
            $this->assertSame('225.00', (string) $period->profit_amount);
            $this->assertCount(3, $period->periodUsers);
            $this->assertEqualsCanonicalizing(
                [$admin->id, $u1->id, $u2->id],
                $period->periodUsers->pluck('user_id')->all()
            );
            $this->assertTrue($period->periodUsers->every(fn ($row) => (string) $row->profit_share === '75.00'));
        }
    }

    public function test_removing_last_participant_clears_posted_accruals(): void
    {
        Config::set('investment.auto_accrue_on_save', true);
        Carbon::setTestNow(Carbon::parse('2026-04-15 12:00:00', config('app.timezone')));

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Single investor pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-03-01',
        ]);

        $participant = InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '5000.00',
        ]);

        $investment->refresh();
        app(\App\Services\InvestmentAccrualService::class)->syncMonthsThrough($investment, Investment::accrualThroughInclusive());
        $this->assertGreaterThan(0, $investment->periods()->count());

        $this->actingAs($admin)
            ->delete(route('admin.investments.participants.destroy', [$investment, $participant]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $investment->refresh();
        $this->assertSame(0, $investment->participants()->count());
        $this->assertSame(0, $investment->periods()->count());
    }
}

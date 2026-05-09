<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class InvestmentTagAllInvestorsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Config::set('investment.profit_carry_since_month', null);
        parent::tearDown();
    }

    public function test_admin_can_tag_all_investors_at_once(): void
    {
        $admin = User::factory()->admin()->create();
        $i1 = User::factory()->create(['email' => 'inv1@example.com']);
        $i2 = User::factory()->create(['email' => 'inv2@example.com']);

        $investment = Investment::create([
            'title' => 'Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.participants.tag-all', $investment), [
            'bulk_contribution_amount' => '500.00',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $investment->load('participants');
        $this->assertCount(3, $investment->participants);
        $this->assertEquals('500.00', (string) $investment->participants->firstWhere('user_id', $admin->id)->contribution_amount);
        $this->assertEquals('500.00', (string) $investment->participants->firstWhere('user_id', $i1->id)->contribution_amount);
        $this->assertEquals('500.00', (string) $investment->participants->firstWhere('user_id', $i2->id)->contribution_amount);
    }

    public function test_tag_all_second_call_does_not_change_existing_participants(): void
    {
        $admin = User::factory()->admin()->create();
        $i1 = User::factory()->create(['email' => 'inv1b@example.com']);
        $i2 = User::factory()->create(['email' => 'inv2b@example.com']);

        $investment = Investment::create([
            'title' => 'Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.participants.tag-all', $investment), [
            'bulk_contribution_amount' => '500.00',
        ])->assertSessionHasNoErrors();

        $this->post(route('admin.investments.participants.tag-all', $investment), [
            'bulk_contribution_amount' => '100.00',
        ])->assertSessionHasNoErrors();

        $investment->refresh()->load('participants');
        $this->assertCount(3, $investment->participants);
        $this->assertEquals('500.00', (string) $investment->participants->firstWhere('user_id', $i1->id)->contribution_amount);
    }

    public function test_tag_all_promotes_draft_investment_to_active(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Draft pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_DRAFT,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.tag-all', $investment), [
            'bulk_contribution_amount' => '100.00',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(Investment::STATUS_ACTIVE, $investment->fresh()->status);
    }

    public function test_adding_participant_promotes_draft_investment_to_active(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Draft pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_DRAFT,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.store', $investment), [
            'user_id' => $investor->id,
            'contribution_amount' => '1000.00',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(Investment::STATUS_ACTIVE, $investment->fresh()->status);
        $this->assertTrue(
            InvestmentParticipant::query()
                ->where('investment_id', $investment->id)
                ->where('user_id', $investor->id)
                ->exists()
        );
    }

    public function test_tag_all_adds_each_users_cumulative_previous_profit_to_base_contribution(): void
    {
        Config::set('investment.profit_carry_since_month', null);

        $admin = User::factory()->admin()->create();
        $i1 = User::factory()->create(['email' => 'carry1@example.com']);
        $i2 = User::factory()->create(['email' => 'carry2@example.com']);

        $previousInvestment = Investment::create([
            'title' => 'Previous pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        $periodFeb = InvestmentPeriod::create([
            'investment_id' => $previousInvestment->id,
            'month' => '2026-02-01',
            'applied_rate_pct' => '1.5',
            'principal_snapshot' => '10000.00',
            'profit_amount' => '300.00',
        ]);
        InvestmentPeriodUser::create([
            'investment_period_id' => $periodFeb->id,
            'user_id' => $i1->id,
            'contribution_snapshot' => '5000.00',
            'profit_share' => '75.00',
        ]);
        $periodMarch = InvestmentPeriod::create([
            'investment_id' => $previousInvestment->id,
            'month' => '2026-03-01',
            'applied_rate_pct' => '1.5',
            'principal_snapshot' => '10000.00',
            'profit_amount' => '300.00',
        ]);
        InvestmentPeriodUser::create([
            'investment_period_id' => $periodMarch->id,
            'user_id' => $i1->id,
            'contribution_snapshot' => '5000.00',
            'profit_share' => '150.00',
        ]);
        InvestmentPeriodUser::create([
            'investment_period_id' => $periodMarch->id,
            'user_id' => $i2->id,
            'contribution_snapshot' => '5000.00',
            'profit_share' => '90.00',
        ]);

        $newInvestment = Investment::create([
            'title' => 'New pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-04-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.tag-all', $newInvestment), [
            'bulk_contribution_amount' => '5000.00',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $newInvestment->refresh()->load('participants');
        $this->assertSame('5000.00', (string) $newInvestment->participants->firstWhere('user_id', $admin->id)->contribution_amount);
        // 5000 base + Feb 75 + Mar 150
        $this->assertSame('5225.00', (string) $newInvestment->participants->firstWhere('user_id', $i1->id)->contribution_amount);
        $this->assertSame('5090.00', (string) $newInvestment->participants->firstWhere('user_id', $i2->id)->contribution_amount);
    }

    public function test_tag_all_respects_profit_carry_since_month_on_request(): void
    {
        Config::set('investment.profit_carry_since_month', null);

        $admin = User::factory()->admin()->create();
        $i1 = User::factory()->create(['email' => 'sincecut@example.com']);

        $previousInvestment = Investment::create([
            'title' => 'Prev',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        foreach (['2026-01-01', '2026-02-01', '2026-03-01'] as $idx => $month) {
            $p = InvestmentPeriod::create([
                'investment_id' => $previousInvestment->id,
                'month' => $month,
                'applied_rate_pct' => '1.5',
                'principal_snapshot' => '5000.00',
                'profit_amount' => '150.00',
            ]);
            InvestmentPeriodUser::create([
                'investment_period_id' => $p->id,
                'user_id' => $i1->id,
                'contribution_snapshot' => '5000.00',
                'profit_share' => (string) ($idx === 0 ? '999.99' : '10.00'),
            ]);
        }

        $newInvestment = Investment::create([
            'title' => 'New',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-04-01',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-04-10 12:00:00', config('app.timezone')));

        $this->actingAs($admin)->post(route('admin.investments.participants.tag-all', $newInvestment), [
            'bulk_contribution_amount' => '5000.00',
            'profit_carry_since_month' => '2026-02',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $newInvestment->refresh()->load('participants');
        // Excludes January 999.99; includes Feb + Mar (10 + 10)
        $this->assertSame('5020.00', (string) $newInvestment->participants->firstWhere('user_id', $i1->id)->contribution_amount);

        Carbon::setTestNow();
    }

    public function test_admin_can_optionally_add_latest_previous_profit_when_tagging_participant(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $previousInvestment = Investment::create([
            'title' => 'Previous pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);
        $previousPeriod = InvestmentPeriod::create([
            'investment_id' => $previousInvestment->id,
            'month' => '2026-03-01',
            'applied_rate_pct' => '1.5',
            'principal_snapshot' => '5000.00',
            'profit_amount' => '150.00',
        ]);
        InvestmentPeriodUser::create([
            'investment_period_id' => $previousPeriod->id,
            'user_id' => $investor->id,
            'contribution_snapshot' => '5000.00',
            'profit_share' => '150.00',
        ]);

        $newInvestment = Investment::create([
            'title' => 'New pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-04-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.store', $newInvestment), [
            'user_id' => $investor->id,
            'contribution_amount' => '5000.00',
            'include_previous_profit' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $participant = InvestmentParticipant::query()
            ->where('investment_id', $newInvestment->id)
            ->where('user_id', $investor->id)
            ->firstOrFail();

        $this->assertSame('5150.00', (string) $participant->contribution_amount);
    }
}

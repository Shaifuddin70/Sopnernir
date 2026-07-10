<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentTagAllInvestorsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
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
            'contribution_per_investor' => '500.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.participants.tag-all', $investment))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

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
            'contribution_per_investor' => '500.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.investments.participants.tag-all', $investment))
            ->assertSessionHasNoErrors();

        $this->post(route('admin.investments.participants.tag-all', $investment))
            ->assertSessionHasNoErrors();

        $investment->refresh()->load('participants');
        $this->assertCount(3, $investment->participants);
        $this->assertEquals('500.00', (string) $investment->participants->firstWhere('user_id', $i1->id)->contribution_amount);
    }

    public function test_tag_all_promotes_draft_investment_to_active(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create();

        $investment = Investment::create([
            'title' => 'Draft pool',
            'default_monthly_rate_pct' => '1.5',
            'contribution_per_investor' => '100.00',
            'status' => Investment::STATUS_DRAFT,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.tag-all', $investment))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(Investment::STATUS_ACTIVE, $investment->fresh()->status);
    }

    public function test_admin_can_tag_multiple_investors_at_once(): void
    {
        $admin = User::factory()->admin()->create();
        $i1 = User::factory()->create(['email' => 'multi1@example.com']);
        $i2 = User::factory()->create(['email' => 'multi2@example.com']);

        $investment = Investment::create([
            'title' => 'Pool',
            'default_monthly_rate_pct' => '1.5',
            'contribution_per_investor' => '750.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.store', $investment), [
            'user_ids' => [$i1->id, $i2->id],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $investment->load('participants');
        $this->assertCount(2, $investment->participants);
        $this->assertEquals('750.00', (string) $investment->participants->firstWhere('user_id', $i1->id)->contribution_amount);
        $this->assertEquals('750.00', (string) $investment->participants->firstWhere('user_id', $i2->id)->contribution_amount);
    }

    public function test_adding_participant_promotes_draft_investment_to_active(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Draft pool',
            'default_monthly_rate_pct' => '1.5',
            'contribution_per_investor' => '1000.00',
            'status' => Investment::STATUS_DRAFT,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $this->actingAs($admin)->post(route('admin.investments.participants.store', $investment), [
            'user_ids' => [$investor->id],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(Investment::STATUS_ACTIVE, $investment->fresh()->status);
        $this->assertTrue(
            InvestmentParticipant::query()
                ->where('investment_id', $investment->id)
                ->where('user_id', $investor->id)
                ->exists()
        );
    }
}

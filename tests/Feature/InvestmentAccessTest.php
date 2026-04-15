<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_investor_cannot_view_untagged_investment(): void
    {
        $investor = User::factory()->create();
        $other = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Private pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $other->id,
            'period_start' => '2026-01-01',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $other->id,
            'contribution_amount' => '500.00',
        ]);

        $this->actingAs($investor);

        $this->get(route('investments.show', $investment))->assertForbidden();
    }

    public function test_investor_can_view_tagged_investment(): void
    {
        $investor = User::factory()->create();

        $investment = Investment::create([
            'title' => 'Shared pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $investor->id,
            'period_start' => '2026-01-01',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $investment->id,
            'user_id' => $investor->id,
            'contribution_amount' => '500.00',
        ]);

        $this->actingAs($investor);

        $this->get(route('investments.show', $investment))->assertOk();
    }

    public function test_non_admin_cannot_open_admin_investments(): void
    {
        $investor = User::factory()->create();

        $this->actingAs($investor);

        $this->get(route('admin.investments.index'))->assertForbidden();
    }

    public function test_non_admin_cannot_open_admin_users(): void
    {
        $investor = User::factory()->create();

        $this->actingAs($investor);

        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_user_with_is_admin_can_open_admin_investments(): void
    {
        $manager = User::factory()->create(['is_admin' => true]);

        $this->actingAs($manager);

        $this->get(route('admin.investments.index'))->assertOk();
    }
}

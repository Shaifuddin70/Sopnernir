<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneAccessInvestmentLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_phone_access_and_view_only_their_investments(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['phone' => '01700000001']);
        $other = User::factory()->create(['phone' => '01700000002']);

        $myInvestment = Investment::create([
            'title' => 'My Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        $otherInvestment = Investment::create([
            'title' => 'Other Pool',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-01-01',
        ]);

        InvestmentParticipant::create([
            'investment_id' => $myInvestment->id,
            'user_id' => $user->id,
            'contribution_amount' => '5000.00',
        ]);
        InvestmentParticipant::create([
            'investment_id' => $otherInvestment->id,
            'user_id' => $other->id,
            'contribution_amount' => '4000.00',
        ]);

        $period = InvestmentPeriod::create([
            'investment_id' => $myInvestment->id,
            'month' => '2026-02-01',
            'applied_rate_pct' => '1.5',
            'principal_snapshot' => '5000.00',
            'profit_amount' => '75.00',
        ]);
        InvestmentPeriodUser::create([
            'investment_period_id' => $period->id,
            'user_id' => $user->id,
            'contribution_snapshot' => '5000.00',
            'profit_share' => '75.00',
        ]);

        $this->post(route('phone-access.store'), ['phone' => '01700000001'])
            ->assertRedirect(route('phone-access.investments.index'));

        $this->get(route('phone-access.investments.index'))
            ->assertOk()
            ->assertSeeText('My Pool')
            ->assertDontSeeText('Other Pool')
            ->assertSeeText('Platform total amount')
            ->assertSeeText('9075.00')
            ->assertSeeText('5075.00')
            ->assertSeeText('5000.00')
            ->assertSeeText('1');

        $this->get(route('phone-access.investments.show', $myInvestment))
            ->assertOk()
            ->assertSeeText('5000.00')
            ->assertSeeText('75.00');

        $this->get(route('phone-access.investments.show', $otherInvestment))
            ->assertNotFound();
    }

    public function test_phone_access_requires_existing_phone_match(): void
    {
        $this->post(route('phone-access.store'), ['phone' => '01999999999'])
            ->assertSessionHasErrors('phone');
    }
}

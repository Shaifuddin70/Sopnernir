<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentDeedDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_investment_with_plan_dates_and_total_profit(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'New pool',
            'deed_no' => '2219',
            'notes' => 'Sample note',
            'period_start' => '2026-05-07',
            'deed_completion_deadline' => '2027-04-30',
            'default_monthly_rate_pct' => '1.5',
            'contribution_per_investor' => '5000.00',
            'status' => Investment::STATUS_DRAFT,
            'is_active' => '1',
            'payment_month' => '2026-05',
        ]);

        $investment = Investment::query()->where('title', 'New pool')->first();

        $response->assertRedirect(route('admin.investments.show', $investment));
        $this->assertSame('2219', $investment->deed_no);
        $this->assertSame('2026-05-07', $investment->period_start->toDateString());
        $this->assertSame('2027-04-30', $investment->deed_completion_deadline->toDateString());
        $this->assertSame('5000.00', $investment->contribution_per_investor);
        $this->assertSame('1.5000', $investment->default_monthly_rate_pct);
    }

    public function test_ending_date_is_required_when_creating_investment(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'Missing deadline',
            'deed_no' => '1001',
            'period_start' => '2026-05-07',
            'default_monthly_rate_pct' => '1.5',
            'contribution_per_investor' => '1000',
            'status' => Investment::STATUS_DRAFT,
            'payment_month' => '2026-05',
        ])->assertSessionHasErrors('deed_completion_deadline');
    }

    public function test_admin_show_displays_plan_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $deadline = Carbon::parse('2026-12-31');

        $investment = Investment::create([
            'title' => 'Pool with deed',
            'deed_no' => '2330',
            'default_monthly_rate_pct' => '0',
            'total_profit_amount' => '25000.00',
            'total_invested_amount' => '150000.00',
            'contribution_per_investor' => '5000.00',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'period_start' => '2026-06-11',
            'deed_completion_deadline' => $deadline,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.investments.show', $investment))
            ->assertOk()
            ->assertSee('2330', false)
            ->assertSee('25000.00', false)
            ->assertSee($deadline->translatedFormat('j F Y'), false);
    }
}

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

    public function test_admin_can_create_investment_with_deed_completion_deadline(): void
    {
        $admin = User::factory()->admin()->create();

        $deadline = Carbon::now()->addMonths(6)->toDateString();

        $response = $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'New pool',
            'notes' => null,
            'period_start' => '2026-01',
            'deed_completion_deadline' => $deadline,
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_DRAFT,
            'is_active' => '1',
        ]);

        $investment = Investment::query()->where('title', 'New pool')->first();

        $response->assertRedirect(route('admin.investments.show', $investment));
        $this->assertSame($deadline, $investment->deed_completion_deadline->toDateString());
    }

    public function test_deed_deadline_is_required_when_creating_investment(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'Missing deadline',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_DRAFT,
        ])->assertSessionHasErrors('deed_completion_deadline');
    }

    public function test_admin_show_displays_deed_completion_deadline(): void
    {
        $admin = User::factory()->admin()->create();
        $deadline = Carbon::parse('2026-12-31');

        $investment = Investment::create([
            'title' => 'Pool with deed',
            'default_monthly_rate_pct' => '1.5',
            'status' => Investment::STATUS_ACTIVE,
            'created_by' => $admin->id,
            'deed_completion_deadline' => $deadline,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.investments.show', $investment))
            ->assertOk()
            ->assertSee('Plan completion date', false)
            ->assertSee($deadline->translatedFormat('j F Y'), false);
    }
}

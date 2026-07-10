<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestorMonthlyPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_monthly_payments_page(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create(['name' => 'Paying Investor']);

        $this->actingAs($admin)
            ->get(route('admin.monthly-payments.index', ['month' => '2026-03']))
            ->assertOk()
            ->assertSee(__('Monthly payments'), false)
            ->assertSee('Paying Investor', false)
            ->assertSee(__('Create investment'), false)
            ->assertSee(
                route('admin.investments.index', ['new' => 1, 'payment_month' => '2026-03']),
                false
            )
            ->assertSee(
                route('admin.monthly-payments.index', ['month' => '2026-02']),
                false
            )
            ->assertSee(
                route('admin.monthly-payments.index', ['month' => '2026-04']),
                false
            );
    }

    public function test_create_investment_link_prefills_payment_month(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.investments.index', ['new' => 1, 'payment_month' => '2026-03']))
            ->assertOk()
            ->assertSee('value="2026-03"', false);
    }

    public function test_admin_can_mark_investor_paid_for_month(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.monthly-payments.update'), [
            'user_id' => $investor->id,
            'month' => '2026-04',
            'paid' => true,
        ])->assertRedirect();

        $payment = InvestorMonthlyPayment::query()->first();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPaid());
        $this->assertSame($admin->id, $payment->recorded_by);
    }

    public function test_admin_can_check_all_investors_paid_for_month(): void
    {
        $admin = User::factory()->admin()->create();
        $first = User::factory()->create(['name' => 'Bulk One']);
        $second = User::factory()->create(['name' => 'Bulk Two']);

        $this->actingAs($admin)
            ->get(route('admin.monthly-payments.index', ['month' => '2026-05']))
            ->assertOk()
            ->assertSee(__('Check all'), false);

        $this->actingAs($admin)->patch(route('admin.monthly-payments.bulk-update'), [
            'month' => '2026-05',
            'paid' => true,
            'user_ids' => [$first->id, $second->id],
        ])->assertRedirect();

        $this->assertTrue(
            InvestorMonthlyPayment::query()
                ->where('user_id', $first->id)
                ->whereDate('month', '2026-05-01')
                ->first()
                ?->isPaid() ?? false
        );
        $this->assertTrue(
            InvestorMonthlyPayment::query()
                ->where('user_id', $second->id)
                ->whereDate('month', '2026-05-01')
                ->first()
                ?->isPaid() ?? false
        );
    }

    public function test_non_admin_cannot_access_monthly_payments(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.monthly-payments.index'))
            ->assertForbidden();
    }

    public function test_monthly_payments_search_filters_investors(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Sakibur Rahman', 'email' => 'sakib@example.com']);
        User::factory()->create(['name' => 'Other Person', 'email' => 'other@example.com']);

        $this->actingAs($admin)
            ->get(route('admin.monthly-payments.index', [
                'month' => '2026-03',
                'search' => 'Sakibur',
            ]))
            ->assertOk()
            ->assertSee('Sakibur Rahman', false)
            ->assertDontSee('Other Person', false);
    }

    public function test_monthly_payments_search_returns_ajax_fragment(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Sakibur Rahman', 'email' => 'sakib@example.com']);
        User::factory()->create(['name' => 'Other Person', 'email' => 'other@example.com']);

        $response = $this->actingAs($admin)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('admin.monthly-payments.index', [
                'month' => '2026-03',
                'search' => 'Sakibur',
            ]));

        $response->assertOk()
            ->assertJsonStructure(['html']);

        $html = (string) $response->json('html');
        $this->assertStringContainsString('Sakibur Rahman', $html);
        $this->assertStringNotContainsString('Other Person', $html);
    }
}

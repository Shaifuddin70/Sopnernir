<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestorMonthlyPayment;
use App\Models\MonthlyPaymentDefault;
use App\Models\User;
use App\Notifications\MonthlyPaymentRecordedNotification;
use App\Notifications\NewInvestmentPlanNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvestorNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_investor_paid_sends_payment_email(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        MonthlyPaymentDefault::query()->create([
            'month' => '2026-04-01',
            'amount' => '3200.00',
        ]);

        $this->actingAs($admin)->patch(route('admin.monthly-payments.update'), [
            'user_id' => $investor->id,
            'month' => '2026-04',
            'paid' => true,
        ])->assertRedirect();

        Notification::assertSentTo($investor, MonthlyPaymentRecordedNotification::class, function (MonthlyPaymentRecordedNotification $notification): bool {
            return $notification->month->toDateString() === '2026-04-01'
                && $notification->amount === '3200.00';
        });
        Notification::assertSentToTimes($investor, MonthlyPaymentRecordedNotification::class, 1);
    }

    public function test_checking_an_already_paid_investor_does_not_email_again(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        InvestorMonthlyPayment::create([
            'user_id' => $investor->id,
            'month' => '2026-04-01',
            'paid_at' => now(),
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.monthly-payments.update'), [
            'user_id' => $investor->id,
            'month' => '2026-04',
            'paid' => true,
        ])->assertRedirect();

        Notification::assertNothingSentTo($investor);
    }

    public function test_marking_investor_unpaid_does_not_send_payment_email(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        InvestorMonthlyPayment::create([
            'user_id' => $investor->id,
            'month' => '2026-04-01',
            'paid_at' => now(),
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.monthly-payments.update'), [
            'user_id' => $investor->id,
            'month' => '2026-04',
            'paid' => 0,
        ])->assertRedirect();

        Notification::assertNotSentTo($investor, MonthlyPaymentRecordedNotification::class);
    }

    public function test_bulk_paid_sends_email_to_each_investor(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.monthly-payments.bulk-update'), [
            'month' => '2026-05',
            'paid' => true,
            'user_ids' => [$first->id, $second->id],
        ])->assertRedirect();

        Notification::assertSentTo($first, MonthlyPaymentRecordedNotification::class);
        Notification::assertSentTo($second, MonthlyPaymentRecordedNotification::class);
    }

    public function test_creating_a_listed_investment_emails_active_users(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $paidInvestor = User::factory()->create();
        $otherInvestor = User::factory()->create();
        User::factory()->create(['is_active' => false]);

        InvestorMonthlyPayment::create([
            'user_id' => $paidInvestor->id,
            'month' => '2026-06-01',
            'paid_at' => now(),
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'June pool',
            'deed_no' => 'JUNE-001',
            'notes' => null,
            'contribution_per_investor' => '5000.00',
            'total_profit_amount' => '30000.00',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-12-31',
            'status' => 'draft',
            'is_active' => '1',
            'payment_month' => '2026-06',
        ])->assertRedirect();

        $investment = Investment::query()->where('title', 'June pool')->first();
        $this->assertNotNull($investment);

        Notification::assertSentTo($paidInvestor, NewInvestmentPlanNotification::class);
        Notification::assertSentTo($otherInvestor, NewInvestmentPlanNotification::class);
        Notification::assertSentTo($admin, NewInvestmentPlanNotification::class);

        Notification::assertSentTo($paidInvestor, NewInvestmentPlanNotification::class, function ($notification) use ($paidInvestor) {
            return in_array($paidInvestor->id, $notification->taggedUserIds, true);
        });
    }

    public function test_hidden_investment_does_not_email_investors(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.investments.store'), [
            'title' => 'Hidden pool',
            'deed_no' => 'HIDE-001',
            'contribution_per_investor' => '5000.00',
            'total_profit_amount' => '10000.00',
            'period_start' => '2026-06-01',
            'deed_completion_deadline' => '2026-12-31',
            'status' => 'draft',
            'is_active' => '0',
            'payment_month' => '2026-06',
        ])->assertRedirect();

        Notification::assertNotSentTo($investor, NewInvestmentPlanNotification::class);
        Notification::assertNotSentTo($admin, NewInvestmentPlanNotification::class);
    }
}

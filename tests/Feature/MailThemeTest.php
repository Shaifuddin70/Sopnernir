<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\User;
use App\Notifications\MonthlyPaymentRecordedNotification;
use App\Notifications\NewInvestmentPlanNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_email_uses_branded_layout(): void
    {
        $user = User::factory()->create(['name' => 'Rokib']);
        $html = $this->renderMail((new ResetPasswordNotification('test-token'))->toMail($user));

        $this->assertStringContainsString(config('app.name'), $html);
        $this->assertStringContainsString(__('Investment pools'), $html);
        $this->assertStringContainsString('alt="'.config('app.name').'"', $html);
        $this->assertTrue(
            str_contains($html, 'images/logo.png') || str_contains($html, 'data:image/'),
            'Email should include the Shopnernir logo image'
        );
        $this->assertStringContainsString('Reset your password.', $html);
        $this->assertStringContainsString('Hello Rokib,', $html);
        $this->assertStringContainsString(__('Thank you for choosing :app. We appreciate your trust in us.', ['app' => config('app.name')]), $html);
        $this->assertStringNotContainsString("If you're having trouble clicking", $html);
    }

    public function test_new_investment_email_includes_plan_summary(): void
    {
        $user = User::factory()->create(['name' => 'Rokib']);
        $investment = Investment::query()->create([
            'title' => 'Premium Growth Plan',
            'deed_no' => 'INV-2026-00125',
            'contribution_per_investor' => '10000.00',
            'total_profit_amount' => '11000.00',
            'period_start' => '2026-10-30',
            'deed_completion_deadline' => '2027-10-30',
            'status' => Investment::STATUS_DRAFT,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $html = $this->renderMail((new NewInvestmentPlanNotification($investment, [$user->id]))->toMail($user));

        $this->assertStringContainsString('Premium Growth Plan', $html);
        $this->assertStringContainsString('INV-2026-00125', $html);
        $this->assertStringContainsString('10,000.00', $html);
        $this->assertStringContainsString(__('Investment details'), $html);
        $this->assertStringContainsString(__('You have been included on this plan.'), $html);
        $this->assertStringNotContainsString("If you're having trouble clicking", $html);
    }

    public function test_payment_email_includes_month_summary(): void
    {
        $user = User::factory()->create(['name' => 'Rokib']);
        $month = now()->startOfMonth();
        $html = $this->renderMail((new MonthlyPaymentRecordedNotification($month, '2500.00'))->toMail($user));

        $this->assertStringContainsString(__('Payment recorded'), $html);
        $this->assertStringContainsString($month->translatedFormat('F Y'), $html);
        $this->assertStringContainsString('2,500.00', $html);
        $this->assertStringContainsString(__('Your payment has been recorded.'), $html);
        $this->assertStringNotContainsString(__('Payment details'), $html);
    }

    private function renderMail($mail): string
    {
        $html = $mail->render();

        return is_string($html) ? $html : $html->toHtml();
    }
}

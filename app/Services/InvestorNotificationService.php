<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\User;
use App\Notifications\MonthlyPaymentRecordedNotification;
use App\Notifications\NewInvestmentPlanNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class InvestorNotificationService
{
    public function paymentRecorded(User $user, Carbon $month): void
    {
        if (! $this->canEmail($user)) {
            return;
        }

        $user->notify(new MonthlyPaymentRecordedNotification($month->copy()->startOfMonth()));
    }

    public function newInvestmentPlan(Investment $investment): void
    {
        if (! $investment->is_active) {
            return;
        }

        $taggedUserIds = $investment->participants()->pluck('user_id')->map(fn ($id) => (int) $id)->all();

        $users = User::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get()
            ->filter(fn (User $user) => $this->canEmail($user))
            ->values();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new NewInvestmentPlanNotification($investment, $taggedUserIds));
    }

    private function canEmail(User $user): bool
    {
        return is_string($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL);
    }
}

<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Notice;
use App\Models\SpecialInstallment;
use App\Models\User;
use App\Notifications\MonthlyPaymentRecordedNotification;
use App\Notifications\NewInvestmentPlanNotification;
use App\Notifications\NoticePublishedNotification;
use App\Notifications\SpecialInstallmentRecordedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class InvestorNotificationService
{
    public function paymentRecorded(User $user, Carbon $month): void
    {
        if (! $this->canEmail($user)) {
            return;
        }

        $month = $month->copy()->startOfMonth();
        $amount = app(MonthlyPaymentService::class)->defaultAmountForMonth($month);

        $user->notify(new MonthlyPaymentRecordedNotification($month, $amount));
    }

    public function specialInstallmentRecorded(User $user, SpecialInstallment $installment): void
    {
        if (! $this->canEmail($user)) {
            return;
        }

        $user->notify(new SpecialInstallmentRecordedNotification($installment));
    }

    public function newInvestmentPlan(Investment $investment): void
    {
        if (! $investment->is_active) {
            return;
        }

        $taggedUserIds = $investment->participants()->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $users = $this->emailableUsers();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new NewInvestmentPlanNotification($investment, $taggedUserIds));
    }

    public function noticePublished(Notice $notice): void
    {
        if (! $notice->isPublished()) {
            return;
        }

        $users = $this->emailableUsers();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new NoticePublishedNotification($notice));
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function emailableUsers()
    {
        return User::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get()
            ->filter(fn (User $user) => $this->canEmail($user))
            ->values();
    }

    private function canEmail(User $user): bool
    {
        return is_string($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL);
    }
}


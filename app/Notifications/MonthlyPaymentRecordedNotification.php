<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyPaymentRecordedNotification extends Notification
{
    public function __construct(public Carbon $month) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->month->copy()->startOfMonth()->translatedFormat('F Y');
        $data = [
            'subject' => __('Payment recorded for :month', ['month' => $label]),
            'appName' => config('app.name'),
            'userName' => $notifiable->name ?? __('there'),
            'monthLabel' => $label,
            'actionUrl' => route('investments.index'),
        ];

        return (new MailMessage)
            ->subject($data['subject'])
            ->view([
                'html' => 'emails.payment-recorded',
                'text' => 'emails.payment-recorded-text',
            ], $data);
    }
}

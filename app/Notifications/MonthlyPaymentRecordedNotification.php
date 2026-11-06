<?php

namespace App\Notifications;

use App\Support\BrandedMail;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyPaymentRecordedNotification extends Notification
{
    public function __construct(
        public Carbon $month,
        public ?string $amount = null,
    ) {}

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
            'amountLabel' => $this->amount !== null && $this->amount !== ''
                ? BrandedMail::money($this->amount)
                : null,
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

<?php

namespace App\Notifications;

use App\Models\SpecialInstallment;
use App\Support\BrandedMail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpecialInstallmentRecordedNotification extends Notification
{
    public function __construct(public SpecialInstallment $installment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = [
            'subject' => __('Payment recorded for :month', ['month' => $this->installment->title]),
            'appName' => config('app.name'),
            'userName' => $notifiable->name ?? __('there'),
            'monthLabel' => $this->installment->title,
            'amountLabel' => BrandedMail::money($this->installment->amount),
            'actionUrl' => route('payments.index'),
        ];

        return (new MailMessage)
            ->subject($data['subject'])
            ->view([
                'html' => 'emails.payment-recorded',
                'text' => 'emails.payment-recorded-text',
            ], $data);
    }
}

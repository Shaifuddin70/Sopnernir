<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $app = config('app.name');
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $data = [
            'subject' => __('Reset your :app password', ['app' => $app]),
            'appName' => $app,
            'userName' => $notifiable->name ?? __('there'),
            'actionUrl' => $this->resetUrl($notifiable),
            'expire' => $expire,
        ];

        return (new MailMessage)
            ->subject($data['subject'])
            ->view([
                'html' => 'emails.reset-password',
                'text' => 'emails.reset-password-text',
            ], $data);
    }
}

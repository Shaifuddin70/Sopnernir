<?php

namespace App\Notifications;

use App\Models\Notice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoticePublishedNotification extends Notification
{
    public function __construct(public Notice $notice) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->notice->loadMissing('attachments');

        $data = [
            'subject' => __('New notice: :title', ['title' => $this->notice->title]),
            'appName' => config('app.name'),
            'userName' => $notifiable->name ?? __('there'),
            'noticeTitle' => $this->notice->title,
            'excerpt' => $this->notice->excerpt(220),
            'attachmentCount' => $this->notice->attachments->count(),
            'actionUrl' => route('notices.show', $this->notice),
            'publishedAt' => $this->notice->published_at?->timezone(config('app.timezone'))->translatedFormat('j M Y, g:i A'),
        ];

        return (new MailMessage)
            ->subject($data['subject'])
            ->view([
                'html' => 'emails.notice-published',
                'text' => 'emails.notice-published-text',
            ], $data);
    }
}

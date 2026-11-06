<?php

namespace App\Notifications;

use App\Models\Investment;
use App\Support\BrandedMail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInvestmentPlanNotification extends Notification
{
    /**
     * @param  list<int>  $taggedUserIds
     */
    public function __construct(
        public Investment $investment,
        public array $taggedUserIds = [],
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
        $app = config('app.name');
        $isTagged = in_array((int) $notifiable->id, $this->taggedUserIds, true);
        $contribution = $this->investment->contribution_per_investor !== null
            ? BrandedMail::money($this->investment->contribution_per_investor)
            : null;
        $profit = $this->investment->usesTotalProfitPlan()
            ? BrandedMail::money($this->investment->total_profit_amount)
            : null;
        $duration = BrandedMail::monthDuration(
            $this->investment->period_start,
            $this->investment->deed_completion_deadline,
        );

        $details = [
            ['label' => __('Plan name'), 'value' => $this->investment->title],
        ];

        if ($this->investment->deed_no) {
            $details[] = ['label' => __('Deed number'), 'value' => $this->investment->deed_no];
        }

        if ($this->investment->period_start) {
            $details[] = ['label' => __('Start date'), 'value' => $this->investment->period_start->translatedFormat('j M Y')];
        }

        if ($this->investment->deed_completion_deadline) {
            $details[] = ['label' => __('End date'), 'value' => $this->investment->deed_completion_deadline->translatedFormat('j M Y')];
        }

        if ($duration !== null) {
            $details[] = ['label' => __('Duration'), 'value' => $duration];
        }

        if ($contribution !== null) {
            $details[] = ['label' => __('Contribution'), 'value' => $contribution];
        }

        $rate = $this->investment->default_monthly_rate_pct;
        if ($rate !== null && (float) $rate > 0) {
            $details[] = ['label' => __('Monthly rate'), 'value' => rtrim(rtrim(number_format((float) $rate, 4, '.', ''), '0'), '.').'%'];
        }

        $callout = null;
        if ($profit !== null) {
            $callout = [
                'label' => __('Total profit'),
                'value' => $profit,
                'text' => $isTagged
                    ? __('You have been included on this plan.')
                    : __('Planned pool profit for this investment.'),
            ];
        } elseif ($isTagged) {
            $callout = [
                'label' => __('Included on this plan'),
                'value' => null,
                'text' => __('You have been included on this plan.'),
            ];
        }

        $data = [
            'subject' => __('New investment plan: :title', ['title' => $this->investment->title]),
            'appName' => $app,
            'userName' => $notifiable->name ?? __('there'),
            'isTagged' => $isTagged,
            'headline' => $isTagged
                ? __('You\'ve been included on a new plan.')
                : __('A new investment plan is available.'),
            'intro' => $isTagged
                ? __('Your investment plan has been added on :app.', ['app' => $app])
                : __('A new investment plan is now available on :app.', ['app' => $app]),
            'heroLabel' => __('Contribution'),
            'heroValue' => $contribution,
            'heroStatus' => $isTagged
                ? __('Investment successfully registered')
                : __('New plan now available'),
            'details' => $details,
            'callout' => $callout,
            'actionUrl' => $isTagged
                ? route('investments.show', $this->investment)
                : route('investments.index'),
            'actionText' => $isTagged ? __('View this plan') : __('View your portfolio'),
        ];

        return (new MailMessage)
            ->subject($data['subject'])
            ->view([
                'html' => 'emails.new-investment-plan',
                'text' => 'emails.new-investment-plan-text',
            ], $data);
    }
}

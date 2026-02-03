<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Announcement $announcement,
        /** @var array<string> */
        private readonly array $preferredChannels = ['database', 'mail', 'webpush']
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (in_array('database', $this->preferredChannels, true)) {
            $channels[] = 'database';
        }

        if (in_array('mail', $this->preferredChannels, true) && config('mail.mailers.smtp') && config('mail.from.address')) {
            $channels[] = 'mail';
        }

        if (
            in_array('webpush', $this->preferredChannels, true)
            && config('webpush.vapid.public_key')
            && class_exists(\NotificationChannels\WebPush\WebPushChannel::class)
        ) {
            $channels[] = \NotificationChannels\WebPush\WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->announcement->title)
            ->greeting(__('Hello :name,', ['name' => $notifiable->name ?? '']))
            ->line($this->announcement->content)
            ->line(__('Priority: :priority', ['priority' => ucfirst($this->announcement->priority)]))
            ->line(__('Publish date: :date', ['date' => optional($this->announcement->publish_date)->format('Y-m-d')]))
            ->action(__('View in portal'), url('/announcements'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'content' => $this->announcement->content,
            'priority' => $this->announcement->priority,
            'type' => $this->announcement->type,
            'publish_date' => optional($this->announcement->publish_date)->toDateString(),
        ];
    }

    public function toWebPush(object $notifiable)
    {
        if (!class_exists(\NotificationChannels\WebPush\WebPushMessage::class)) {
            return null;
        }

        return (new \NotificationChannels\WebPush\WebPushMessage)
            ->title($this->announcement->title)
            ->icon(url('/favicon.ico'))
            ->body(str($this->announcement->content)->limit(140))
            ->data([
                'url' => url('/announcements'),
                'announcement_id' => $this->announcement->id,
            ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\PeriodSwitchRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PeriodSwitchRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PeriodSwitchRequest $switchRequest,
        private readonly string $requesterName,
        private readonly string $className,
        private readonly string $subjectName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $locale = $notifiable->preferred_locale ?? app()->getLocale();
        
        return [
            'type' => 'period_switch_requested',
            'switch_request_id' => $this->switchRequest->id,
            'class_id' => $this->switchRequest->period?->timetable?->class_id,
            'title' => $this->getTitle($locale),
            'body' => $this->getBody($locale),
            'requester_name' => $this->requesterName,
            'class_name' => $this->className,
            'subject_name' => $this->subjectName,
            'date' => $this->switchRequest->date?->format('Y-m-d'),
            'period_number' => $this->switchRequest->period?->period_number,
        ];
    }

    public function toFcm(object $notifiable): array
    {
        $locale = $notifiable->preferred_locale ?? app()->getLocale();
        
        return [
            'title' => $this->getTitle($locale),
            'body' => $this->getBody($locale),
            'data' => [
                'type' => 'period_switch_requested',
                'switch_request_id' => $this->switchRequest->id,
                'click_action' => 'OPEN_SWITCH_REQUESTS',
            ],
        ];
    }

    private function getTitle(string $locale): string
    {
        return $locale === 'mm' 
            ? 'အချိန်စာရင်း ပြောင်းလဲရန် တောင်းဆိုချက်' 
            : 'Period Switch Request';
    }

    private function getBody(string $locale): string
    {
        $date = $this->switchRequest->date?->format('M d, Y') ?? '';
        $period = 'P' . ($this->switchRequest->period?->period_number ?? '');
        
        if ($locale === 'mm') {
            return "{$this->requesterName} မှ {$this->className} အတန်း၏ {$date} ရက်နေ့ {$period} ပီရီယက်ကို {$this->subjectName} ဘာသာဖြင့် ပြောင်းလဲရန် တောင်းဆိုထားပါသည်။";
        }
        
        return "{$this->requesterName} requested to switch {$period} on {$date} for {$this->className} with {$this->subjectName}.";
    }
}

<?php

namespace App\Notifications;

use App\Models\PeriodSwitchRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PeriodSwitchResponded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PeriodSwitchRequest $switchRequest,
        private readonly string $responderName,
        private readonly string $className,
        private readonly string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $locale = $notifiable->preferred_locale ?? app()->getLocale();
        
        return [
            'type' => 'period_switch_responded',
            'switch_request_id' => $this->switchRequest->id,
            'class_id' => $this->switchRequest->period?->timetable?->class_id,
            'title' => $this->getTitle($locale),
            'body' => $this->getBody($locale),
            'responder_name' => $this->responderName,
            'class_name' => $this->className,
            'status' => $this->status,
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
                'type' => 'period_switch_responded',
                'switch_request_id' => $this->switchRequest->id,
                'status' => $this->status,
                'click_action' => 'OPEN_SWITCH_REQUESTS',
            ],
        ];
    }

    private function getTitle(string $locale): string
    {
        if ($locale === 'mm') {
            return $this->status === 'accepted' 
                ? 'အချိန်စာရင်း ပြောင်းလဲရန် လက်ခံပြီး' 
                : 'အချိန်စာရင်း ပြောင်းလဲရန် ငြင်းပယ်ပြီး';
        }
        
        return $this->status === 'accepted' 
            ? 'Period Switch Accepted' 
            : 'Period Switch Rejected';
    }

    private function getBody(string $locale): string
    {
        $date = $this->switchRequest->date?->format('M d, Y') ?? '';
        $period = 'P' . ($this->switchRequest->period?->period_number ?? '');
        
        if ($locale === 'mm') {
            $statusText = $this->status === 'accepted' ? 'လက်ခံ' : 'ငြင်းပယ်';
            return "{$this->responderName} မှ {$this->className} အတန်း၏ {$date} ရက်နေ့ {$period} ပီရီယက် ပြောင်းလဲရန် တောင်းဆိုချက်ကို {$statusText}ပြီးပါပြီ။";
        }
        
        $statusText = $this->status === 'accepted' ? 'accepted' : 'rejected';
        return "{$this->responderName} has {$statusText} your request to switch {$period} on {$date} for {$this->className}.";
    }
}

<?php

namespace App\Notifications;

use App\Models\DailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DailyReportReceived extends Notification
{
    use Queueable;

    protected $report;

    public function __construct(DailyReport $report)
    {
        $this->report = $report;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Daily Report',
            'message' => $this->report->subject,
            'type' => 'daily_report',
            'report_id' => $this->report->id,
            'category' => $this->report->category,
            'sender_name' => $this->report->user?->name ?? 'Admin',
            'created_at' => $this->report->created_at->toISOString(),
        ];
    }
}

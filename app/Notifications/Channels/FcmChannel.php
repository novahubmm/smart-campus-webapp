<?php

namespace App\Notifications\Channels;

use App\Services\FcmService;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function __construct(
        private readonly FcmService $fcmService
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $data = $notification->toFcm($notifiable);
        
        if (empty($data)) {
            return;
        }

        $this->fcmService->sendToUser(
            $notifiable,
            $data['title'] ?? '',
            $data['body'] ?? '',
            $data['data'] ?? []
        );
    }
}

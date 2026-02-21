<?php

namespace App\Jobs\PaymentSystem;

use App\Models\PaymentSystem\Payment;
use App\Services\PaymentSystem\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The payment to send notification for.
     */
    protected Payment $payment;

    /**
     * The notification type.
     */
    protected string $notificationType;

    /**
     * Create a new job instance.
     *
     * @param Payment $payment
     * @param string $notificationType One of: 'new_payment', 'verified', 'rejected'
     */
    public function __construct(Payment $payment, string $notificationType)
    {
        $this->payment = $payment;
        $this->notificationType = $notificationType;
    }

    /**
     * Execute the job.
     * 
     * Sends payment notifications via FCM based on notification type.
     * 
     * Validates: Requirements 13.4, 13.7, 20.1, 20.2, 20.3
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            Log::info('Sending payment notification', [
                'payment_id' => $this->payment->id,
                'payment_number' => $this->payment->payment_number,
                'notification_type' => $this->notificationType,
            ]);

            switch ($this->notificationType) {
                case 'new_payment':
                    $notificationService->notifyAdminOfNewPayment($this->payment);
                    break;
                case 'verified':
                    $notificationService->notifyGuardianOfVerification($this->payment);
                    break;
                case 'rejected':
                    $notificationService->notifyGuardianOfRejection($this->payment);
                    break;
                default:
                    Log::warning('Unknown notification type', [
                        'notification_type' => $this->notificationType,
                    ]);
            }

            Log::info('Payment notification sent successfully', [
                'payment_id' => $this->payment->id,
                'notification_type' => $this->notificationType,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment notification', [
                'payment_id' => $this->payment->id,
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw - notification failures shouldn't break the payment flow
        }
    }
}

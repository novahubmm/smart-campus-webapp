<?php

namespace App\Services\PaymentSystem;

use App\Models\PaymentSystem\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send FCM notification to a user.
     * 
     * Uses the existing FCM setup from the announcement notification infrastructure.
     * 
     * @param User $user
     * @param string $title
     * @param string $titleMm
     * @param string $body
     * @param string $bodyMm
     * @param array $data Additional data payload
     * @return bool Success status
     */
    protected function sendFCMNotification(
        User $user,
        string $title,
        string $titleMm,
        string $body,
        string $bodyMm,
        array $data = []
    ): bool {
        try {
            // Get FCM token from user
            $fcmToken = $user->fcm_token;
            
            if (!$fcmToken) {
                Log::warning('User has no FCM token', ['user_id' => $user->id]);
                return false;
            }

            // Get FCM server key from config
            $serverKey = config('services.fcm.server_key');
            
            if (!$serverKey) {
                Log::error('FCM server key not configured');
                return false;
            }

            // Prepare notification payload
            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => array_merge([
                    'title' => $title,
                    'title_mm' => $titleMm,
                    'body' => $body,
                    'body_mm' => $bodyMm,
                    'type' => 'payment',
                ], $data),
            ];

            // Send FCM request
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', [
                    'user_id' => $user->id,
                    'title' => $title,
                ]);
                return true;
            } else {
                Log::error('FCM notification failed', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Notify admin of new payment submission.
     * 
     * Validates: Requirement 13.4
     *
     * @param Payment $payment
     * @return void
     */
    public function notifyAdminOfNewPayment(Payment $payment): void
    {
        // Get admin users (users with 'admin' role)
        $admins = User::role('admin')->get();

        $title = 'New Payment Submitted';
        $titleMm = 'ငွေပေးချေမှုအသစ်တင်သွင်းပြီးပါပြီ';
        $body = sprintf(
            'Payment %s for %s MMK has been submitted and is pending verification.',
            $payment->payment_number,
            number_format($payment->payment_amount)
        );
        $bodyMm = sprintf(
            'ငွေပေးချေမှု %s အတွက် %s ကျပ် တင်သွင်းပြီး အတည်ပြုရန် စောင့်ဆိုင်းနေပါသည်။',
            $payment->payment_number,
            number_format($payment->payment_amount)
        );

        foreach ($admins as $admin) {
            $this->sendFCMNotification(
                $admin,
                $title,
                $titleMm,
                $body,
                $bodyMm,
                [
                    'payment_id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'action' => 'verify_payment',
                ]
            );
        }
    }

    /**
     * Notify guardian of payment verification.
     * 
     * Validates: Requirement 13.7
     *
     * @param Payment $payment
     * @return void
     */
    public function notifyGuardianOfVerification(Payment $payment): void
    {
        // Get guardian user from student
        $student = $payment->student;
        $primaryGuardian = $student->guardian()->first(); // Get first primary guardian
        
        if (!$primaryGuardian || !$primaryGuardian->user) {
            Log::warning('No primary guardian found for student', ['student_id' => $student->id]);
            return;
        }

        $guardian = $primaryGuardian->user;

        $title = 'Payment Verified';
        $titleMm = 'ငွေပေးချေမှု အတည်ပြုပြီးပါပြီ';
        $body = sprintf(
            'Your payment %s for %s MMK has been verified successfully.',
            $payment->payment_number,
            number_format($payment->payment_amount)
        );
        $bodyMm = sprintf(
            'သင်၏ငွေပေးချေမှု %s အတွက် %s ကျပ် အောင်မြင်စွာ အတည်ပြုပြီးပါပြီ။',
            $payment->payment_number,
            number_format($payment->payment_amount)
        );

        $this->sendFCMNotification(
            $guardian,
            $title,
            $titleMm,
            $body,
            $bodyMm,
            [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'action' => 'view_payment',
            ]
        );
    }

    /**
     * Notify guardian of payment rejection.
     * 
     * Validates: Requirement 13.7
     *
     * @param Payment $payment
     * @return void
     */
    public function notifyGuardianOfRejection(Payment $payment): void
    {
        // Get guardian user from student
        $student = $payment->student;
        $primaryGuardian = $student->guardian()->first(); // Get first primary guardian
        
        if (!$primaryGuardian || !$primaryGuardian->user) {
            Log::warning('No primary guardian found for student', ['student_id' => $student->id]);
            return;
        }

        $guardian = $primaryGuardian->user;

        $title = 'Payment Rejected';
        $titleMm = 'ငွေပေးချေမှု ပယ်ချခံရပါသည်';
        $body = sprintf(
            'Your payment %s has been rejected. Reason: %s',
            $payment->payment_number,
            $payment->rejection_reason ?? 'No reason provided'
        );
        $bodyMm = sprintf(
            'သင်၏ငွေပေးချေမှု %s ပယ်ချခံရပါသည်။ အကြောင်းရင်း: %s',
            $payment->payment_number,
            $payment->rejection_reason ?? 'အကြောင်းရင်းမဖော်ပြပါ'
        );

        $this->sendFCMNotification(
            $guardian,
            $title,
            $titleMm,
            $body,
            $bodyMm,
            [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'rejection_reason' => $payment->rejection_reason,
                'action' => 'resubmit_payment',
            ]
        );
    }
}

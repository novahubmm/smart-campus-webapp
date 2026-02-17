<?php

namespace App\Services\Finance;

use App\Models\PaymentProof;
use App\Models\NotificationLog;
use App\Models\StudentProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send rejection notification to guardian
     */
    public function sendRejectionNotification(PaymentProof $paymentProof, string $reason): void
    {
        $student = $paymentProof->student;
        $guardian = $student->guardians()->first();

        if (!$guardian || !$guardian->user) {
            return;
        }

        $data = [
            'type' => 'payment_proof_rejected',
            'student_name' => $student->user->name,
            'student_id' => $student->student_identifier,
            'payment_amount' => $paymentProof->payment_amount,
            'payment_date' => $paymentProof->payment_date->format('Y-m-d'),
            'rejection_reason' => $reason,
            'fee_details' => $this->getFeeDetails($paymentProof->fee_ids),
        ];

        // Send notification via existing notification system
        // This would integrate with your FCM/push notification system
        $this->sendNotification($guardian->user->id, 'Payment Proof Rejected', $data);

        // Log the notification
        $this->logNotification(
            $guardian->user->id,
            'rejection',
            $data,
            auth()->id()
        );
    }

    /**
     * Send reinform notification to guardian
     */
    public function sendReinformNotification(string $studentId, Collection $unpaidInvoices): void
    {
        $student = StudentProfile::with('user')->find($studentId);
        if (!$student) {
            return;
        }

        $guardian = $student->guardians()->first();
        if (!$guardian || !$guardian->user) {
            return;
        }

        $totalAmount = $unpaidInvoices->sum('balance');
        $feeDetails = $unpaidInvoices->map(function ($invoice) {
            return [
                'fee_type' => $invoice->feeStructure?->feeType?->name ?? 'Fee',
                'amount' => $invoice->balance,
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'month' => $invoice->month,
            ];
        })->toArray();

        $data = [
            'type' => 'payment_reminder',
            'student_name' => $student->user->name,
            'student_id' => $student->student_identifier,
            'total_amount' => $totalAmount,
            'fee_details' => $feeDetails,
            'due_date' => $unpaidInvoices->min('due_date')?->format('Y-m-d'),
        ];

        // Send notification
        $this->sendNotification(
            $guardian->user->id,
            'Payment Reminder',
            $data
        );

        // Log the notification
        $this->logNotification(
            $guardian->user->id,
            'reinform',
            $data,
            auth()->id()
        );
    }

    /**
     * Log notification for audit trail
     */
    private function logNotification(
        string $recipientId,
        string $type,
        array $data,
        ?string $triggeredBy = null
    ): void {
        NotificationLog::create([
            'recipient_id' => $recipientId,
            'notification_type' => $type,
            'data' => $data,
            'triggered_by' => $triggeredBy,
            'sent_at' => now(),
        ]);
    }

    /**
     * Get fee details from invoice IDs
     */
    private function getFeeDetails(?array $feeIds): array
    {
        if (!$feeIds) {
            return [];
        }

        $invoices = \App\Models\Invoice::whereIn('id', $feeIds)
            ->with('feeStructure.feeType')
            ->get();

        return $invoices->map(function ($invoice) {
            return [
                'fee_type' => $invoice->feeStructure?->feeType?->name ?? 'Fee',
                'amount' => $invoice->total_amount,
                'month' => $invoice->month,
            ];
        })->toArray();
    }

    /**
     * Send notification via existing notification system
     */
    private function sendNotification(string $userId, string $title, array $data): void
    {
        // This integrates with your existing notification system
        // You can use FCM, database notifications, or any other method
        
        // Example using Laravel's notification system:
        $user = \App\Models\User::find($userId);
        if ($user && $user->fcm_token) {
            // Send FCM notification
            // This would use your existing FCM service
            try {
                \App\Services\FirebaseService::sendNotification(
                    $user->fcm_token,
                    $title,
                    $data['type'] === 'payment_proof_rejected' 
                        ? "Your payment proof has been rejected. Reason: {$data['rejection_reason']}"
                        : "You have unpaid fees totaling {$data['total_amount']} MMK",
                    $data
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send notification: ' . $e->getMessage());
            }
        }
    }
}

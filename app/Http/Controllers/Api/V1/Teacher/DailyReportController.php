<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\DailyReportRecipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    /**
     * Get teacher's own daily reports (sent by teacher)
     */
    public function myReports(Request $request): JsonResponse
    {
        $user = $request->user();

        $reports = DailyReport::where('user_id', $user->id)
            ->where('direction', 'incoming')
            ->orderByDesc('created_at')
            ->paginate(10);

        $data = $reports->map(fn($r) => [
            'id' => $r->id,
            'title' => $r->subject,
            'recipient' => ucfirst($r->recipient),
            'description' => $r->message,
            'date' => $r->created_at->format('Y-m-d'),
            'category' => ucfirst($r->category),
            'status' => $this->formatStatus($r->status),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => $data,
                'pagination' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                ],
            ],
        ]);
    }

    /**
     * Get daily reports received by the teacher (sent from admin)
     */
    public function receivedReports(Request $request): JsonResponse
    {
        $user = $request->user();

        $reports = DailyReport::where('recipient_user_id', $user->id)
            ->where('direction', 'outgoing')
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(10);

        $data = $reports->map(fn($r) => [
            'id' => $r->id,
            'title' => $r->subject,
            'sender' => $r->user?->name ?? 'Admin',
            'recipient' => 'You',
            'description' => $r->message,
            'date' => $r->created_at->format('Y-m-d'),
            'category' => ucfirst($r->category),
            'status' => $this->formatStatus($r->status),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => $data,
                'pagination' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                ],
            ],
        ]);
    }
    
    /**
     * Format status for API response
     */
    private function formatStatus(string $status): string
    {
        return match($status) {
            'pending' => 'Under Review',
            'reviewed' => 'Reviewed',
            'resolved' => 'Resolved',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'acknowledged' => 'Acknowledged',
            default => ucfirst($status),
        };
    }

    /**
     * Get available recipients for daily reports
     * Returns fixed admin recipient
     */
    public function recipients(Request $request): JsonResponse
    {
        // Get the first admin user
        $adminUser = \App\Models\User::role('admin')->first();
        
        if (!$adminUser) {
            // Fallback if no admin found
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $recipients = [
            [
                'id' => $adminUser->id,
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'School Administration',
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $recipients,
        ]);
    }

    /**
     * Store a new daily report (from teacher)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'recipient' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $user = $request->user();

        $report = DailyReport::create([
            'user_id' => $user->id,
            'direction' => 'incoming',
            'category' => $validated['category'],
            'recipient' => $validated['recipient'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully',
            'data' => [
                'id' => $report->id,
                'title' => $report->subject,
                'recipient' => ucfirst($report->recipient),
                'description' => $report->message,
                'date' => $report->created_at->format('Y-m-d'),
                'category' => ucfirst($report->category),
                'status' => 'Under Review',
            ],
        ], 201);
    }

    /**
     * Show a specific daily report
     */
    public function show(string $id): JsonResponse
    {
        $report = DailyReport::with(['user', 'reviewedBy', 'recipientUser'])->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Daily report not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $report->id,
                'title' => $report->subject,
                'recipient' => ucfirst($report->recipient),
                'recipient_name' => $report->recipientUser?->name ?? ucfirst($report->recipient),
                'sender' => $report->user?->name ?? 'Unknown',
                'description' => $report->message,
                'full_message' => $report->message,
                'date' => $report->created_at->format('Y-m-d'),
                'category' => ucfirst($report->category),
                'status' => $this->formatStatus($report->status),
                'response' => $report->admin_remarks,
                'responded_by' => $report->reviewedBy?->name,
                'responded_at' => $report->reviewed_at?->toISOString(),
                'attachments' => [],
            ],
        ]);
    }

    /**
     * Update the status of a daily report
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,reviewed,resolved,approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ]);

        $report = DailyReport::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Daily report not found',
            ], 404);
        }

        $report->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_remarks' => $validated['remarks'] ?? $report->admin_remarks,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily report status updated',
            'data' => [
                'id' => $report->id,
                'status' => $report->status,
            ],
        ]);
    }
}

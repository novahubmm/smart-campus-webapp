<?php

namespace App\Http\Controllers;

use App\Models\DailyReportRecipient;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DailyReportRecipientController extends Controller
{
    use LogsActivity;
    public function index(Request $request): View|JsonResponse
    {
        $recipients = DailyReportRecipient::ordered()->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $recipients]);
        }

        return view('daily-report-recipients.index', compact('recipients'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:daily_report_recipients,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $recipient = DailyReportRecipient::create($validated);

        $this->logCreate('DailyReportRecipient', $recipient->id, $recipient->name);

        return response()->json([
            'success' => true,
            'message' => 'Recipient created successfully',
            'data' => $recipient,
        ], 201);
    }

    public function show(DailyReportRecipient $dailyReportRecipient): JsonResponse
    {
        return response()->json(['data' => $dailyReportRecipient]);
    }

    public function update(Request $request, DailyReportRecipient $dailyReportRecipient): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:daily_report_recipients,slug,' . $dailyReportRecipient->id],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $dailyReportRecipient->update($validated);

        $this->logUpdate('DailyReportRecipient', $dailyReportRecipient->id, $dailyReportRecipient->name);

        return response()->json([
            'success' => true,
            'message' => 'Recipient updated successfully',
            'data' => $dailyReportRecipient,
        ]);
    }

    public function destroy(DailyReportRecipient $dailyReportRecipient): JsonResponse
    {
        $recipientName = $dailyReportRecipient->name;
        $recipientId = $dailyReportRecipient->id;
        $dailyReportRecipient->delete();

        $this->logDelete('DailyReportRecipient', $recipientId, $recipientName);

        return response()->json([
            'success' => true,
            'message' => 'Recipient deleted successfully',
        ]);
    }
}

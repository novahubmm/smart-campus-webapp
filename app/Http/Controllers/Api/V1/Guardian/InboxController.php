<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Http\Controllers\Controller;
use App\Models\InboxMessage;
use App\Models\InboxMessageReply;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\GuardianProfile;

class InboxController extends Controller
{
    /**
     * List all inbox messages for the authenticated guardian.
     */
    public function index(Request $request): JsonResponse
    {
        $guardianProfile = $request->user()->guardianProfile;

        if (!$guardianProfile) {
            return response()->json(['error' => 'Guardian profile not found'], 404);
        }

        $messages = InboxMessage::with(['studentProfile'])
            ->where('guardian_profile_id', $guardianProfile->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $messages,
        ]);
    }

    /**
     * View a specific message and its replies.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $guardianProfile = $request->user()->guardianProfile;

        $inbox = InboxMessage::with(['studentProfile', 'replies.sender'])
            ->where('guardian_profile_id', $guardianProfile->id)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $inbox,
        ]);
    }

    /**
     * Send a new message to the school inbox.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_profile_id' => 'required|exists:student_profiles,id',
            'subject' => 'required|string|max:255',
            'category' => 'nullable|in:general,academic,behavior,health,complaint',
            'priority' => 'nullable|in:low,medium,high',
            'body' => 'required|string',
        ]);

        $guardianProfile = $request->user()->guardianProfile;

        if (!$guardianProfile) {
            return response()->json(['error' => 'Guardian profile not found'], 404);
        }

        // Verify that the student belongs to the guardian
        $studentExists = $guardianProfile->students()->where('student_profiles.id', $request->student_profile_id)->exists();
        if (!$studentExists) {
            return response()->json(['error' => 'Student does not belong to this guardian'], 403);
        }

        $inbox = InboxMessage::create([
            'guardian_profile_id' => $guardianProfile->id,
            'student_profile_id' => $request->student_profile_id,
            'subject' => $request->subject,
            'category' => $request->category ?? 'general',
            'priority' => $request->priority ?? 'medium',
            'status' => 'unread',
        ]);

        $inbox->replies()->create([
            'sender_type' => get_class($guardianProfile),
            'sender_id' => $guardianProfile->id,
            'body' => $request->body,
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $inbox->load('replies'),
        ], 201);
    }

    /**
     * Reply to an existing message thread.
     */
    public function reply(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $guardianProfile = $request->user()->guardianProfile;

        $inbox = InboxMessage::where('guardian_profile_id', $guardianProfile->id)
            ->findOrFail($id);

        $reply = $inbox->replies()->create([
            'sender_type' => get_class($guardianProfile),
            'sender_id' => $guardianProfile->id,
            'body' => $request->body,
            'is_read' => true,
        ]);

        // Option to change status back to unread for admins to notice
        $inbox->update(['status' => 'unread']);

        return response()->json([
            'status' => 'success',
            'message' => 'Reply sent successfully',
            'data' => $reply, // Optionally wrap in load('sender')
        ], 201);
    }
}

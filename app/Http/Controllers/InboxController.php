<?php

namespace App\Http\Controllers;

use App\Models\InboxMessage;
use App\Models\InboxMessageReply;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InboxController extends Controller
{
    public function index(Request $request): View
    {
        $query = InboxMessage::with(['guardianProfile.user', 'studentProfile.grade', 'studentProfile.classModel', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('grade_id')) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('grade_id', $request->grade_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        $messages = $query->latest()->paginate(15)->withQueryString();

        $grades = Grade::orderBy('level')->get();
        $classes = SchoolClass::orderBy('name')->get();

        $stats = [
            'total' => InboxMessage::count(),
            'unread' => InboxMessage::where('status', 'unread')->count(),
            'resolved' => InboxMessage::where('status', 'resolved')->count(),
        ];

        return view('inbox.index', compact('messages', 'grades', 'classes', 'stats'));
    }

    public function show(InboxMessage $inbox): View
    {
        $inbox->load(['guardianProfile.user', 'studentProfile.grade', 'studentProfile.classModel', 'assignedTo', 'replies.sender']);

        if ($inbox->status === 'unread') {
            $inbox->update(['status' => 'read']);
        }

        $teachers = TeacherProfile::with('user')->where('status', 'active')->get();

        return view('inbox.show', compact('inbox', 'teachers'));
    }

    public function reply(Request $request, InboxMessage $inbox): RedirectResponse
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $inbox->replies()->create([
            'sender_type' => get_class($request->user()),
            'sender_id' => $request->user()->id,
            'body' => $request->body,
            'is_read' => true,
        ]);

        return back()->with('status', 'Reply sent successfully.');
    }

    public function assign(Request $request, InboxMessage $inbox): RedirectResponse
    {
        $request->validate([
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
        ]);

        $inbox->update([
            'assigned_to_type' => TeacherProfile::class,
            'assigned_to_id' => $request->teacher_profile_id,
            'status' => 'assigned',
        ]);

        return back()->with('status', 'Message assigned to teacher successfully.');
    }

    public function updateStatus(Request $request, InboxMessage $inbox): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:unread,read,assigned,resolved,closed',
        ]);

        $inbox->update([
            'status' => $request->status,
        ]);

        return back()->with('status', 'Message status updated successfully.');
    }
}

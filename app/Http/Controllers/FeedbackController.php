<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Display feedback list (system admin only)
     */
    public function index(): View
    {
        $feedbacks = Feedback::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('system-admin.feedback.index', compact('feedbacks'));
    }

    /**
     * Display single feedback (system admin only)
     */
    public function show(Feedback $feedback): View
    {
        $feedback->load('user');
        return view('system-admin.feedback.show', compact('feedback'));
    }

    /**
     * Update feedback status (system admin only)
     */
    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,closed',
            'admin_notes' => 'nullable|string',
        ]);

        $feedback->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('system-admin.feedback.show', $feedback)
            ->with('success', 'Feedback updated successfully');
    }

    /**
     * Submit feedback form (public)
     */
    public function create(): View
    {
        return view('feedback.create');
    }

    /**
     * Store feedback (public)
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:bug,feature_request,general,complaint,suggestion',
        ]);

        Feedback::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        return redirect()->route('feedback.create')
            ->with('success', 'Thank you for your feedback! We will review it soon.');
    }
}

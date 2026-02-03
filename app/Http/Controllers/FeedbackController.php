<?php

namespace App\Http\Controllers;

use App\Services\ControlPanelService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    use LogsActivity;

    private ControlPanelService $controlPanelService;

    public function __construct(ControlPanelService $controlPanelService)
    {
        $this->controlPanelService = $controlPanelService;
    }

    /**
     * Show the feedback form
     */
    public function index()
    {
        return view('feedback.index');
    }

    /**
     * Submit feedback from web interface
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'category' => 'required|in:bug,feature,improvement,question,other',
            'priority' => 'required|in:low,normal,high,urgent'
        ]);

        $user = Auth::user();
        
        // Prepare feedback data
        $feedbackData = [
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'category' => $request->input('category'),
            'priority' => $request->input('priority'),
            'source' => 'web',
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->getRoleNames()->first() ?? 'user',
        ];

        // Send directly to Control Panel (no local storage)
        $sent = $this->controlPanelService->sendFeedback($feedbackData);

        if ($sent) {
            $this->logCreate('Feedback', $user->id, $request->input('title'));
            return redirect()->back()->with('success', 'Your feedback has been submitted successfully. Thank you!');
        } else {
            return redirect()->back()->with('error', 'Unable to submit feedback at this time. Please try again later or contact your administrator.');
        }
    }
}
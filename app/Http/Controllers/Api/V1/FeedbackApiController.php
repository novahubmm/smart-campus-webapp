<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ControlPanelService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class FeedbackApiController extends Controller
{
    private ControlPanelService $controlPanelService;

    public function __construct(ControlPanelService $controlPanelService)
    {
        $this->controlPanelService = $controlPanelService;
    }

    /**
     * Submit feedback from mobile apps (Guardian App, Teacher App)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'category' => 'required|in:bug,feature,improvement,question,other',
            'priority' => 'required|in:low,normal,high,urgent',
            'source' => 'required|in:guardian_app,teacher_app,mobile'
        ]);

        $user = Auth::user();
        
        // Prepare feedback data
        $feedbackData = [
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'category' => $request->input('category'),
            'priority' => $request->input('priority'),
            'source' => $request->input('source'),
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->getRoleNames()->first() ?? 'user',
        ];

        // Send directly to Control Panel (no local storage)
        $sent = $this->controlPanelService->sendFeedback($feedbackData);

        if ($sent) {
            return ApiResponse::success([
                'message' => 'Feedback submitted successfully'
            ], 'Your feedback has been submitted. Thank you!');
        } else {
            return ApiResponse::error(
                'Unable to submit feedback at this time. Please try again later.',
                500
            );
        }
    }

    /**
     * Get feedback categories for mobile apps
     */
    public function categories(): JsonResponse
    {
        $categories = [
            'bug' => 'Bug Report',
            'feature' => 'Feature Request',
            'improvement' => 'Improvement Suggestion',
            'question' => 'Question/Help',
            'other' => 'Other'
        ];

        return ApiResponse::success([
            'categories' => $categories
        ]);
    }

    /**
     * Get feedback priorities for mobile apps
     */
    public function priorities(): JsonResponse
    {
        $priorities = [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];

        return ApiResponse::success([
            'priorities' => $priorities
        ]);
    }
}
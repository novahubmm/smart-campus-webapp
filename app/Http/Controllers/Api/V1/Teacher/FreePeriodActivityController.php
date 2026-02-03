<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\FreePeriodActivityType;
use App\Models\TeacherFreePeriodActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FreePeriodActivityController extends Controller
{
    /**
     * GET /api/v1/teacher/free-period/activity-types
     * Returns list of available activity types
     */
    public function activityTypes(): JsonResponse
    {
        $types = FreePeriodActivityType::active()
            ->ordered()
            ->get()
            ->map(fn($type) => [
                'id' => $type->code,
                'label' => $type->localized_label,
                'color' => $type->color,
                'icon_svg' => $type->icon_svg,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'activity_types' => $types,
            ],
        ]);
    }

    /**
     * POST /api/v1/teacher/free-period/activities
     * Records teacher's activity during free period
     * Accepts single activity or array of activities
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
            ], 404);
        }

        // Get valid activity type codes
        $validCodes = FreePeriodActivityType::active()->pluck('code')->toArray();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'activities' => ['required', 'array', 'min:1'],
            'activities.*.activity_type' => ['required', 'string', Rule::in($validCodes)],
            'activities.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $date = $validated['date'];
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        $durationMinutes = TeacherFreePeriodActivity::calculateDuration($startTime, $endTime);

        $createdActivities = [];
        $errors = [];

        foreach ($validated['activities'] as $index => $activityData) {
            $activityType = FreePeriodActivityType::where('code', $activityData['activity_type'])->first();

            if (!$activityType) {
                $errors[] = "Activity type '{$activityData['activity_type']}' not found";
                continue;
            }

            // Check for duplicate (same teacher, date, start_time, activity_type) - include soft deleted
            $exists = TeacherFreePeriodActivity::withTrashed()
                ->where('teacher_profile_id', $teacherProfile->id)
                ->where('date', $date)
                ->where('start_time', $startTime)
                ->where('activity_type_id', $activityType->id)
                ->exists();

            if ($exists) {
                $errors[] = "Activity '{$activityType->localized_label}' already recorded for this time slot";
                continue;
            }

            try {
                $activity = TeacherFreePeriodActivity::create([
                    'teacher_profile_id' => $teacherProfile->id,
                    'activity_type_id' => $activityType->id,
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'duration_minutes' => $durationMinutes,
                    'notes' => $activityData['notes'] ?? null,
                ]);

                $createdActivities[] = [
                    'id' => $activity->id,
                    'teacher_id' => $teacherProfile->id,
                    'date' => $activity->date->format('Y-m-d'),
                    'start_time' => $activity->start_time,
                    'end_time' => $activity->end_time,
                    'duration_minutes' => $activity->duration_minutes,
                    'activity_type' => $activityType->code,
                    'activity_label' => $activityType->localized_label,
                    'notes' => $activity->notes,
                    'created_at' => $activity->created_at->toIso8601String(),
                ];
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $errors[] = "Activity '{$activityType->label}' already recorded for this time slot";
                continue;
            }
        }

        if (empty($createdActivities) && !empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record activities',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($createdActivities) . ' activity(ies) recorded successfully',
            'data' => count($createdActivities) === 1 ? $createdActivities[0] : $createdActivities,
            'errors' => $errors ?: null,
        ]);
    }

    /**
     * GET /api/v1/teacher/free-period/activities
     * Get teacher's recorded activities with optional date filter
     * Can optionally specify teacher_profile_id to view another teacher's activities
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'teacher_profile_id' => ['nullable', 'string', 'exists:teacher_profiles,id'],
            'date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'activity_type' => ['nullable', 'string'],
        ]);

        // If teacher_profile_id is provided, use that; otherwise use current user's profile
        if (!empty($validated['teacher_profile_id'])) {
            // Check if user has permission to view other teachers' profiles
            if (!$user->can(\App\Enums\PermissionEnum::VIEW_DEPARTMENTS_PROFILES->value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view other teacher profiles',
                ], 403);
            }
            $teacherProfileId = $validated['teacher_profile_id'];
        } else {
            $teacherProfile = $user->teacherProfile;
            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found',
                ], 404);
            }
            $teacherProfileId = $teacherProfile->id;
        }

        $query = TeacherFreePeriodActivity::where('teacher_profile_id', $teacherProfileId)
            ->with('activityType')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->orderBy('created_at', 'desc');

        if (!empty($validated['date'])) {
            $query->whereDate('date', $validated['date']);
        } elseif (!empty($validated['start_date']) && !empty($validated['end_date'])) {
            $query->whereBetween('date', [$validated['start_date'], $validated['end_date']]);
        }

        if (!empty($validated['activity_type'])) {
            $query->whereHas('activityType', fn($q) => $q->where('code', $validated['activity_type']));
        }

        $activities = $query->paginate(20)->withQueryString();

        $activitiesData = $activities->map(fn($activity) => [
            'id' => $activity->id,
            'teacher_profile_id' => $activity->teacher_profile_id,
            'date' => $activity->date->format('Y-m-d'),
            'start_time' => $activity->start_time,
            'end_time' => $activity->end_time,
            'duration_minutes' => $activity->duration_minutes,
            'activity_type' => $activity->activityType?->code,
            'activity_label' => $activity->activityType?->localized_label,
            'activity_color' => $activity->activityType?->color,
            'notes' => $activity->notes,
            'created_at' => $activity->created_at->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activitiesData,
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'from' => $activities->firstItem(),
                'to' => $activities->lastItem(),
            ],
        ]);
    }
}

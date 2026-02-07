<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreFreePeriodActivityRequest;
use App\Models\ActivityType;
use App\Models\FreePeriodActivity;
use App\Models\FreePeriodActivityItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FreePeriodActivityController extends Controller
{
    /**
     * GET /api/v1/free-period/activity-types
     * Returns list of available activity types
     */
    public function activityTypes(): JsonResponse
    {
        $types = ActivityType::active()
            ->ordered()
            ->get()
            ->map(fn($type) => [
                'id' => (string) $type->id,
                'label' => $type->label,
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
     * POST /api/v1/free-period/activities
     * Records teacher's activity during free period
     */
    public function store(StoreFreePeriodActivityRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $date = $validated['date'];
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        $durationMinutes = FreePeriodActivity::calculateDuration($startTime, $endTime);

        // Check for time overlap
        $overlap = FreePeriodActivity::where('teacher_id', $user->id)
            ->where('date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->first();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Time slot already has a recorded activity',
                'error' => [
                    'code' => 'TIME_OVERLAP',
                    'existing_record' => [
                        'id' => $overlap->id,
                        'start_time' => $overlap->start_time,
                        'end_time' => $overlap->end_time,
                    ],
                ],
            ], 409);
        }

        DB::beginTransaction();
        try {
            // Create main activity record
            $activityId = FreePeriodActivity::generateId($date);
            $activity = FreePeriodActivity::create([
                'id' => $activityId,
                'teacher_id' => $user->id,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
            ]);

            // Create activity items
            $activityItems = [];
            foreach ($validated['activities'] as $activityData) {
                $activityType = ActivityType::find($activityData['activity_type']);
                
                FreePeriodActivityItem::create([
                    'activity_id' => $activity->id,
                    'activity_type_id' => $activityType->id,
                    'notes' => $activityData['notes'] ?? null,
                ]);

                $activityItems[] = [
                    'activity_type' => [
                        'id' => (string) $activityType->id,
                        'label' => $activityType->label,
                        'color' => $activityType->color,
                        'icon_svg' => $activityType->icon_svg,
                    ],
                    'notes' => $activityData['notes'] ?? null,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activity recorded successfully',
                'data' => [
                    'id' => $activity->id,
                    'teacher_id' => $activity->teacher_id,
                    'date' => $activity->date->format('Y-m-d'),
                    'start_time' => $activity->start_time,
                    'end_time' => $activity->end_time,
                    'duration_minutes' => $activity->duration_minutes,
                    'activities' => $activityItems,
                    'created_at' => $activity->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record activity',
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * GET /api/v1/free-period/activities
     * Get teacher's recorded activities with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'week_offset' => ['nullable', 'integer'],
        ]);

        $query = FreePeriodActivity::where('teacher_id', $user->id)
            ->with('activityItems.activityType');

        // Apply date filters
        if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
            $query->whereBetween('date', [$validated['start_date'], $validated['end_date']]);
        } elseif (isset($validated['week_offset'])) {
            $weekOffset = (int) $validated['week_offset'];
            $startOfWeek = now()->addWeeks($weekOffset)->startOfWeek();
            $endOfWeek = now()->addWeeks($weekOffset)->endOfWeek();
            $query->whereBetween('date', [$startOfWeek, $endOfWeek]);
        } else {
            // Default: last 12 weeks
            $query->where('date', '>=', now()->subWeeks(12));
        }

        $activities = $query->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        // Calculate statistics
        $stats = $this->calculateActivityStats($activities);

        $activitiesData = $activities->map(function ($activity) {
            $activityItems = $activity->activityItems->map(function ($item) {
                return [
                    'activity_type' => [
                        'id' => (string) $item->activityType->id,
                        'label' => $item->activityType->label,
                        'color' => $item->activityType->color,
                        'icon_svg' => $item->activityType->icon_svg,
                    ],
                    'notes' => $item->notes,
                ];
            });

            return [
                'id' => $activity->id,
                'date' => $activity->date->format('Y-m-d'),
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
                'duration_minutes' => $activity->duration_minutes,
                'activities' => $activityItems,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activitiesData,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Calculate activity statistics
     */
    private function calculateActivityStats($activities): array
    {
        if ($activities->isEmpty()) {
            return [
                'total_records' => 0,
                'total_hours' => 0,
                'most_common_activity' => null,
                'activity_breakdown' => [],
            ];
        }

        $totalRecords = $activities->count();
        $totalMinutes = $activities->sum('duration_minutes');
        $totalHours = round($totalMinutes / 60, 1);

        // Count activity types
        $activityCounts = [];
        foreach ($activities as $activity) {
            foreach ($activity->activityItems as $item) {
                $label = $item->activityType->label;
                if (!isset($activityCounts[$label])) {
                    $activityCounts[$label] = 0;
                }
                $activityCounts[$label]++;
            }
        }

        arsort($activityCounts);
        $mostCommonActivity = !empty($activityCounts) ? array_key_first($activityCounts) : null;

        // Calculate breakdown
        $totalActivityItems = array_sum($activityCounts);
        $breakdown = [];
        foreach ($activityCounts as $label => $count) {
            $breakdown[] = [
                'activity_type' => $label,
                'count' => $count,
                'percentage' => round(($count / $totalActivityItems) * 100, 1),
            ];
        }

        return [
            'total_records' => $totalRecords,
            'total_hours' => $totalHours,
            'most_common_activity' => $mostCommonActivity,
            'activity_breakdown' => $breakdown,
        ];
    }
}

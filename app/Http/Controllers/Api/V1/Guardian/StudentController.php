<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Guardian\GuardianNoteResource;
use App\Http\Resources\Api\Guardian\StudentGoalResource;
use App\Interfaces\Guardian\GuardianStudentRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        private readonly GuardianStudentRepositoryInterface $studentRepository
    ) {}

    /**
     * Get Student Profile
     * GET /api/v1/guardian/students/{id}/profile
     */
    public function profile(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $profile = $this->studentRepository->getStudentProfile($student);

            return ApiResponse::success($profile);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Academic Summary
     * GET /api/v1/guardian/students/{id}/academic-summary
     */
    public function academicSummary(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $summary = $this->studentRepository->getAcademicSummary($student);

            return ApiResponse::success($summary);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve academic summary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Rankings
     * GET /api/v1/guardian/students/{id}/rankings
     */
    public function rankings(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $rankings = $this->studentRepository->getRankings($student);

            return ApiResponse::success($rankings);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve rankings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Achievements
     * GET /api/v1/guardian/students/{id}/achievements
     */
    public function achievements(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $achievements = $this->studentRepository->getAchievements($student);

            return ApiResponse::success($achievements);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve achievements: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Goals
     * GET /api/v1/guardian/students/{id}/goals
     */
    public function goals(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $goals = $this->studentRepository->getGoals($student);

            return ApiResponse::success(StudentGoalResource::collection($goals));
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve goals: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create Goal
     * POST /api/v1/guardian/students/{id}/goals
     */
    public function createGoal(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:gpa,attendance,rank,subject,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'target_date' => 'nullable|date',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $data = $request->all();
            $data['guardian_id'] = $request->user()->guardianProfile->id;

            $goal = $this->studentRepository->createGoal($student, $data);

            return ApiResponse::success(new StudentGoalResource($goal), 'Goal created successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create goal: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Goal
     * PUT /api/v1/guardian/students/{id}/goals/{goalId}
     */
    public function updateGoal(Request $request, string $id, string $goalId): JsonResponse
    {
        $request->validate([
            'type' => 'sometimes|string|in:gpa,attendance,rank,subject,other',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'target_date' => 'nullable|date',
            'status' => 'sometimes|string|in:in_progress,completed,cancelled',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $goal = $this->studentRepository->updateGoal($goalId, $request->all());

            return ApiResponse::success(new StudentGoalResource($goal), 'Goal updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update goal: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete Goal
     * DELETE /api/v1/guardian/students/{id}/goals/{goalId}
     */
    public function deleteGoal(Request $request, string $id, string $goalId): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $this->studentRepository->deleteGoal($goalId);

            return ApiResponse::success(null, 'Goal deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete goal: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Notes
     * GET /api/v1/guardian/students/{id}/notes
     */
    public function notes(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $notes = $this->studentRepository->getNotes($student);

            return ApiResponse::success(GuardianNoteResource::collection($notes));
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve notes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create Note
     * POST /api/v1/guardian/students/{id}/notes
     */
    public function createNote(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|in:academic,behavior,health,general',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $guardianId = $request->user()->guardianProfile->id;
            $note = $this->studentRepository->createNote($student, $guardianId, $request->all());

            return ApiResponse::success(new GuardianNoteResource($note), 'Note created successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create note: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Note
     * PUT /api/v1/guardian/students/{id}/notes/{noteId}
     */
    public function updateNote(Request $request, string $id, string $noteId): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'category' => 'nullable|string|in:academic,behavior,health,general',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $note = $this->studentRepository->updateNote($noteId, $request->all());

            return ApiResponse::success(new GuardianNoteResource($note), 'Note updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update note: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete Note
     * DELETE /api/v1/guardian/students/{id}/notes/{noteId}
     */
    public function deleteNote(Request $request, string $id, string $noteId): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $this->studentRepository->deleteNote($noteId);

            return ApiResponse::success(null, 'Note deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete note: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get GPA Trends
     * GET /api/v1/guardian/students/{id}/gpa-trends?months={months}
     */
    public function gpaTrends(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'months' => 'nullable|integer|min:1|max:24',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $months = $request->input('months', 12);
            $trends = $this->studentRepository->getGPATrends($student, $months);

            return ApiResponse::success($trends);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve GPA trends: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Performance Analysis
     * GET /api/v1/guardian/students/{id}/performance-analysis
     */
    public function performanceAnalysis(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $analysis = $this->studentRepository->getPerformanceAnalysis($student);

            return ApiResponse::success($analysis);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve performance analysis: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Strengths and Weaknesses
     * GET /api/v1/guardian/students/{id}/subject-strengths-weaknesses
     */
    public function subjectStrengthsWeaknesses(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $data = $this->studentRepository->getSubjectStrengthsWeaknesses($student);

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject analysis: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Academic Badges
     * GET /api/v1/guardian/students/{id}/badges
     */
    public function badges(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $badges = $this->studentRepository->getAcademicBadges($student);

            return ApiResponse::success($badges);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve badges: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Performance (Profile Screen)
     * GET /api/v1/guardian/students/{id}/profile/subject-performance
     */
    public function subjectPerformance(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $performance = $this->studentRepository->getSubjectPerformance($student);

            return ApiResponse::success($performance);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject performance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Progress Tracking (GPA & Rank History)
     * GET /api/v1/guardian/students/{id}/profile/progress-tracking?months=6
     */
    public function progressTracking(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'months' => 'nullable|integer|min:1|max:12',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $months = $request->input('months', 6);
            $tracking = $this->studentRepository->getProgressTracking($student, $months);

            return ApiResponse::success($tracking);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve progress tracking: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Comparison Data (Student vs Class Average)
     * GET /api/v1/guardian/students/{id}/profile/comparison
     */
    public function comparisonData(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $comparison = $this->studentRepository->getComparisonData($student);

            return ApiResponse::success($comparison);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve comparison data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Attendance Summary (Profile Screen)
     * GET /api/v1/guardian/students/{id}/profile/attendance-summary?months=3
     */
    public function attendanceSummary(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'months' => 'nullable|integer|min:1|max:12',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $months = $request->input('months', 3);
            $summary = $this->studentRepository->getAttendanceSummary($student, $months);

            return ApiResponse::success($summary);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve attendance summary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Rankings & Exam History (Profile Screen)
     * GET /api/v1/guardian/students/{id}/profile/rankings
     */
    public function rankingsData(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $rankings = $this->studentRepository->getRankingsData($student);

            return ApiResponse::success($rankings);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve rankings data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Complete Student Profile (Full Profile Tab)
     * GET /api/v1/guardian/students/{id}/profile/full
     */
    public function fullProfile(Request $request, string $id): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $profile = $this->studentRepository->getFullProfile($student);

            return ApiResponse::success($profile);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve full profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper to get authorized student
     */
    private function getAuthorizedStudent(Request $request, string $studentId): ?StudentProfile
    {
        $user = $request->user();
        $guardianProfile = $user->guardianProfile;

        if (!$guardianProfile) {
            return null;
        }

        return $guardianProfile->students()
            ->where('student_profiles.id', $studentId)
            ->with(['user', 'grade', 'classModel'])
            ->first();
    }
}

<?php

namespace App\Interfaces\Teacher;

use App\Models\User;
use Illuminate\Support\Collection;

interface TeacherClassRepositoryInterface
{
    // 1. Get all classes
    public function getMyClasses(User $teacher): array;

    // 2. Get class detail
    public function getClassDetail(User $teacher, string $classId): ?array;

    // 3. Get class students
    public function getClassStudents(User $teacher, string $classId, ?string $search = null, ?string $gender = null): ?array;

    // 3b. Get specific student detail within a class
    public function getClassStudentDetail(User $teacher, string $classId, string $studentId): ?array;

    // 4. Get class teachers
    public function getClassTeachers(User $teacher, string $classId): ?array;

    // 5. Get class timetable
    public function getClassTimetable(User $teacher, string $classId, ?string $date = null): ?array;

    // 6. Get class rankings
    public function getClassRankings(User $teacher, string $classId, ?string $examId = null, ?string $examType = null): ?array;

    // 7. Get class exams (for rankings dropdown)
    public function getClassExams(User $teacher, string $classId): ?array;

    // 9. Assign class leader
    public function assignClassLeader(User $teacher, string $classId, string $studentId): ?array;

    // 10. Get switch requests
    public function getSwitchRequests(User $teacher, string $classId, ?string $status = null, ?string $type = null): ?array;

    // 11. Create switch request
    public function createSwitchRequest(User $teacher, string $classId, array $data): ?array;

    // 12. Respond to switch request
    public function respondToSwitchRequest(User $teacher, string $classId, string $requestId, string $status): ?array;

    // 13. Get available teachers for switch
    public function getAvailableTeachers(User $teacher, string $classId, string $day, string $period): ?array;

    // Legacy: Get class statistics
    public function getClassStatistics(User $teacher, string $classId): ?array;

    // 14. Get student profile
    public function getStudentProfile(User $teacher, string $studentId): ?array;

    // 15. Get student academic
    public function getStudentAcademic(User $teacher, string $studentId, ?string $academicYear = null, ?string $term = null): ?array;

    // 16. Get student attendance
    public function getStudentAttendance(User $teacher, string $studentId, ?int $month = null, ?int $year = null): ?array;

    // 17. Get student remarks
    public function getStudentRemarks(User $teacher, string $studentId, ?string $type = null, ?string $category = null, ?string $dateFrom = null, ?string $dateTo = null): ?array;

    // 19. Get student rankings
    public function getStudentRankings(User $teacher, string $studentId): ?array;

    // 20. Get student ranking detail
    public function getStudentRankingDetail(User $teacher, string $studentId, string $examId): ?array;

    // 21. Get class student ranking details
    public function getClassStudentRankingDetails(User $teacher, string $classId, string $studentId, string $examId): ?array;

    // Dropdown helpers
    public function getClassesDropdown(User $teacher): Collection;
    public function getAttendanceDropdown(User $teacher): Collection;
}

<?php

namespace App\Repositories;

use App\DTOs\Timetable\PeriodData;
use App\DTOs\Timetable\TimetableData;
use App\DTOs\Timetable\TimetableFilterData;
use App\Interfaces\TimetableRepositoryInterface;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\StudentClass;
use App\Models\Timetable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TimetableRepository implements TimetableRepositoryInterface
{
    public function list(TimetableFilterData $filter): Collection
    {
        $query = Timetable::query()->with(['schoolClass', 'grade', 'batch', 'periods']);

        if ($filter->batch_id) {
            $query->where('batch_id', $filter->batch_id);
        }
        if ($filter->grade_id) {
            $query->where('grade_id', $filter->grade_id);
        }
        if ($filter->class_id) {
            $query->where('class_id', $filter->class_id);
        }

        if ($filter->teacher_profile_id) {
            $query->whereHas('schoolClass', function ($q) use ($filter) {
                $q->where('teacher_id', $filter->teacher_profile_id);
            });
        }

        if ($filter->student_id) {
            $query->whereIn('class_id', function ($q) use ($filter) {
                $q->select('class_id')->from((new StudentClass())->getTable())->where('student_id', $filter->student_id);
            });
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function getForClass(string $classId): ?Timetable
    {
        return Timetable::with('periods')->where('class_id', $classId)->first();
    }

    public function getForTeacherHomeroom(string $teacherProfileId): ?Timetable
    {
        $classId = SchoolClass::where('teacher_id', $teacherProfileId)->value('id');

        return $classId ? $this->getForClass($classId) : null;
    }

    public function getForStudent(string $studentId): ?Timetable
    {
        $classId = StudentClass::where('student_id', $studentId)->value('class_id');

        return $classId ? $this->getForClass($classId) : null;
    }

    public function storeTimetable(TimetableData $data, Collection $periods): Timetable
    {
        $timetable = Timetable::create($data->toArray());

        $this->syncPeriods($timetable, $periods);

        return $timetable->load('periods');
    }

    public function updateTimetable(Timetable $timetable, TimetableData $data, Collection $periods): Timetable
    {
        $timetable->update($data->toArray());

        $this->syncPeriods($timetable, $periods);

        return $timetable->load('periods');
    }

    public function activateTimetable(Timetable $timetable): Timetable
    {
        // Deactivate other timetables for the same class
        Timetable::where('class_id', $timetable->class_id)
            ->where('id', '!=', $timetable->id)
            ->update(['is_active' => false]);

        // Activate this timetable
        $timetable->update(['is_active' => true]);

        return $timetable->fresh('periods');
    }

    public function deleteTimetable(Timetable $timetable): bool
    {
        return $timetable->delete();
    }

    private function syncPeriods(Timetable $timetable, Collection $periods): void
    {
        // Use force delete to avoid unique constraint collisions from soft-deleted rows
        $timetable->periods()->withTrashed()->forceDelete();

        $periodPayloads = $periods->map(function (PeriodData $period) use ($timetable) {
            return array_merge($period->toArray(), [
                'id' => (string) Str::uuid(),
                'timetable_id' => $timetable->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        })->toArray();

        if (!empty($periodPayloads)) {
            Period::insert($periodPayloads);
        }
    }
}

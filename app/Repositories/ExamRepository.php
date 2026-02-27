<?php

namespace App\Repositories;

use App\DTOs\Exam\ExamData;
use App\DTOs\Exam\ExamFilterData;
use App\DTOs\Exam\ExamMarkData;
use App\Interfaces\ExamRepositoryInterface;
use App\Models\Exam;
use App\Models\ExamMark;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExamRepository implements ExamRepositoryInterface
{
    public function list(ExamFilterData $filter): LengthAwarePaginator
    {
        $query = Exam::query()
            ->with([
                'examType:id,name',
                'batch:id,name',
                'grade:id,level',
                'schoolClass:id,name,grade_id',
                'schedules.subject:id,name,code',
                'schedules.class:id,name',
            ])
            ->latest('start_date');

        $this->applyFilters($query, $filter);

        return $query->paginate($filter->perPage)->withQueryString();
    }

    public function stats(ExamFilterData $filter): array
    {
        $base = Exam::query();
        $this->applyFilters($base, $filter);

        $today = Carbon::today();

        $active = (clone $base)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $upcoming = (clone $base)
            ->where('status', 'upcoming')
            ->whereDate('start_date', '>', $today)
            ->count();

        $completed = (clone $base)
            ->whereIn('status', ['completed', 'finished'])
            ->count();

        return compact('active', 'upcoming', 'completed');
    }

    public function find(string $id): ?Exam
    {
        return Exam::with([
            'examType:id,name',
            'batch:id,name',
            'grade:id,level',
            'schoolClass:id,name,grade_id',
            'schedules.subject:id,name,code',
            'schedules.class:id,name',
        ])->find($id);
    }

    public function create(ExamData $data): Exam
    {
        return DB::transaction(function () use ($data) {
            /** @var Exam $exam */
            $exam = Exam::create($data->toArray());

            $this->syncSchedules($exam, $data);

            return $exam->fresh(['examType', 'batch', 'grade', 'schoolClass', 'schedules.subject', 'schedules.class']);
        });
    }

    public function update(Exam $exam, ExamData $data): Exam
    {
        return DB::transaction(function () use ($exam, $data) {
            $exam->update($data->toArray());

            $this->syncSchedules($exam, $data);

            return $exam->fresh(['examType', 'batch', 'grade', 'schoolClass', 'schedules.subject', 'schedules.class']);
        });
    }

    public function delete(Exam $exam): void
    {
        $exam->delete();
    }

    public function marksForExam(string $examId): Collection
    {
        return ExamMark::with(['student.user', 'subject:id,name,code'])
            ->where('exam_id', $examId)
            ->get();
    }

    public function storeMark(ExamMarkData $data): ExamMark
    {
        return ExamMark::create($data->toArray());
    }

    public function updateMark(ExamMark $mark, ExamMarkData $data): ExamMark
    {
        $mark->update($data->toArray());

        return $mark->fresh(['student.user', 'subject']);
    }

    public function deleteMark(ExamMark $mark): void
    {
        $mark->delete();
    }

    private function applyFilters($query, ExamFilterData $filter): void
    {
        if ($filter->exam_type_id) {
            $query->where('exam_type_id', $filter->exam_type_id);
        }

        if ($filter->batch_id) {
            $query->where('batch_id', $filter->batch_id);
        }

        if ($filter->grade_id) {
            $query->where('grade_id', $filter->grade_id);
        }

        if ($filter->class_id) {
            $query->where('class_id', $filter->class_id);
        }

        if ($filter->status && $filter->status !== 'all') {
            $today = Carbon::today();
            match ($filter->status) {
                'active' => $query->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today),
                'upcoming' => $query->where('status', 'upcoming'),
                'completed' => $query->where('status', 'completed'),
                'results' => $query->where('status', 'results'),
                default => null,
            };
        }

        if ($filter->month) {
            try {
                $monthDate = Carbon::createFromFormat('Y-m', $filter->month);
                $query->whereBetween('start_date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()]);
            } catch (\Exception $e) {
                // Ignore invalid month formats
            }
        }
    }

    private function syncSchedules(Exam $exam, ExamData $data): void
    {
        $exam->schedules()->delete();

        if (empty($data->schedules)) {
            return;
        }

        $exam->schedules()->createMany(
            collect($data->schedules)->map(fn($schedule) => array_merge($schedule->toArray(), [
                'exam_id' => $exam->id,
            ]))->all()
        );
    }
}

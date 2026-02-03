<?php

namespace App\Services;

use App\DTOs\Exam\ExamData;
use App\DTOs\Exam\ExamFilterData;
use App\DTOs\Exam\ExamMarkData;
use App\Interfaces\ExamRepositoryInterface;
use App\Models\Exam;
use App\Models\ExamMark;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExamService
{
    public function __construct(private readonly ExamRepositoryInterface $repository) {}

    public function list(ExamFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->list($filter);
    }

    public function stats(ExamFilterData $filter): array
    {
        return $this->repository->stats($filter);
    }

    public function find(string $id): ?Exam
    {
        return $this->repository->find($id);
    }

    public function create(ExamData $data): Exam
    {
        return $this->repository->create($data);
    }

    public function update(Exam $exam, ExamData $data): Exam
    {
        return $this->repository->update($exam, $data);
    }

    public function delete(Exam $exam): void
    {
        $this->repository->delete($exam);
    }

    public function marksForExam(string $examId): Collection
    {
        return $this->repository->marksForExam($examId);
    }

    public function storeMark(ExamMarkData $data): ExamMark
    {
        return $this->repository->storeMark($data);
    }

    public function updateMark(ExamMark $mark, ExamMarkData $data): ExamMark
    {
        return $this->repository->updateMark($mark, $data);
    }

    public function deleteMark(ExamMark $mark): void
    {
        $this->repository->deleteMark($mark);
    }
}

<?php

namespace App\Interfaces;

use App\DTOs\Exam\ExamData;
use App\DTOs\Exam\ExamFilterData;
use App\DTOs\Exam\ExamMarkData;
use App\Models\Exam;
use App\Models\ExamMark;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ExamRepositoryInterface
{
    public function list(ExamFilterData $filter): LengthAwarePaginator;

    public function stats(ExamFilterData $filter): array;

    public function find(string $id): ?Exam;

    public function create(ExamData $data): Exam;

    public function update(Exam $exam, ExamData $data): Exam;

    public function delete(Exam $exam): void;

    public function marksForExam(string $examId): Collection;

    public function storeMark(ExamMarkData $data): ExamMark;

    public function updateMark(ExamMark $mark, ExamMarkData $data): ExamMark;

    public function deleteMark(ExamMark $mark): void;
}

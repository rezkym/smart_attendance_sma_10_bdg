<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository implements StudentRepositoryInterface
{
    public function __construct(
        protected Student $model
    ) {}

    /**
     * @return Collection<int, Student>
     */
    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, Student>
     */
    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->active()
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findById(int $studentId): ?Student
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->find($studentId);
    }

    public function findByNisn(string $nisn): ?Student
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->where('nisn', $nisn)
            ->first();
    }

    public function findByNis(string $nis): ?Student
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->where('nis', $nis)
            ->first();
    }

    public function findByRfid(string $rfidCardNumber): ?Student
    {
        // Step 1: Try new rfid_cards table first (active card with user_id)
        $rfidCard = \App\Models\RfidCard::where('card_uid', $rfidCardNumber)
            ->where('status', \App\Enums\CardStatus::ACTIVE)
            ->whereNotNull('user_id')
            ->first();

        if ($rfidCard !== null) {
            return $this->model->newQuery()
                ->with(['user', 'enrollments.classroom'])
                ->whereHas('user', fn($query) => $query->where('id', $rfidCard->user_id))
                ->first();
        }

        // Step 2: Fallback to legacy students.rfid_card_number column
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->where('rfid_card_number', $rfidCardNumber)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Student
    {
        $student = $this->model->newQuery()->create($data);

        return $student->load(['user', 'enrollments.classroom']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Student $student, array $data): Student
    {
        $student->update($data);

        return $student->fresh()->load(['user', 'enrollments.classroom']);
    }

    public function delete(Student $student): bool
    {
        return (bool) $student->delete();
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getActiveCount(): int
    {
        return $this->model->newQuery()->active()->count();
    }

    /**
     * @return Builder<Student>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->orderBy('id', 'desc');
    }

    /**
     * @return Collection<int, Student>
     */
    public function getByClassroom(int $classroomId): Collection
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->inClassroom($classroomId)
            ->active()
            ->orderBy('id')
            ->get();
    }

    /**
     * Get students by classroom via enrollment table (Phase G - no fallback).
     *
     * @return Collection<int, Student>
     */
    public function getByClassroomViaEnrollment(int $classroomId, ?int $academicYearId = null): Collection
    {
        return $this->model->newQuery()
            ->with(['user', 'enrollments.classroom'])
            ->whereHas('enrollments', function ($q) use ($classroomId, $academicYearId) {
                $q->where('classroom_id', $classroomId)
                    ->where('status', \App\Enums\EnrollmentStatus::ACTIVE);

                if ($academicYearId !== null) {
                    $q->where('academic_year_id', $academicYearId);
                }
            })
            ->active()
            ->orderBy('id')
            ->get();
    }
}

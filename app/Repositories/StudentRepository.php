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
            ->with(['user', 'classroom'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, Student>
     */
    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->with(['user', 'classroom'])
            ->active()
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findById(int $studentId): ?Student
    {
        return $this->model->newQuery()
            ->with(['user', 'classroom'])
            ->find($studentId);
    }

    public function findByNisn(string $nisn): ?Student
    {
        return $this->model->newQuery()
            ->with(['user', 'classroom'])
            ->where('nisn', $nisn)
            ->first();
    }

    public function findByNis(string $nis): ?Student
    {
        return $this->model->newQuery()
            ->with(['user', 'classroom'])
            ->where('nis', $nis)
            ->first();
    }

    public function findByRfid(string $rfidCardNumber): ?Student
    {
        return $this->model->newQuery()
            ->with(['user', 'classroom'])
            ->where('rfid_card_number', $rfidCardNumber)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Student
    {
        $student = $this->model->newQuery()->create($data);

        return $student->load(['user', 'classroom']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Student $student, array $data): Student
    {
        $student->update($data);

        return $student->fresh()->load(['user', 'classroom']);
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
            ->with(['user', 'classroom'])
            ->orderBy('id', 'desc');
    }

    /**
     * @return Collection<int, Student>
     */
    public function getByClassroom(int $classroomId): Collection
    {
        return $this->model->newQuery()
            ->with(['user', 'classroom'])
            ->inClassroom($classroomId)
            ->active()
            ->orderBy('full_name')
            ->get();
    }
}

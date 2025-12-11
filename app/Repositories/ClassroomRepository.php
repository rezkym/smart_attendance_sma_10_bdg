<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Classroom;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ClassroomRepository implements ClassroomRepositoryInterface
{
    public function __construct(
        protected Classroom $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->active()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->where('academic_year_id', $academicYearId)
            ->active()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $classroomId): ?Classroom
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->find($classroomId);
    }

    public function create(array $data): Classroom
    {
        $classroom = $this->model->newQuery()->create($data);

        return $classroom->load('academicYear');
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        $classroom->update($data);

        return $classroom->fresh()->load('academicYear');
    }

    public function delete(Classroom $classroom): bool
    {
        return (bool) $classroom->delete();
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getActiveCount(): int
    {
        return $this->model->newQuery()->active()->count();
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->orderBy('grade_level')
            ->orderBy('name');
    }
}

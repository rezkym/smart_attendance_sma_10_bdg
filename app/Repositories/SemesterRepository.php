<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\SemesterType;
use App\Models\Semester;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SemesterRepository implements SemesterRepositoryInterface
{
    public function __construct(
        protected Semester $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->orderByDesc('start_date')
            ->get();
    }

    public function findById(int $semesterId): ?Semester
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->find($semesterId);
    }

    public function create(array $data): Semester
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Semester $semester, array $data): Semester
    {
        $semester->update($data);

        return $semester->fresh(['academicYear']);
    }

    public function delete(Semester $semester): bool
    {
        return (bool) $semester->delete();
    }

    public function getActive(): ?Semester
    {
        return $this->model->newQuery()
            ->active()
            ->with('academicYear')
            ->first();
    }

    public function deactivateAll(): void
    {
        $this->model->newQuery()
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->newQuery()
            ->byAcademicYear($academicYearId)
            ->orderBy('type')
            ->get();
    }

    public function existsByAcademicYearAndType(int $academicYearId, SemesterType $type, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()
            ->where('academic_year_id', $academicYearId)
            ->where('type', $type);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('academicYear')
            ->orderByDesc('start_date');
    }
}

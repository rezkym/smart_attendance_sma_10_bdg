<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AcademicYear;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AcademicYearRepository implements AcademicYearRepositoryInterface
{
    public function __construct(
        protected AcademicYear $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->latest()
            ->get();
    }

    public function findById(int $academicYearId): ?AcademicYear
    {
        return $this->model->newQuery()->find($academicYearId);
    }

    public function create(array $data): AcademicYear
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(AcademicYear $academicYear, array $data): AcademicYear
    {
        $academicYear->update($data);

        return $academicYear->fresh();
    }

    public function delete(AcademicYear $academicYear): bool
    {
        return (bool) $academicYear->delete();
    }

    public function getActive(): ?AcademicYear
    {
        return $this->model->newQuery()
            ->active()
            ->first();
    }

    public function deactivateAll(): void
    {
        $this->model->newQuery()
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->latest();
    }
}

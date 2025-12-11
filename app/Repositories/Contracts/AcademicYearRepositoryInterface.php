<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface AcademicYearRepositoryInterface
{
    /**
     * Get all academic years
     *
     * @return Collection<int, AcademicYear>
     */
    public function getAll(): Collection;

    /**
     * Find academic year by ID
     */
    public function findById(int $academicYearId): ?AcademicYear;

    /**
     * Create a new academic year
     *
     * @param array{name: string, start_date: string, end_date: string, is_active?: bool, description?: string|null} $data
     */
    public function create(array $data): AcademicYear;

    /**
     * Update academic year
     *
     * @param array{name?: string, start_date?: string, end_date?: string, is_active?: bool, description?: string|null} $data
     */
    public function update(AcademicYear $academicYear, array $data): AcademicYear;

    /**
     * Delete academic year
     */
    public function delete(AcademicYear $academicYear): bool;

    /**
     * Get the active academic year
     */
    public function getActive(): ?AcademicYear;

    /**
     * Deactivate all academic years
     */
    public function deactivateAll(): void;

    /**
     * Get total count
     */
    public function getTotalCount(): int;

    /**
     * Get query builder for DataTables
     */
    public function getDataTableQuery(): Builder;
}

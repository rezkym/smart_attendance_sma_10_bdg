<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\SemesterType;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface SemesterRepositoryInterface
{
    /**
     * Get all semesters with academic year relationship.
     *
     * @return Collection<int, Semester>
     */
    public function getAll(): Collection;

    /**
     * Find semester by ID with relationships.
     */
    public function findById(int $semesterId): ?Semester;

    /**
     * Create a new semester.
     *
     * @param array{academic_year_id: int, type: int, start_date: string, end_date: string, is_active?: bool} $data
     */
    public function create(array $data): Semester;

    /**
     * Update semester.
     *
     * @param array{academic_year_id?: int, type?: int, start_date?: string, end_date?: string, is_active?: bool} $data
     */
    public function update(Semester $semester, array $data): Semester;

    /**
     * Delete semester.
     */
    public function delete(Semester $semester): bool;

    /**
     * Get the currently active semester.
     */
    public function getActive(): ?Semester;

    /**
     * Deactivate all semesters.
     */
    public function deactivateAll(): void;

    /**
     * Get semesters by academic year.
     *
     * @return Collection<int, Semester>
     */
    public function getByAcademicYear(int $academicYearId): Collection;

    /**
     * Check if semester type already exists for academic year.
     */
    public function existsByAcademicYearAndType(int $academicYearId, SemesterType $type, ?int $excludeId = null): bool;

    /**
     * Get total count.
     */
    public function getTotalCount(): int;

    /**
     * Get query builder for DataTables.
     */
    public function getDataTableQuery(): Builder;
}

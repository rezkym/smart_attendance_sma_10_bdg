<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface ClassroomRepositoryInterface
{
    /**
     * Get all classrooms with academicYear relation
     *
     * @return Collection<int, Classroom>
     */
    public function getAll(): Collection;

    /**
     * Get all active classrooms
     *
     * @return Collection<int, Classroom>
     */
    public function getAllActive(): Collection;

    /**
     * Get classrooms by academic year
     *
     * @return Collection<int, Classroom>
     */
    public function getByAcademicYear(int $academicYearId): Collection;

    /**
     * Find classroom by ID with academicYear relation
     */
    public function findById(int $classroomId): ?Classroom;

    /**
     * Create a new classroom
     *
     * @param array{name: string, grade_level: int, academic_year_id: int, capacity?: int|null, description?: string|null, is_active?: bool} $data
     */
    public function create(array $data): Classroom;

    /**
     * Update classroom
     *
     * @param array{name?: string, grade_level?: int, academic_year_id?: int, capacity?: int|null, description?: string|null, is_active?: bool} $data
     */
    public function update(Classroom $classroom, array $data): Classroom;

    /**
     * Delete classroom
     */
    public function delete(Classroom $classroom): bool;

    /**
     * Get total count
     */
    public function getTotalCount(): int;

    /**
     * Get active count
     */
    public function getActiveCount(): int;

    /**
     * Get query builder for DataTables with academicYear eager loading
     */
    public function getDataTableQuery(): Builder;
}

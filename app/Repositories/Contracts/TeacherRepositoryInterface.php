<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface TeacherRepositoryInterface
{
    /**
     * Get all teachers with user relation
     *
     * @return Collection<int, Teacher>
     */
    public function getAll(): Collection;

    /**
     * Get all active teachers
     *
     * @return Collection<int, Teacher>
     */
    public function getAllActive(): Collection;

    /**
     * Find teacher by ID with user relation
     */
    public function findById(int $teacherId): ?Teacher;

    /**
     * Find teacher by user ID
     */
    public function findByUserId(int $userId): ?Teacher;

    /**
     * Create a new teacher
     *
     * @param array{user_id: int, nip?: string|null, phone?: string|null, address?: string|null, is_active?: bool} $data
     */
    public function create(array $data): Teacher;

    /**
     * Update teacher
     *
     * @param array{nip?: string|null, phone?: string|null, address?: string|null, is_active?: bool} $data
     */
    public function update(Teacher $teacher, array $data): Teacher;

    /**
     * Delete teacher
     */
    public function delete(Teacher $teacher): bool;

    /**
     * Get total count
     */
    public function getTotalCount(): int;

    /**
     * Get active count
     */
    public function getActiveCount(): int;

    /**
     * Get query builder for DataTables with user eager loading
     */
    public function getDataTableQuery(): Builder;
}

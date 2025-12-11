<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface SubjectRepositoryInterface
{
    /**
     * Get all subjects
     *
     * @return Collection<int, Subject>
     */
    public function getAll(): Collection;

    /**
     * Get all active subjects
     *
     * @return Collection<int, Subject>
     */
    public function getAllActive(): Collection;

    /**
     * Find subject by ID
     */
    public function findById(int $subjectId): ?Subject;

    /**
     * Create a new subject
     *
     * @param array{code: string, name: string, description?: string|null, is_active?: bool} $data
     */
    public function create(array $data): Subject;

    /**
     * Update subject
     *
     * @param array{code?: string, name?: string, description?: string|null, is_active?: bool} $data
     */
    public function update(Subject $subject, array $data): Subject;

    /**
     * Delete subject
     */
    public function delete(Subject $subject): bool;

    /**
     * Get total count
     */
    public function getTotalCount(): int;

    /**
     * Get active count
     */
    public function getActiveCount(): int;

    /**
     * Get query builder for DataTables
     */
    public function getDataTableQuery(): Builder;
}

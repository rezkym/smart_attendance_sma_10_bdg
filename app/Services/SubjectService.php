<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SubjectService
{
    public function __construct(
        protected SubjectRepositoryInterface $subjectRepository
    ) {}

    /**
     * Get all subjects
     *
     * @return Collection<int, Subject>
     */
    public function getAllSubjects(): Collection
    {
        return $this->subjectRepository->getAll();
    }

    /**
     * Get all active subjects
     *
     * @return Collection<int, Subject>
     */
    public function getAllActiveSubjects(): Collection
    {
        return $this->subjectRepository->getAllActive();
    }

    /**
     * Get subject by ID
     */
    public function getSubjectById(int $subjectId): ?Subject
    {
        return $this->subjectRepository->findById($subjectId);
    }

    /**
     * Create a new subject
     *
     * @param array{code: string, name: string, description?: string|null, is_active?: bool} $data
     */
    public function createSubject(array $data): Subject
    {
        return $this->subjectRepository->create($data);
    }

    /**
     * Update subject
     *
     * @param array{code?: string, name?: string, description?: string|null, is_active?: bool} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateSubject(int $subjectId, array $data): Subject
    {
        $subject = $this->subjectRepository->findById($subjectId);

        if ($subject === null) {
            throw new \InvalidArgumentException("Subject with ID {$subjectId} not found.");
        }

        return $this->subjectRepository->update($subject, $data);
    }

    /**
     * Delete subject
     *
     * @throws \InvalidArgumentException
     */
    public function deleteSubject(int $subjectId): bool
    {
        $subject = $this->subjectRepository->findById($subjectId);

        if ($subject === null) {
            throw new \InvalidArgumentException("Subject with ID {$subjectId} not found.");
        }

        // TODO: Add check for related schedules/timetables when that feature is implemented

        return $this->subjectRepository->delete($subject);
    }

    /**
     * Get statistics for dashboard cards
     *
     * @return array{total: int, active: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->subjectRepository->getTotalCount(),
            'active' => $this->subjectRepository->getActiveCount(),
        ];
    }

    /**
     * Get DataTables query builder
     */
    public function getDataTableQuery(): Builder
    {
        return $this->subjectRepository->getDataTableQuery();
    }
}

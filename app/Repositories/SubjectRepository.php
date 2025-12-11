<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SubjectRepository implements SubjectRepositoryInterface
{
    public function __construct(
        protected Subject $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->orderBy('name')
            ->get();
    }

    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function findById(int $subjectId): ?Subject
    {
        return $this->model->newQuery()->find($subjectId);
    }

    public function create(array $data): Subject
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update($data);

        return $subject->fresh();
    }

    public function delete(Subject $subject): bool
    {
        return (bool) $subject->delete();
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
            ->orderBy('name');
    }
}

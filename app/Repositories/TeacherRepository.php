<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TeacherRepository implements TeacherRepositoryInterface
{
    public function __construct(
        protected Teacher $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->with('user')
            ->active()
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findById(int $teacherId): ?Teacher
    {
        return $this->model->newQuery()
            ->with('user')
            ->find($teacherId);
    }

    public function findByUserId(int $userId): ?Teacher
    {
        return $this->model->newQuery()
            ->with('user')
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Teacher
    {
        $teacher = $this->model->newQuery()->create($data);

        return $teacher->load('user');
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $teacher->update($data);

        return $teacher->fresh()->load('user');
    }

    public function delete(Teacher $teacher): bool
    {
        return (bool) $teacher->delete();
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
            ->with('user')
            ->orderBy('id', 'desc');
    }
}

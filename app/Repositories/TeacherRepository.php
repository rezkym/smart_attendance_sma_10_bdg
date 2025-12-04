<?php

namespace App\Repositories;

use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TeacherRepository implements TeacherRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        $this->applyFilters($query, $filters, includeSearch: true);

        return $query->paginate($perPage);
    }

    public function datatableQuery(array $filters = []): Builder
    {
        $query = $this->baseQuery()->select('teachers.*');

        $this->applyFilters($query, $filters, includeSearch: false);

        return $query;
    }

    public function findById(int $id): ?Teacher
    {
        return Teacher::with(['user.roles', 'subjects', 'homeroomClassrooms'])->find($id);
    }

    public function create(array $data): Teacher
    {
        return Teacher::create($data);
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $teacher->fill($data);
        $teacher->save();

        return $teacher;
    }

    public function delete(Teacher $teacher): void
    {
        $teacher->delete();
    }

    public function syncSubjects(Teacher $teacher, array $subjectIds): Teacher
    {
        $teacher->subjects()->sync($subjectIds);

        return $teacher->load('subjects');
    }

    private function baseQuery(): Builder
    {
        return Teacher::query()
            ->with(['user.roles', 'subjects', 'homeroomClassrooms'])
            ->latest('id');
    }

    private function applyFilters(Builder $query, array $filters, bool $includeSearch = true): void
    {
        if (! empty($filters['subject_id'])) {
            $query->whereHas('subjects', function (Builder $subjectQuery) use ($filters) {
                $subjectQuery->where('subjects.id', $filters['subject_id']);
            });
        }

        if (! empty($filters['classroom_id'])) {
            $query->whereHas('homeroomClassrooms', function (Builder $classroomQuery) use ($filters) {
                $classroomQuery->where('classrooms.id', $filters['classroom_id']);
            });
        }

        if ($includeSearch && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('teacher_number', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
    }
}

<?php

namespace App\Repositories\Contracts;

use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface TeacherRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function datatableQuery(array $filters = []): Builder;

    public function findById(int $id): ?Teacher;

    public function create(array $data): Teacher;

    public function update(Teacher $teacher, array $data): Teacher;

    public function delete(Teacher $teacher): void;

    public function syncSubjects(Teacher $teacher, array $subjectIds): Teacher;
}

<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StudentRepository implements StudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::query()
            ->with(['user.roles', 'classroom'])
            ->latest('id');

        if (! empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                    ->orWhere('rfid_card_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    public function datatableQuery(array $filters = []): Builder
    {
        $query = Student::query()
            ->with(['user.roles', 'classroom'])
            ->select('students.*');

        if (! empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }

        return $query;
    }

    public function findById(int $id): ?Student
    {
        return Student::with(['user.roles', 'classroom'])->find($id);
    }

    public function create(array $data): Student
    {
        return Student::create($data);
    }

    public function update(Student $student, array $data): Student
    {
        $student->fill($data);
        $student->save();

        return $student;
    }

    public function delete(Student $student): void
    {
        $student->delete();
    }
}

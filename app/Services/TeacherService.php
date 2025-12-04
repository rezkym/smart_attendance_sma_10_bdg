<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\User;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherService
{
    public function __construct(
        private readonly TeacherRepositoryInterface $teacherRepository
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->teacherRepository->paginate($filters, $perPage);
    }

    public function datatableQuery(array $filters = []): Builder
    {
        return $this->teacherRepository->datatableQuery($filters);
    }

    public function findById(int $id): ?Teacher
    {
        return $this->teacherRepository->findById($id);
    }

    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            $teacherData = Arr::only($data, [
                'user_id',
                'teacher_number',
                'date_of_birth',
                'gender',
                'address',
                'phone_number',
                'specialization',
            ]);

            $user = User::findOrFail($data['user_id']);

            if ($user->teacher()->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => ['User ini sudah memiliki data guru.'],
                ]);
            }

            if (! $user->hasRole('teacher')) {
                throw ValidationException::withMessages([
                    'user_id' => ['User yang dipilih harus memiliki role teacher.'],
                ]);
            }

            $teacher = $this->teacherRepository->create($teacherData);

            return $teacher->load(['user.roles', 'subjects', 'homeroomClassrooms']);
        });
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        return DB::transaction(function () use ($teacher, $data) {
            $teacherData = Arr::only($data, [
                'teacher_number',
                'date_of_birth',
                'gender',
                'address',
                'phone_number',
                'specialization',
            ]);

            $updated = $this->teacherRepository->update($teacher, $teacherData);

            return $updated->load(['user.roles', 'subjects', 'homeroomClassrooms']);
        });
    }

    public function syncSubjects(Teacher $teacher, array $subjectIds): Teacher
    {
        return DB::transaction(function () use ($teacher, $subjectIds) {
            $updated = $this->teacherRepository->syncSubjects($teacher, $subjectIds);

            return $updated->load(['user.roles', 'subjects', 'homeroomClassrooms']);
        });
    }

    public function assignHomeroom(?int $classroomId, Teacher $teacher): Teacher
    {
        return DB::transaction(function () use ($classroomId, $teacher) {
            if ($classroomId === null) {
                Classroom::where('homeroom_teacher_id', $teacher->id)->update([
                    'homeroom_teacher_id' => null,
                ]);

                return $teacher->load(['user.roles', 'subjects', 'homeroomClassrooms']);
            }

            $classroom = Classroom::findOrFail($classroomId);

            Classroom::where('homeroom_teacher_id', $teacher->id)
                ->where('id', '!=', $classroom->id)
                ->update(['homeroom_teacher_id' => null]);

            $classroom->homeroom_teacher_id = $teacher->id;
            $classroom->save();

            return $teacher->load(['user.roles', 'subjects', 'homeroomClassrooms']);
        });
    }

    public function delete(Teacher $teacher): void
    {
        DB::transaction(function () use ($teacher) {
            $teacher->subjects()->sync([]);
            $this->teacherRepository->delete($teacher);
        });
    }
}

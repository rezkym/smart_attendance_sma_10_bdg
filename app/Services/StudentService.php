<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function __construct(
        private readonly StudentRepositoryInterface $studentRepository
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->studentRepository->paginate($filters, $perPage);
    }

    public function datatableQuery(array $filters = []): Builder
    {
        return $this->studentRepository->datatableQuery($filters);
    }

    public function findById(int $id): ?Student
    {
        return $this->studentRepository->findById($id);
    }

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $studentData = Arr::only($data, [
                'user_id',
                'student_number',
                'rfid_card_number',
                'date_of_birth',
                'gender',
                'address',
                'phone_number',
                'parent_name',
                'parent_phone',
                'classroom_id',
            ]);

            // Validasi user_id harus ada dan belum punya student record
            $user = User::findOrFail($data['user_id']);

            if ($user->student()->exists()) {
                throw new \Exception('User ini sudah memiliki data siswa.');
            }

            // Create student record yang ter-link ke user
            $student = $this->studentRepository->create($studentData);

            return $student->load(['user.roles', 'classroom']);
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            // Update hanya data student, tidak termasuk user
            // User data di-manage di User Management
            $studentData = Arr::only($data, [
                'student_number',
                'rfid_card_number',
                'date_of_birth',
                'gender',
                'address',
                'phone_number',
                'parent_name',
                'parent_phone',
                'classroom_id',
            ]);

            $updated = $this->studentRepository->update($student, $studentData);

            return $updated->load(['user.roles', 'classroom']);
        });
    }

    public function assignRfid(Student $student, ?string $rfidCardNumber): Student
    {
        return DB::transaction(function () use ($student, $rfidCardNumber) {
            $updated = $this->studentRepository->update($student, [
                'rfid_card_number' => $rfidCardNumber,
            ]);

            return $updated->load(['user.roles', 'classroom']);
        });
    }

    public function assignClassroom(Student $student, ?int $classroomId): Student
    {
        return DB::transaction(function () use ($student, $classroomId) {
            $updated = $this->studentRepository->update($student, [
                'classroom_id' => $classroomId,
            ]);

            return $updated->load(['user.roles', 'classroom']);
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $this->studentRepository->delete($student);
        });
    }
}

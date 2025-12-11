<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TeacherService
{
    public function __construct(
        protected TeacherRepositoryInterface $teacherRepository
    ) {}

    /**
     * Get all teachers
     *
     * @return Collection<int, Teacher>
     */
    public function getAllTeachers(): Collection
    {
        return $this->teacherRepository->getAll();
    }

    /**
     * Get all active teachers
     *
     * @return Collection<int, Teacher>
     */
    public function getAllActiveTeachers(): Collection
    {
        return $this->teacherRepository->getAllActive();
    }

    /**
     * Get teacher by ID
     */
    public function getTeacherById(int $teacherId): ?Teacher
    {
        return $this->teacherRepository->findById($teacherId);
    }

    /**
     * Get users with teacher role who don't have a teacher profile yet
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getAvailableUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::role('teacher')
            ->whereDoesntHave('teacher')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new teacher profile for existing user
     *
     * @param array{user_id: int, nip?: string|null, phone?: string|null, address?: string|null, is_active?: bool} $data
     *
     * @throws \InvalidArgumentException
     */
    public function createTeacher(array $data): Teacher
    {
        $user = User::find($data['user_id']);

        if ($user === null) {
            throw new \InvalidArgumentException('Selected user not found.');
        }

        // Check if user already has a teacher profile
        if ($user->teacher !== null) {
            throw new \InvalidArgumentException('This user already has a teacher profile.');
        }

        // Create teacher profile
        return $this->teacherRepository->create([
            'user_id' => $user->id,
            'nip' => $data['nip'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update teacher profile data only (user data is not editable)
     *
     * @param array{nip?: string|null, phone?: string|null, address?: string|null, is_active?: bool} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateTeacher(int $teacherId, array $data): Teacher
    {
        $teacher = $this->teacherRepository->findById($teacherId);

        if ($teacher === null) {
            throw new \InvalidArgumentException("Teacher with ID {$teacherId} not found.");
        }

        // Update teacher profile only
        $teacherData = [];
        if (array_key_exists('nip', $data)) {
            $teacherData['nip'] = $data['nip'];
        }
        if (array_key_exists('phone', $data)) {
            $teacherData['phone'] = $data['phone'];
        }
        if (array_key_exists('address', $data)) {
            $teacherData['address'] = $data['address'];
        }
        if (isset($data['is_active'])) {
            $teacherData['is_active'] = $data['is_active'];
        }

        if (count($teacherData) > 0) {
            return $this->teacherRepository->update($teacher, $teacherData);
        }

        return $teacher->fresh()->load('user');
    }

    /**
     * Delete teacher profile only (user remains but loses teacher role)
     *
     * @throws \InvalidArgumentException
     */
    public function deleteTeacher(int $teacherId): bool
    {
        $teacher = $this->teacherRepository->findById($teacherId);

        if ($teacher === null) {
            throw new \InvalidArgumentException("Teacher with ID {$teacherId} not found.");
        }

        return DB::transaction(function () use ($teacher) {
            // Remove teacher role from user
            $teacher->user->removeRole('teacher');

            // Delete only the teacher profile, user account remains
            return $this->teacherRepository->delete($teacher);
        });
    }

    /**
     * Get statistics for dashboard cards
     *
     * @return array{total: int, active: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->teacherRepository->getTotalCount(),
            'active' => $this->teacherRepository->getActiveCount(),
        ];
    }

    /**
     * Get DataTables query builder
     */
    public function getDataTableQuery(): Builder
    {
        return $this->teacherRepository->getDataTableQuery();
    }
}

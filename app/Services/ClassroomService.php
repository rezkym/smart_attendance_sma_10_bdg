<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Classroom;
use App\Models\Teacher;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ClassroomService
{
    public function __construct(
        protected ClassroomRepositoryInterface $classroomRepository,
        protected TeacherRepositoryInterface $teacherRepository
    ) {}

    /**
     * Get all classrooms
     *
     * @return Collection<int, Classroom>
     */
    public function getAllClassrooms(): Collection
    {
        return $this->classroomRepository->getAll();
    }

    /**
     * Get all active classrooms
     *
     * @return Collection<int, Classroom>
     */
    public function getAllActiveClassrooms(): Collection
    {
        return $this->classroomRepository->getAllActive();
    }

    /**
     * Get classrooms by academic year
     *
     * @return Collection<int, Classroom>
     */
    public function getClassroomsByAcademicYear(int $academicYearId): Collection
    {
        return $this->classroomRepository->getByAcademicYear($academicYearId);
    }

    /**
     * Get classroom by ID
     */
    public function getClassroomById(int $classroomId): ?Classroom
    {
        return $this->classroomRepository->findById($classroomId);
    }

    /**
     * Create a new classroom
     *
     * @param array{name: string, grade_level: int, academic_year_id: int, homeroom_teacher_id?: int|null, capacity?: int|null, description?: string|null, is_active?: bool} $data
     */
    public function createClassroom(array $data): Classroom
    {
        return DB::transaction(function () use ($data) {
            return $this->classroomRepository->create([
                'name' => $data['name'],
                'grade_level' => $data['grade_level'],
                'academic_year_id' => $data['academic_year_id'],
                'homeroom_teacher_id' => $data['homeroom_teacher_id'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update classroom
     *
     * @param array{name?: string, grade_level?: int, academic_year_id?: int, homeroom_teacher_id?: int|null, capacity?: int|null, description?: string|null, is_active?: bool} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateClassroom(int $classroomId, array $data): Classroom
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if ($classroom === null) {
            throw new \InvalidArgumentException("Classroom with ID {$classroomId} not found.");
        }

        return DB::transaction(function () use ($classroom, $data) {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            if (isset($data['grade_level'])) {
                $updateData['grade_level'] = $data['grade_level'];
            }
            if (isset($data['academic_year_id'])) {
                $updateData['academic_year_id'] = $data['academic_year_id'];
            }
            if (array_key_exists('homeroom_teacher_id', $data)) {
                $updateData['homeroom_teacher_id'] = $data['homeroom_teacher_id'];
            }
            if (array_key_exists('capacity', $data)) {
                $updateData['capacity'] = $data['capacity'];
            }
            if (array_key_exists('description', $data)) {
                $updateData['description'] = $data['description'];
            }
            if (isset($data['is_active'])) {
                $updateData['is_active'] = $data['is_active'];
            }

            if (count($updateData) > 0) {
                return $this->classroomRepository->update($classroom, $updateData);
            }

            return $classroom->fresh()->load(['academicYear', 'homeroomTeacher.user']);
        });
    }

    /**
     * Delete classroom
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function deleteClassroom(int $classroomId): bool
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if ($classroom === null) {
            throw new \InvalidArgumentException("Classroom with ID {$classroomId} not found.");
        }

        // TODO: Check if classroom has students before deleting
        // if ($classroom->students()->count() > 0) {
        //     throw new \RuntimeException('Cannot delete classroom that has students assigned.');
        // }

        return $this->classroomRepository->delete($classroom);
    }

    /**
     * Get statistics for dashboard cards
     *
     * @return array{total: int, active: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->classroomRepository->getTotalCount(),
            'active' => $this->classroomRepository->getActiveCount(),
        ];
    }

    /**
     * Get DataTables query builder
     */
    public function getDataTableQuery(): Builder
    {
        return $this->classroomRepository->getDataTableQuery();
    }

    /**
     * Get available homeroom teachers (active teachers)
     *
     * @return Collection<int, Teacher>
     */
    public function getAvailableHomeroomTeachers(): Collection
    {
        return $this->teacherRepository->getAllActive();
    }

    /**
     * Assign homeroom teacher to classroom
     *
     * @throws \InvalidArgumentException
     */
    public function assignHomeroomTeacher(int $classroomId, ?int $teacherId): Classroom
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if ($classroom === null) {
            throw new \InvalidArgumentException("Classroom with ID {$classroomId} not found.");
        }

        // Validate teacher exists if provided
        if ($teacherId !== null) {
            $teacher = $this->teacherRepository->findById($teacherId);
            if ($teacher === null) {
                throw new \InvalidArgumentException("Teacher with ID {$teacherId} not found.");
            }
        }

        return $this->classroomRepository->update($classroom, [
            'homeroom_teacher_id' => $teacherId,
        ]);
    }

    /**
     * Get classrooms by homeroom teacher
     *
     * @return Collection<int, Classroom>
     */
    public function getClassroomsByHomeroomTeacher(int $teacherId): Collection
    {
        return $this->classroomRepository->getByHomeroomTeacher($teacherId);
    }
}

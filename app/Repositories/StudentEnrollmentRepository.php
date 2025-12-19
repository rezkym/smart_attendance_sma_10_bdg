<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\EnrollmentStatus;
use App\Models\StudentEnrollment;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentEnrollmentRepository implements StudentEnrollmentRepositoryInterface
{
    public function __construct(
        protected StudentEnrollment $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with(['student.user', 'classroom', 'academicYear'])
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function findById(int $enrollmentId): ?StudentEnrollment
    {
        return $this->model->newQuery()
            ->with(['student.user', 'classroom', 'academicYear'])
            ->find($enrollmentId);
    }

    public function create(array $data): StudentEnrollment
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(StudentEnrollment $enrollment, array $data): StudentEnrollment
    {
        $enrollment->update($data);

        return $enrollment->fresh(['student.user', 'classroom', 'academicYear']);
    }

    public function delete(StudentEnrollment $enrollment): bool
    {
        return (bool) $enrollment->delete();
    }

    public function getByStudent(int $studentId): Collection
    {
        return $this->model->newQuery()
            ->byStudent($studentId)
            ->with(['classroom', 'academicYear'])
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function getByClassroom(int $classroomId): Collection
    {
        return $this->model->newQuery()
            ->byClassroom($classroomId)
            ->with(['student.user', 'academicYear'])
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->newQuery()
            ->byAcademicYear($academicYearId)
            ->with(['student.user', 'classroom'])
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function getCurrentEnrollment(int $studentId): ?StudentEnrollment
    {
        return $this->model->newQuery()
            ->byStudent($studentId)
            ->active()
            ->with(['classroom', 'academicYear'])
            ->latest('enrolled_at')
            ->first();
    }

    public function hasActiveEnrollmentInYear(int $studentId, int $academicYearId, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->active();

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getActiveByClassroom(int $classroomId): Collection
    {
        return $this->model->newQuery()
            ->byClassroom($classroomId)
            ->active()
            ->with(['student.user', 'academicYear'])
            ->orderBy('enrolled_at')
            ->get();
    }

    public function updateStatus(StudentEnrollment $enrollment, EnrollmentStatus $status, ?string $leftAt = null): StudentEnrollment
    {
        $data = ['status' => $status];

        if ($leftAt !== null) {
            $data['left_at'] = $leftAt;
        }

        $enrollment->update($data);

        return $enrollment->fresh(['student.user', 'classroom', 'academicYear']);
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with(['student.user', 'classroom', 'academicYear'])
            ->orderByDesc('enrolled_at');
    }
}

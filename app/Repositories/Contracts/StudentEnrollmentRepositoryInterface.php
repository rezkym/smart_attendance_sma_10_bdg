<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\EnrollmentStatus;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface StudentEnrollmentRepositoryInterface
{
    /**
     * Get all enrollments with relationships.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getAll(): Collection;

    /**
     * Find enrollment by ID with relationships.
     */
    public function findById(int $enrollmentId): ?StudentEnrollment;

    /**
     * Create a new enrollment.
     *
     * @param array{student_id: int, classroom_id: int, academic_year_id: int, enrolled_at: string, left_at?: string|null, status?: int} $data
     */
    public function create(array $data): StudentEnrollment;

    /**
     * Update enrollment.
     *
     * @param array{classroom_id?: int, enrolled_at?: string, left_at?: string|null, status?: int} $data
     */
    public function update(StudentEnrollment $enrollment, array $data): StudentEnrollment;

    /**
     * Delete enrollment.
     */
    public function delete(StudentEnrollment $enrollment): bool;

    /**
     * Get enrollments by student.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getByStudent(int $studentId): Collection;

    /**
     * Get enrollments by classroom.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getByClassroom(int $classroomId): Collection;

    /**
     * Get enrollments by academic year.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getByAcademicYear(int $academicYearId): Collection;

    /**
     * Get current active enrollment for a student.
     */
    public function getCurrentEnrollment(int $studentId): ?StudentEnrollment;

    /**
     * Check if student already has active enrollment in the same academic year.
     */
    public function hasActiveEnrollmentInYear(int $studentId, int $academicYearId, ?int $excludeId = null): bool;

    /**
     * Get active enrollments for classroom.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getActiveByClassroom(int $classroomId): Collection;

    /**
     * Update enrollment status.
     */
    public function updateStatus(StudentEnrollment $enrollment, EnrollmentStatus $status, ?string $leftAt = null): StudentEnrollment;

    /**
     * Get query builder for DataTables.
     */
    public function getDataTableQuery(): Builder;
}

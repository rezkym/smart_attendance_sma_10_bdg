<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\StudentEnrollment;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentService
{
    public function __construct(
        protected StudentEnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * Get all enrollments.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getAllEnrollments(): Collection
    {
        return $this->enrollmentRepository->getAll();
    }

    /**
     * Get enrollment by ID.
     */
    public function getEnrollmentById(int $enrollmentId): ?StudentEnrollment
    {
        return $this->enrollmentRepository->findById($enrollmentId);
    }

    /**
     * Get enrollment history for a student.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getEnrollmentHistory(int $studentId): Collection
    {
        return $this->enrollmentRepository->getByStudent($studentId);
    }

    /**
     * Get current active enrollment for a student.
     */
    public function getCurrentEnrollment(int $studentId): ?StudentEnrollment
    {
        return $this->enrollmentRepository->getCurrentEnrollment($studentId);
    }

    /**
     * Get enrollments by classroom.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getEnrollmentsByClassroom(int $classroomId): Collection
    {
        return $this->enrollmentRepository->getByClassroom($classroomId);
    }

    /**
     * Get active enrollments by classroom.
     *
     * @return Collection<int, StudentEnrollment>
     */
    public function getActiveEnrollmentsByClassroom(int $classroomId): Collection
    {
        return $this->enrollmentRepository->getActiveByClassroom($classroomId);
    }

    /**
     * Enroll a student to a classroom.
     *
     * @param array{student_id: int, classroom_id: int, academic_year_id: int, enrolled_at: string} $data
     *
     * @throws \InvalidArgumentException
     */
    public function enrollStudent(array $data): StudentEnrollment
    {
        // Check if student already has active enrollment in the same academic year
        if ($this->enrollmentRepository->hasActiveEnrollmentInYear($data['student_id'], $data['academic_year_id'])) {
            throw new \InvalidArgumentException(
                'Siswa sudah terdaftar aktif di tahun ajaran ini. Gunakan fitur pindah kelas jika ingin memindahkan siswa.'
            );
        }

        return DB::transaction(function () use ($data) {
            $enrollmentData = [
                'student_id' => $data['student_id'],
                'classroom_id' => $data['classroom_id'],
                'academic_year_id' => $data['academic_year_id'],
                'enrolled_at' => $data['enrolled_at'],
                'status' => EnrollmentStatus::ACTIVE->value,
            ];

            return $this->enrollmentRepository->create($enrollmentData);
        });
    }

    /**
     * Transfer student to a new classroom.
     *
     * @throws \InvalidArgumentException
     */
    public function transferStudent(int $studentId, int $newClassroomId, int $academicYearId, string $transferDate): StudentEnrollment
    {
        $currentEnrollment = $this->enrollmentRepository->getCurrentEnrollment($studentId);

        if ($currentEnrollment === null) {
            throw new \InvalidArgumentException('Siswa tidak memiliki enrollment aktif untuk dipindahkan.');
        }

        if ($currentEnrollment->classroom_id === $newClassroomId) {
            throw new \InvalidArgumentException('Siswa sudah terdaftar di kelas tersebut.');
        }

        return DB::transaction(function () use ($currentEnrollment, $newClassroomId, $academicYearId, $transferDate) {
            // Update old enrollment status to TRANSFERRED
            $this->enrollmentRepository->updateStatus(
                $currentEnrollment,
                EnrollmentStatus::TRANSFERRED,
                $transferDate
            );

            // Create new enrollment
            return $this->enrollmentRepository->create([
                'student_id' => $currentEnrollment->student_id,
                'classroom_id' => $newClassroomId,
                'academic_year_id' => $academicYearId,
                'enrolled_at' => $transferDate,
                'status' => EnrollmentStatus::ACTIVE->value,
            ]);
        });
    }

    /**
     * Graduate a student.
     *
     * @throws \InvalidArgumentException
     */
    public function graduateStudent(int $studentId, string $graduationDate): StudentEnrollment
    {
        $currentEnrollment = $this->enrollmentRepository->getCurrentEnrollment($studentId);

        if ($currentEnrollment === null) {
            throw new \InvalidArgumentException('Siswa tidak memiliki enrollment aktif untuk diluluskan.');
        }

        return $this->enrollmentRepository->updateStatus(
            $currentEnrollment,
            EnrollmentStatus::GRADUATED,
            $graduationDate
        );
    }

    /**
     * Drop a student (keluar/mengundurkan diri).
     *
     * @throws \InvalidArgumentException
     */
    public function dropStudent(int $studentId, string $dropDate): StudentEnrollment
    {
        $currentEnrollment = $this->enrollmentRepository->getCurrentEnrollment($studentId);

        if ($currentEnrollment === null) {
            throw new \InvalidArgumentException('Siswa tidak memiliki enrollment aktif.');
        }

        return $this->enrollmentRepository->updateStatus(
            $currentEnrollment,
            EnrollmentStatus::DROPPED,
            $dropDate
        );
    }

    /**
     * Update enrollment data.
     *
     * @param array{classroom_id?: int, enrolled_at?: string, left_at?: string|null, status?: int} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateEnrollment(int $enrollmentId, array $data): StudentEnrollment
    {
        $enrollment = $this->enrollmentRepository->findById($enrollmentId);

        if ($enrollment === null) {
            throw new \InvalidArgumentException("Enrollment dengan ID {$enrollmentId} tidak ditemukan.");
        }

        return $this->enrollmentRepository->update($enrollment, $data);
    }

    /**
     * Delete enrollment.
     *
     * @throws \InvalidArgumentException
     */
    public function deleteEnrollment(int $enrollmentId): bool
    {
        $enrollment = $this->enrollmentRepository->findById($enrollmentId);

        if ($enrollment === null) {
            throw new \InvalidArgumentException("Enrollment dengan ID {$enrollmentId} tidak ditemukan.");
        }

        return $this->enrollmentRepository->delete($enrollment);
    }

    /**
     * Get DataTables query builder.
     */
    public function getDataTableQuery(): Builder
    {
        return $this->enrollmentRepository->getDataTableQuery();
    }

    /**
     * Get statistics.
     *
     * @return array{total_active: int, total_graduated: int, total_transferred: int, total_dropped: int}
     */
    public function getStats(): array
    {
        $enrollments = $this->enrollmentRepository->getAll();

        return [
            'total_active' => $enrollments->where('status', EnrollmentStatus::ACTIVE)->count(),
            'total_graduated' => $enrollments->where('status', EnrollmentStatus::GRADUATED)->count(),
            'total_transferred' => $enrollments->where('status', EnrollmentStatus::TRANSFERRED)->count(),
            'total_dropped' => $enrollments->where('status', EnrollmentStatus::DROPPED)->count(),
        ];
    }
}

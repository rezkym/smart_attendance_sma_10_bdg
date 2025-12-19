<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentService
{
    public function __construct(
        protected StudentRepositoryInterface $studentRepository,
        protected ClassroomRepositoryInterface $classroomRepository,
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get all students.
     *
     * @return Collection<int, Student>
     */
    public function getAllStudents(): Collection
    {
        return $this->studentRepository->getAll();
    }

    /**
     * Get all active students.
     *
     * @return Collection<int, Student>
     */
    public function getAllActiveStudents(): Collection
    {
        return $this->studentRepository->getAllActive();
    }

    /**
     * Get student by ID.
     */
    public function getStudentById(int $studentId): ?Student
    {
        return $this->studentRepository->findById($studentId);
    }

    /**
     * Get student by NISN.
     */
    public function getStudentByNisn(string $nisn): ?Student
    {
        return $this->studentRepository->findByNisn($nisn);
    }

    /**
     * Get student by NIS.
     */
    public function getStudentByNis(string $nis): ?Student
    {
        return $this->studentRepository->findByNis($nis);
    }

    /**
     * Get student by RFID card number.
     */
    public function getStudentByRfid(string $rfidCardNumber): ?Student
    {
        return $this->studentRepository->findByRfid($rfidCardNumber);
    }

    /**
     * Get students by classroom ID (via enrollment).
     * Phase G: Uses enrollment instead of direct classroom_id.
     *
     * @return Collection<int, Student>
     */
    public function getStudentsByClassroom(int $classroomId): Collection
    {
        return $this->studentRepository->getByClassroomViaEnrollment($classroomId);
    }

    /**
     * Get users with student role who don't have a student profile yet.
     *
     * @return Collection<int, User>
     */
    public function getAvailableUsers(): Collection
    {
        return $this->userRepository->getUsersByRoleWithoutRelation('student', 'student');
    }

    /**
     * Create a new student.
     *
     * @param array{user_id: int, nisn: string, nis: string, classroom_id?: int|null, rfid_card_number?: string|null, enrollment_date?: string|null, is_active?: bool, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function createStudent(array $data): Student
    {
        $user = User::find($data['user_id']);

        if ($user === null) {
            throw new \InvalidArgumentException('Selected user not found.');
        }

        // Check if user already has a student profile
        if ($user->student !== null) {
            throw new \InvalidArgumentException('This user already has a student profile.');
        }

        // Check NISN uniqueness
        if ($this->studentRepository->findByNisn($data['nisn']) !== null) {
            throw new \InvalidArgumentException('NISN already registered.');
        }

        // Check NIS uniqueness
        if ($this->studentRepository->findByNis($data['nis']) !== null) {
            throw new \InvalidArgumentException('NIS already registered.');
        }

        // Check RFID uniqueness if provided
        if (isset($data['rfid_card_number']) && $data['rfid_card_number'] !== null) {
            if ($this->studentRepository->findByRfid($data['rfid_card_number']) !== null) {
                throw new \InvalidArgumentException('RFID card number already registered.');
            }
        }

        return $this->studentRepository->create($data);
    }

    /**
     * Update student data.
     *
     * @param array{nisn?: string, nis?: string, classroom_id?: int|null, rfid_card_number?: string|null, enrollment_date?: string|null, is_active?: bool, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateStudent(int $studentId, array $data): Student
    {
        $student = $this->studentRepository->findById($studentId);

        if ($student === null) {
            throw new \InvalidArgumentException("Student with ID {$studentId} not found.");
        }

        // Check NISN uniqueness (ignore current)
        if (isset($data['nisn']) && $data['nisn'] !== $student->nisn) {
            $existingStudent = $this->studentRepository->findByNisn($data['nisn']);
            if ($existingStudent !== null) {
                throw new \InvalidArgumentException('NISN already registered by another student.');
            }
        }

        // Check NIS uniqueness (ignore current)
        if (isset($data['nis']) && $data['nis'] !== $student->nis) {
            $existingStudent = $this->studentRepository->findByNis($data['nis']);
            if ($existingStudent !== null) {
                throw new \InvalidArgumentException('NIS already registered by another student.');
            }
        }

        // Check RFID uniqueness (ignore current)
        if (isset($data['rfid_card_number']) && $data['rfid_card_number'] !== null) {
            if ($data['rfid_card_number'] !== $student->rfid_card_number) {
                $existingStudent = $this->studentRepository->findByRfid($data['rfid_card_number']);
                if ($existingStudent !== null) {
                    throw new \InvalidArgumentException('RFID card number already registered by another student.');
                }
            }
        }

        return $this->studentRepository->update($student, $data);
    }

    /**
     * Delete a student.
     *
     * @throws \InvalidArgumentException
     */
    public function deleteStudent(int $studentId): bool
    {
        $student = $this->studentRepository->findById($studentId);

        if ($student === null) {
            throw new \InvalidArgumentException("Student with ID {$studentId} not found.");
        }

        return $this->studentRepository->delete($student);
    }

    /**
     * Get available classrooms for student assignment.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Classroom>
     */
    public function getAvailableClassrooms(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->classroomRepository->getAllActive();
    }

    /**
     * Get statistics for dashboard cards.
     *
     * @return array{total: int, active: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->studentRepository->getTotalCount(),
            'active' => $this->studentRepository->getActiveCount(),
        ];
    }

    /**
     * Get DataTables query builder.
     *
     * @return Builder<Student>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->studentRepository->getDataTableQuery();
    }
}


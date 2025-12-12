<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface
{
    /**
     * Get all students with relations.
     *
     * @return Collection<int, Student>
     */
    public function getAll(): Collection;

    /**
     * Get all active students.
     *
     * @return Collection<int, Student>
     */
    public function getAllActive(): Collection;

    /**
     * Find student by ID with relations.
     */
    public function findById(int $studentId): ?Student;

    /**
     * Find student by NISN.
     */
    public function findByNisn(string $nisn): ?Student;

    /**
     * Find student by NIS.
     */
    public function findByNis(string $nis): ?Student;

    /**
     * Find student by RFID card number.
     */
    public function findByRfid(string $rfidCardNumber): ?Student;

    /**
     * Create a new student.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Student;

    /**
     * Update student.
     *
     * @param array<string, mixed> $data
     */
    public function update(Student $student, array $data): Student;

    /**
     * Delete student.
     */
    public function delete(Student $student): bool;

    /**
     * Get total count of students.
     */
    public function getTotalCount(): int;

    /**
     * Get count of active students.
     */
    public function getActiveCount(): int;

    /**
     * Get query builder for DataTables with eager loading.
     *
     * @return Builder<Student>
     */
    public function getDataTableQuery(): Builder;

    /**
     * Get students by classroom ID.
     *
     * @return Collection<int, Student>
     */
    public function getByClassroom(int $classroomId): Collection;
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface AttendanceRepositoryInterface
{
    /**
     * Get all attendances with relations.
     *
     * @return Collection<int, Attendance>
     */
    public function getAll(): Collection;

    /**
     * Find attendance by ID with relations.
     */
    public function findById(int $attendanceId): ?Attendance;

    /**
     * Get attendances by student and date.
     *
     * @return Collection<int, Attendance>
     */
    public function getByStudentAndDate(int $studentId, Carbon $date): Collection;

    /**
     * Get attendances by schedule and date.
     *
     * @return Collection<int, Attendance>
     */
    public function getByScheduleAndDate(int $scheduleId, Carbon $date): Collection;

    /**
     * Get attendances by classroom and date.
     *
     * @return Collection<int, Attendance>
     */
    public function getByClassroomAndDate(int $classroomId, Carbon $date): Collection;

    /**
     * Find existing attendance record for unique constraint check.
     */
    public function findExisting(int $studentId, int $scheduleId, Carbon $date): ?Attendance;

    /**
     * Get attendance statistics by classroom for a date range.
     *
     * @return array<string, mixed>
     */
    public function getStatsByClassroom(int $classroomId, Carbon $startDate, Carbon $endDate): array;

    /**
     * Get today's attendance statistics.
     *
     * @return array<string, int>
     */
    public function getTodayStats(): array;

    /**
     * Create a new attendance.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Attendance;

    /**
     * Create multiple attendance records.
     *
     * @param array<int, array<string, mixed>> $records
     * @return Collection<int, Attendance>
     */
    public function createMany(array $records): Collection;

    /**
     * Update attendance.
     *
     * @param array<string, mixed> $data
     */
    public function update(Attendance $attendance, array $data): Attendance;

    /**
     * Delete attendance.
     */
    public function delete(Attendance $attendance): bool;

    /**
     * Get query builder for DataTables with eager loading.
     *
     * @return Builder<Attendance>
     */
    public function getDataTableQuery(): Builder;

    /**
     * Get attendances by classroom and date range.
     *
     * @return Collection<int, Attendance>
     */
    public function getByClassroomAndDateRange(int $classroomId, Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get attendances by student and date range.
     *
     * @return Collection<int, Attendance>
     */
    public function getByStudentAndDateRange(int $studentId, Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get attendance count by status for a classroom and date range.
     *
     * @return array<string, int>
     */
    public function getStatusCountByClassroomAndDateRange(int $classroomId, Carbon $startDate, Carbon $endDate): array;

    /**
     * Get daily attendance summary for a classroom.
     *
     * @return array<string, array<string, int>>
     */
    public function getDailyAttendanceSummary(int $classroomId, Carbon $startDate, Carbon $endDate): array;

    /**
     * Get attendance stats for a specific date.
     *
     * @return array{present: int, late: int, excused: int, sick: int, absent: int}
     */
    public function getStatsByDate(Carbon $date): array;
}

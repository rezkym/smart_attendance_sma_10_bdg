<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface ScheduleRepositoryInterface
{
    /**
     * Get all schedules with relations.
     *
     * @return Collection<int, Schedule>
     */
    public function getAll(): Collection;

    /**
     * Get all active schedules.
     *
     * @return Collection<int, Schedule>
     */
    public function getAllActive(): Collection;

    /**
     * Find schedule by ID with relations.
     */
    public function findById(int $scheduleId): ?Schedule;

    /**
     * Get schedules by classroom.
     *
     * @return Collection<int, Schedule>
     */
    public function getByClassroom(int $classroomId): Collection;

    /**
     * Get schedules by academic year.
     *
     * @return Collection<int, Schedule>
     */
    public function getByAcademicYear(int $academicYearId): Collection;

    /**
     * Get schedules by classroom and academic year.
     *
     * @return Collection<int, Schedule>
     */
    public function getByClassroomAndAcademicYear(int $classroomId, int $academicYearId): Collection;

    /**
     * Get schedules by teacher.
     *
     * @return Collection<int, Schedule>
     */
    public function getByTeacher(int $teacherId): Collection;

    /**
     * Get schedules by day of week.
     *
     * @return Collection<int, Schedule>
     */
    public function getByDay(int $dayOfWeek): Collection;

    /**
     * Find active schedule by classroom, day, and time.
     */
    public function findActiveByClassroomDayAndTime(
        int $classroomId,
        int $dayOfWeek,
        string $time
    ): ?Schedule;

    /**
     * Find most recent past schedule (ended within last N hours).
     */
    public function findRecentPastSchedule(
        int $classroomId,
        int $dayOfWeek,
        string $currentTime,
        int $lookbackHours = 3
    ): ?Schedule;

    /**
     * Check if schedule conflicts exist.
     */
    public function hasConflict(
        int $classroomId,
        int $academicYearId,
        int $dayOfWeek,
        string $startTime,
        ?int $excludeScheduleId = null
    ): bool;

    /**
     * Create a new schedule.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Schedule;

    /**
     * Update schedule.
     *
     * @param array<string, mixed> $data
     */
    public function update(Schedule $schedule, array $data): Schedule;

    /**
     * Delete schedule.
     */
    public function delete(Schedule $schedule): bool;

    /**
     * Get total count.
     */
    public function getTotalCount(): int;

    /**
     * Get active count.
     */
    public function getActiveCount(): int;

    /**
     * Get query builder for DataTables with eager loading.
     *
     * @return Builder<Schedule>
     */
    public function getDataTableQuery(): Builder;
}

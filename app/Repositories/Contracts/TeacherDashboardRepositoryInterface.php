<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface TeacherDashboardRepositoryInterface
{
    /**
     * Get teacher by user ID.
     */
    public function getTeacherByUserId(int $userId): ?Teacher;

    /**
     * Get schedules by teacher with optional filters.
     *
     * @param array<string, mixed> $filters
     * @return Collection<int, Schedule>
     */
    public function getSchedulesByTeacher(int $teacherId, array $filters = []): Collection;

    /**
     * Get today's schedules for a teacher.
     *
     * @return Collection<int, Schedule>
     */
    public function getTodaySchedules(int $teacherId): Collection;

    /**
     * Get upcoming schedules for the next N days.
     *
     * @return Collection<int, Schedule>
     */
    public function getUpcomingSchedules(int $teacherId, int $days = 7): Collection;

    /**
     * Get schedule by ID for a specific teacher.
     */
    public function findScheduleByIdForTeacher(int $scheduleId, int $teacherId): ?Schedule;

    /**
     * Get teacher's total schedule count.
     */
    public function getScheduleCount(int $teacherId): int;

    /**
     * Get teacher's classrooms count (distinct).
     */
    public function getClassroomCount(int $teacherId): int;

    /**
     * Get query builder for DataTables.
     *
     * @param array<string, mixed> $filters
     * @return Builder<Schedule>
     */
    public function getDataTableQuery(int $teacherId, array $filters = []): Builder;
}

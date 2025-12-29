<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Schedule;
use App\Models\Teacher;
use App\Repositories\Contracts\TeacherDashboardRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class TeacherDashboardRepository implements TeacherDashboardRepositoryInterface
{
    public function __construct(
        protected Teacher $teacherModel,
        protected Schedule $scheduleModel
    ) {}

    /**
     * Get teacher by user ID.
     */
    public function getTeacherByUserId(int $userId): ?Teacher
    {
        return $this->teacherModel->newQuery()
            ->with(['user', 'homerooms'])
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get schedules by teacher with optional filters.
     *
     * @param array<string, mixed> $filters
     * @return Collection<int, Schedule>
     */
    public function getSchedulesByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = $this->scheduleModel->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->where('teacher_id', $teacherId)
            ->active();

        // Apply day of week filter
        if (isset($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        // Apply semester filter
        if (isset($filters['semester_id'])) {
            $query->where('semester_id', $filters['semester_id']);
        }

        // Apply academic year filter
        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        return $query
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get today's schedules for a teacher.
     *
     * @return Collection<int, Schedule>
     */
    public function getTodaySchedules(int $teacherId): Collection
    {
        $todayDayOfWeek = Carbon::today()->dayOfWeekIso; // 1 = Monday, 7 = Sunday

        return $this->scheduleModel->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $todayDayOfWeek)
            ->active()
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get upcoming schedules for the next N days.
     *
     * @return Collection<int, Schedule>
     */
    public function getUpcomingSchedules(int $teacherId, int $days = 7): Collection
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addDays($days);

        // Get days of week for the date range
        $daysOfWeek = [];
        for ($date = $today->copy(); $date->lte($endDate); $date->addDay()) {
            $daysOfWeek[] = $date->dayOfWeekIso;
        }
        $daysOfWeek = array_unique($daysOfWeek);

        return $this->scheduleModel->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->where('teacher_id', $teacherId)
            ->whereIn('day_of_week', $daysOfWeek)
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get schedule by ID for a specific teacher.
     */
    public function findScheduleByIdForTeacher(int $scheduleId, int $teacherId): ?Schedule
    {
        return $this->scheduleModel->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->where('id', $scheduleId)
            ->where('teacher_id', $teacherId)
            ->first();
    }

    /**
     * Get teacher's total schedule count.
     */
    public function getScheduleCount(int $teacherId): int
    {
        return $this->scheduleModel->newQuery()
            ->where('teacher_id', $teacherId)
            ->active()
            ->count();
    }

    /**
     * Get teacher's classrooms count (distinct).
     */
    public function getClassroomCount(int $teacherId): int
    {
        return $this->scheduleModel->newQuery()
            ->where('teacher_id', $teacherId)
            ->active()
            ->distinct('classroom_id')
            ->count('classroom_id');
    }

    /**
     * Get query builder for DataTables.
     *
     * @param array<string, mixed> $filters
     * @return Builder<Schedule>
     */
    public function getDataTableQuery(int $teacherId, array $filters = []): Builder
    {
        $query = $this->scheduleModel->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->where('teacher_id', $teacherId)
            ->active();

        if (isset($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (isset($filters['semester_id'])) {
            $query->where('semester_id', $filters['semester_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        return $query
            ->orderBy('day_of_week')
            ->orderBy('start_time');
    }
}

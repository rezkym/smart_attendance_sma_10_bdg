<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Schedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    public function __construct(
        protected Schedule $model
    ) {}

    /**
     * @return Collection<int, Schedule>
     */
    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function findById(int $scheduleId): ?Schedule
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->find($scheduleId);
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getByClassroom(int $classroomId): Collection
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear'])
            ->byClassroom($classroomId)
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear'])
            ->byAcademicYear($academicYearId)
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getByClassroomAndAcademicYear(int $classroomId, int $academicYearId): Collection
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear'])
            ->byClassroom($classroomId)
            ->byAcademicYear($academicYearId)
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getByTeacher(int $teacherId): Collection
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear'])
            ->byTeacher($teacherId)
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getByDay(int $dayOfWeek): Collection
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear'])
            ->byDay($dayOfWeek)
            ->active()
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Find active schedule by classroom, day, and time.
     */
    public function findActiveByClassroomDayAndTime(
        int $classroomId,
        int $dayOfWeek,
        string $time
    ): ?Schedule {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear'])
            ->byClassroom($classroomId)
            ->byDay($dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->active()
            ->first();
    }

    /**
     * Find most recent past schedule (ended within last N hours).
     */
    public function findRecentPastSchedule(
        int $classroomId,
        int $dayOfWeek,
        string $currentTime,
        int $lookbackHours = 3
    ): ?Schedule {
        $lookbackTime = Carbon::parse($currentTime)->subHours($lookbackHours)->format('H:i:s');

        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear'])
            ->byClassroom($classroomId)
            ->byDay($dayOfWeek)
            ->where('end_time', '<', $currentTime)       // Already ended
            ->where('end_time', '>=', $lookbackTime)     // Within lookback window
            ->active()
            ->orderBy('end_time', 'desc')                // Most recent first
            ->first();
    }

    public function hasConflict(
        int $classroomId,
        int $semesterId,
        int $dayOfWeek,
        string $startTime,
        ?int $excludeScheduleId = null
    ): bool {
        $query = $this->model->newQuery()
            ->where('classroom_id', $classroomId)
            ->where('semester_id', $semesterId)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', $startTime);

        if ($excludeScheduleId !== null) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return $query->exists();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Schedule
    {
        $schedule = $this->model->newQuery()->create($data);

        return $schedule->load(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->update($data);

        return $schedule->fresh()->load(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester']);
    }

    public function delete(Schedule $schedule): bool
    {
        return (bool) $schedule->delete();
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getActiveCount(): int
    {
        return $this->model->newQuery()->active()->count();
    }

    /**
     * @return Builder<Schedule>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with(['classroom', 'subject', 'teacher.user', 'academicYear', 'semester'])
            ->orderBy('day_of_week')
            ->orderBy('start_time');
    }
}

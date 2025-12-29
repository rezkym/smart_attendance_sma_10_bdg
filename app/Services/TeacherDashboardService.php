<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Schedule;
use App\Models\Teacher;
use App\Repositories\Contracts\TeacherDashboardRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class TeacherDashboardService
{
    public function __construct(
        protected TeacherDashboardRepositoryInterface $repository
    ) {}

    /**
     * Get teacher by user ID.
     */
    public function getTeacherByUserId(int $userId): ?Teacher
    {
        return $this->repository->getTeacherByUserId($userId);
    }

    /**
     * Get dashboard statistics for a teacher user.
     *
     * @return array{
     *     total_schedules: int,
     *     total_classrooms: int,
     *     today_schedules: int,
     *     is_homeroom: bool,
     *     homeroom_classrooms: array<int, array{id: int, name: string}>
     * }
     */
    public function getDashboardStats(int $userId): array
    {
        $teacher = $this->repository->getTeacherByUserId($userId);

        if ($teacher === null) {
            return [
                'total_schedules' => 0,
                'total_classrooms' => 0,
                'today_schedules' => 0,
                'is_homeroom' => false,
                'homeroom_classrooms' => [],
            ];
        }

        $todaySchedules = $this->repository->getTodaySchedules($teacher->id);

        $homeroomClassrooms = [];
        $isHomeroom = false;

        if ($teacher !== null && $teacher->homerooms->isNotEmpty()) {
            $isHomeroom = true;
            $homeroomClassrooms = $teacher->homerooms->map(function ($classroom) {
                return [
                    'id' => $classroom->id,
                    'name' => $classroom->name,
                ];
            })->toArray();
        }

        return [
            'total_schedules' => $this->repository->getScheduleCount($teacher->id),
            'total_classrooms' => $this->repository->getClassroomCount($teacher->id),
            'today_schedules' => $todaySchedules->count(),
            'is_homeroom' => $isHomeroom,
            'homeroom_classrooms' => $homeroomClassrooms,
        ];
    }

    /**
     * Get today's schedules for a teacher.
     *
     * @return Collection<int, Schedule>
     */
    public function getTodaySchedules(int $teacherId): Collection
    {
        return $this->repository->getTodaySchedules($teacherId);
    }

    /**
     * Get my schedules with optional filters.
     *
     * @param array<string, mixed> $filters
     * @return Collection<int, Schedule>
     */
    public function getMySchedules(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->getSchedulesByTeacher($teacherId, $filters);
    }

    /**
     * Get upcoming schedules for the next N days.
     *
     * @return Collection<int, Schedule>
     */
    public function getUpcomingSchedules(int $teacherId, int $days = 7): Collection
    {
        return $this->repository->getUpcomingSchedules($teacherId, $days);
    }

    /**
     * Get schedule by ID for a specific teacher.
     */
    public function getScheduleForTeacher(int $scheduleId, int $teacherId): ?Schedule
    {
        return $this->repository->findScheduleByIdForTeacher($scheduleId, $teacherId);
    }

    /**
     * Get formatted today schedules for display.
     *
     * @return array<int, array{
     *     id: int,
     *     subject: string,
     *     classroom: string,
     *     start_time: string,
     *     end_time: string,
     *     status: string
     * }>
     */
    public function getFormattedTodaySchedules(int $teacherId): array
    {
        $schedules = $this->getTodaySchedules($teacherId);
        $currentTime = Carbon::now()->format('H:i:s');

        return $schedules->map(function (Schedule $schedule) use ($currentTime) {
            $status = 'upcoming';

            if ($currentTime >= $schedule->start_time && $currentTime <= $schedule->end_time) {
                $status = 'ongoing';
            } elseif ($currentTime > $schedule->end_time) {
                $status = 'completed';
            }

            return [
                'id' => $schedule->id,
                'subject' => $schedule->subject?->name ?? '-',
                'classroom' => $schedule->classroom?->name ?? '-',
                'start_time' => Carbon::parse($schedule->start_time)->format('H:i'),
                'end_time' => Carbon::parse($schedule->end_time)->format('H:i'),
                'status' => $status,
            ];
        })->toArray();
    }

    /**
     * Get DataTable query for schedules.
     *
     * @param array<string, mixed> $filters
     * @return \Illuminate\Database\Eloquent\Builder<Schedule>
     */
    public function getDataTableQuery(int $teacherId, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        return $this->repository->getDataTableQuery($teacherId, $filters);
    }
}

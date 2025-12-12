<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function __construct(
        protected Attendance $model
    ) {}

    /**
     * @return Collection<int, Attendance>
     */
    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $attendanceId): ?Attendance
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->find($attendanceId);
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getByStudentAndDate(int $studentId, Carbon $date): Collection
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->byStudent($studentId)
            ->byDate($date->toDateString())
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getByScheduleAndDate(int $scheduleId, Carbon $date): Collection
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->bySchedule($scheduleId)
            ->byDate($date->toDateString())
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getByClassroomAndDate(int $classroomId, Carbon $date): Collection
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->byClassroom($classroomId)
            ->byDate($date->toDateString())
            ->orderBy('created_at')
            ->get();
    }

    public function findExisting(int $studentId, int $scheduleId, Carbon $date): ?Attendance
    {
        return $this->model->newQuery()
            ->where('student_id', $studentId)
            ->where('schedule_id', $scheduleId)
            ->whereDate('attendance_date', $date)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatsByClassroom(int $classroomId, Carbon $startDate, Carbon $endDate): array
    {
        $query = $this->model->newQuery()
            ->byClassroom($classroomId)
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        $total = $query->count();
        $present = (clone $query)->where('status', AttendanceStatus::PRESENT->value)->count();
        $late = (clone $query)->where('status', AttendanceStatus::LATE->value)->count();
        $excused = (clone $query)->where('status', AttendanceStatus::EXCUSED->value)->count();
        $sick = (clone $query)->where('status', AttendanceStatus::SICK->value)->count();
        $absent = (clone $query)->where('status', AttendanceStatus::ABSENT->value)->count();

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'excused' => $excused,
            'sick' => $sick,
            'absent' => $absent,
            'attendance_rate' => $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getTodayStats(): array
    {
        $today = Carbon::today();

        $query = $this->model->newQuery()->byDate($today->toDateString());

        return [
            'total' => $query->count(),
            'present' => (clone $query)->where('status', AttendanceStatus::PRESENT->value)->count(),
            'late' => (clone $query)->where('status', AttendanceStatus::LATE->value)->count(),
            'excused' => (clone $query)->where('status', AttendanceStatus::EXCUSED->value)->count(),
            'sick' => (clone $query)->where('status', AttendanceStatus::SICK->value)->count(),
            'absent' => (clone $query)->where('status', AttendanceStatus::ABSENT->value)->count(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Attendance
    {
        $attendance = $this->model->newQuery()->create($data);

        return $attendance->load(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder']);
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return Collection<int, Attendance>
     */
    public function createMany(array $records): Collection
    {
        $createdIds = [];

        foreach ($records as $record) {
            $attendance = $this->model->newQuery()->create($record);
            $createdIds[] = $attendance->id;
        }

        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->whereIn('id', $createdIds)
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->update($data);

        return $attendance->fresh()->load(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder']);
    }

    public function delete(Attendance $attendance): bool
    {
        return (bool) $attendance->delete();
    }

    /**
     * @return Builder<Attendance>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getByClassroomAndDateRange(int $classroomId, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->byClassroom($classroomId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getByStudentAndDateRange(int $studentId, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->newQuery()
            ->with(['student.user', 'schedule.subject', 'schedule.teacher.user', 'schedule.classroom', 'recorder'])
            ->byStudent($studentId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function getStatusCountByClassroomAndDateRange(int $classroomId, Carbon $startDate, Carbon $endDate): array
    {
        $query = $this->model->newQuery()
            ->byClassroom($classroomId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $total = $query->count();

        return [
            'total' => $total,
            'present' => (clone $query)->where('status', AttendanceStatus::PRESENT->value)->count(),
            'late' => (clone $query)->where('status', AttendanceStatus::LATE->value)->count(),
            'excused' => (clone $query)->where('status', AttendanceStatus::EXCUSED->value)->count(),
            'sick' => (clone $query)->where('status', AttendanceStatus::SICK->value)->count(),
            'absent' => (clone $query)->where('status', AttendanceStatus::ABSENT->value)->count(),
        ];
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function getDailyAttendanceSummary(int $classroomId, Carbon $startDate, Carbon $endDate): array
    {
        $attendances = $this->model->newQuery()
            ->byClassroom($classroomId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('DATE(attendance_date) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->get();

        $summary = [];
        foreach ($attendances as $attendance) {
            $date = $attendance->date;
            if (!isset($summary[$date])) {
                $summary[$date] = [
                    'present' => 0,
                    'late' => 0,
                    'excused' => 0,
                    'sick' => 0,
                    'absent' => 0,
                    'total' => 0,
                ];
            }
            $summary[$date][$attendance->status] = (int) $attendance->count;
            $summary[$date]['total'] += (int) $attendance->count;
        }

        // Sort by date
        ksort($summary);

        return $summary;
    }

    /**
     * @return array{present: int, late: int, excused: int, sick: int, absent: int}
     */
    public function getStatsByDate(Carbon $date): array
    {
        $baseQuery = $this->model->newQuery()
            ->whereDate('attendance_date', $date->toDateString());

        return [
            'present' => (clone $baseQuery)->where('status', AttendanceStatus::PRESENT->value)->count(),
            'late' => (clone $baseQuery)->where('status', AttendanceStatus::LATE->value)->count(),
            'excused' => (clone $baseQuery)->where('status', AttendanceStatus::EXCUSED->value)->count(),
            'sick' => (clone $baseQuery)->where('status', AttendanceStatus::SICK->value)->count(),
            'absent' => (clone $baseQuery)->where('status', AttendanceStatus::ABSENT->value)->count(),
        ];
    }
}


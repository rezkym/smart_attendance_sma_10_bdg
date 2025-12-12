<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Schedule;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function __construct(
        protected ScheduleRepositoryInterface $scheduleRepository,
        protected ClassroomRepositoryInterface $classroomRepository,
        protected SubjectRepositoryInterface $subjectRepository,
        protected TeacherRepositoryInterface $teacherRepository,
        protected AcademicYearRepositoryInterface $academicYearRepository
    ) {}

    /**
     * Get all schedules.
     *
     * @return Collection<int, Schedule>
     */
    public function getAllSchedules(): Collection
    {
        return $this->scheduleRepository->getAll();
    }

    /**
     * Get all active schedules.
     *
     * @return Collection<int, Schedule>
     */
    public function getAllActiveSchedules(): Collection
    {
        return $this->scheduleRepository->getAllActive();
    }

    /**
     * Get schedule by ID.
     */
    public function getScheduleById(int $scheduleId): ?Schedule
    {
        return $this->scheduleRepository->findById($scheduleId);
    }

    /**
     * Get schedules by classroom.
     *
     * @return Collection<int, Schedule>
     */
    public function getSchedulesByClassroom(int $classroomId): Collection
    {
        return $this->scheduleRepository->getByClassroom($classroomId);
    }

    /**
     * Get schedules by academic year.
     *
     * @return Collection<int, Schedule>
     */
    public function getSchedulesByAcademicYear(int $academicYearId): Collection
    {
        return $this->scheduleRepository->getByAcademicYear($academicYearId);
    }

    /**
     * Get schedules by classroom and academic year.
     *
     * @return Collection<int, Schedule>
     */
    public function getSchedulesByClassroomAndAcademicYear(int $classroomId, int $academicYearId): Collection
    {
        return $this->scheduleRepository->getByClassroomAndAcademicYear($classroomId, $academicYearId);
    }

    /**
     * Get schedules by teacher.
     *
     * @return Collection<int, Schedule>
     */
    public function getSchedulesByTeacher(int $teacherId): Collection
    {
        return $this->scheduleRepository->getByTeacher($teacherId);
    }

    /**
     * Create a new schedule.
     *
     * @param array{classroom_id: int, subject_id: int, teacher_id: int, academic_year_id: int, day_of_week: int, start_time: string, end_time: string, is_active?: bool, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function createSchedule(array $data): Schedule
    {
        // Validate time order
        if ($data['start_time'] >= $data['end_time']) {
            throw new \InvalidArgumentException('End time must be after start time.');
        }

        // Check for schedule conflicts
        if ($this->scheduleRepository->hasConflict(
            $data['classroom_id'],
            $data['academic_year_id'],
            $data['day_of_week'],
            $data['start_time']
        )) {
            throw new \InvalidArgumentException('A schedule already exists for this classroom at the specified time.');
        }

        return DB::transaction(function () use ($data) {
            return $this->scheduleRepository->create([
                'classroom_id' => $data['classroom_id'],
                'subject_id' => $data['subject_id'],
                'teacher_id' => $data['teacher_id'],
                'academic_year_id' => $data['academic_year_id'],
                'day_of_week' => $data['day_of_week'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Update schedule.
     *
     * @param array{classroom_id?: int, subject_id?: int, teacher_id?: int, academic_year_id?: int, day_of_week?: int, start_time?: string, end_time?: string, is_active?: bool, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateSchedule(int $scheduleId, array $data): Schedule
    {
        $schedule = $this->scheduleRepository->findById($scheduleId);

        if ($schedule === null) {
            throw new \InvalidArgumentException("Schedule with ID {$scheduleId} not found.");
        }

        // Determine effective values for validation
        $classroomId = $data['classroom_id'] ?? $schedule->classroom_id;
        $academicYearId = $data['academic_year_id'] ?? $schedule->academic_year_id;
        $dayOfWeek = $data['day_of_week'] ?? $schedule->day_of_week->value;
        $startTime = $data['start_time'] ?? $schedule->start_time;
        $endTime = $data['end_time'] ?? $schedule->end_time;

        // Validate time order
        if ($startTime >= $endTime) {
            throw new \InvalidArgumentException('End time must be after start time.');
        }

        // Check for schedule conflicts (exclude current schedule)
        if ($this->scheduleRepository->hasConflict(
            $classroomId,
            $academicYearId,
            is_int($dayOfWeek) ? $dayOfWeek : $dayOfWeek,
            $startTime,
            $scheduleId
        )) {
            throw new \InvalidArgumentException('A schedule already exists for this classroom at the specified time.');
        }

        return DB::transaction(function () use ($schedule, $data) {
            $updateData = [];

            if (isset($data['classroom_id'])) {
                $updateData['classroom_id'] = $data['classroom_id'];
            }
            if (isset($data['subject_id'])) {
                $updateData['subject_id'] = $data['subject_id'];
            }
            if (isset($data['teacher_id'])) {
                $updateData['teacher_id'] = $data['teacher_id'];
            }
            if (isset($data['academic_year_id'])) {
                $updateData['academic_year_id'] = $data['academic_year_id'];
            }
            if (isset($data['day_of_week'])) {
                $updateData['day_of_week'] = $data['day_of_week'];
            }
            if (isset($data['start_time'])) {
                $updateData['start_time'] = $data['start_time'];
            }
            if (isset($data['end_time'])) {
                $updateData['end_time'] = $data['end_time'];
            }
            if (isset($data['is_active'])) {
                $updateData['is_active'] = $data['is_active'];
            }
            if (array_key_exists('notes', $data)) {
                $updateData['notes'] = $data['notes'];
            }

            if (count($updateData) > 0) {
                return $this->scheduleRepository->update($schedule, $updateData);
            }

            return $schedule->fresh()->load(['classroom', 'subject', 'teacher.user', 'academicYear']);
        });
    }

    /**
     * Delete schedule.
     *
     * @throws \InvalidArgumentException
     */
    public function deleteSchedule(int $scheduleId): bool
    {
        $schedule = $this->scheduleRepository->findById($scheduleId);

        if ($schedule === null) {
            throw new \InvalidArgumentException("Schedule with ID {$scheduleId} not found.");
        }

        return $this->scheduleRepository->delete($schedule);
    }

    /**
     * Get statistics for dashboard cards.
     *
     * @return array{total: int, active: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->scheduleRepository->getTotalCount(),
            'active' => $this->scheduleRepository->getActiveCount(),
        ];
    }

    /**
     * Get DataTables query builder.
     *
     * @return Builder<Schedule>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->scheduleRepository->getDataTableQuery();
    }

    /**
     * Get available classrooms for dropdown.
     *
     * @return Collection<int, \App\Models\Classroom>
     */
    public function getAvailableClassrooms(): Collection
    {
        return $this->classroomRepository->getAllActive();
    }

    /**
     * Get available subjects for dropdown.
     *
     * @return Collection<int, \App\Models\Subject>
     */
    public function getAvailableSubjects(): Collection
    {
        return $this->subjectRepository->getAllActive();
    }

    /**
     * Get available teachers for dropdown.
     *
     * @return Collection<int, \App\Models\Teacher>
     */
    public function getAvailableTeachers(): Collection
    {
        return $this->teacherRepository->getAllActive();
    }

    /**
     * Get available academic years for dropdown.
     *
     * @return Collection<int, \App\Models\AcademicYear>
     */
    public function getAvailableAcademicYears(): Collection
    {
        return $this->academicYearRepository->getAll();
    }

    /**
     * Get active academic year.
     */
    public function getActiveAcademicYear(): ?\App\Models\AcademicYear
    {
        return $this->academicYearRepository->getActive();
    }
}

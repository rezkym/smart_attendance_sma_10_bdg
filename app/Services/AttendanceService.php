<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\DayOfWeek;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected StudentRepositoryInterface $studentRepository,
        protected ScheduleRepositoryInterface $scheduleRepository,
        protected ClassroomRepositoryInterface $classroomRepository,
        protected StudentEnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * Get all attendances.
     *
     * @return Collection<int, Attendance>
     */
    public function getAllAttendances(): Collection
    {
        return $this->attendanceRepository->getAll();
    }

    /**
     * Get attendance by ID.
     */
    public function getAttendanceById(int $attendanceId): ?Attendance
    {
        return $this->attendanceRepository->findById($attendanceId);
    }

    /**
     * Record attendance by RFID card scan.
     *
     * @throws \InvalidArgumentException
     */
    public function recordAttendanceByRfid(string $rfidCardNumber, int $scheduleId): Attendance
    {
        // Find student by RFID
        $student = $this->studentRepository->findByRfid($rfidCardNumber);
        if ($student === null) {
            throw new \InvalidArgumentException('RFID card not registered.');
        }

        if (!$student->is_active) {
            throw new \InvalidArgumentException('Student is not active.');
        }

        // Find schedule
        $schedule = $this->scheduleRepository->findById($scheduleId);
        if ($schedule === null) {
            throw new \InvalidArgumentException('Schedule not found.');
        }

        $now = Carbon::now();
        $today = Carbon::today();

        // Check if already recorded for today
        $existing = $this->attendanceRepository->findExisting($student->id, $scheduleId, $today);
        if ($existing !== null) {
            // Update check-out time if already checked in
            if ($existing->check_in_time !== null && $existing->check_out_time === null) {
                return $this->attendanceRepository->update($existing, [
                    'check_out_time' => $now->format('H:i:s'),
                ]);
            }
            throw new \InvalidArgumentException('Attendance already recorded for this schedule today.');
        }

        // Determine status based on time
        $status = $this->determineStatus($schedule, $now);

        return DB::transaction(function () use ($student, $scheduleId, $today, $now, $status) {
            return $this->attendanceRepository->create([
                'student_id' => $student->id,
                'schedule_id' => $scheduleId,
                'attendance_date' => $today,
                'check_in_time' => $now->format('H:i:s'),
                'status' => $status->value,
                'rfid_scan_time' => $now,
            ]);
        });
    }

    /**
     * Record attendance by RFID card scan (IoT).
     *
     * Scenarios:
     * 1. DURING schedule time → Record attendance (PRESENT/LATE)
     * 2. BEFORE any schedule → Reject "Tidak ada jadwal saat ini"
     * 3. AFTER schedule ended → Check past schedule:
     *    - If past schedule exists & no attendance → Mark ALPHA
     *    - If past schedule exists & already attended → Return "Sudah absen"
     *    - If no past schedule → Reject "Tidak ada mata pelajaran"
     *
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException
     */
    public function recordIoTAttendance(string $rfidCardNumber): array
    {
        // Find student by RFID
        $student = $this->studentRepository->findByRfid($rfidCardNumber);
        if ($student === null) {
            throw new \InvalidArgumentException('Kartu RFID tidak terdaftar.');
        }

        if (!$student->is_active) {
            throw new \InvalidArgumentException('Siswa tidak aktif.');
        }

        $now = Carbon::now();
        $today = Carbon::today();
        $dayOfWeek = $now->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
        $timeString = $now->format('H:i:s');

        $userName = $student->user?->name ?? 'Unknown';
        $studentId = $student->nis ?? $student->nisn ?? '';

        // Get classroom ID via enrollment (with fallback to legacy classroom_id)
        $classroomId = $this->getStudentClassroomId($student);

        // ===== SCENARIO 1: Check for ACTIVE schedule =====
        $activeSchedule = $this->scheduleRepository->findActiveByClassroomDayAndTime(
            $classroomId,
            $dayOfWeek,
            $timeString
        );

        if ($activeSchedule !== null) {
            // Check if already recorded for today
            $existing = $this->attendanceRepository->findExisting($student->id, $activeSchedule->id, $today);
            if ($existing !== null) {
                return [
                    'status' => 'info',
                    'message' => 'Anda sudah absen di ' . ($activeSchedule->subject->name ?? '-'),
                    'card_uid' => $rfidCardNumber,
                    'timestamp' => $now->toIso8601String(),
                    'user' => ['name' => $userName, 'employee_id' => $studentId],
                    'subject' => $activeSchedule->subject->name ?? '-',
                ];
            }

            // Determine status based on time (PRESENT or LATE)
            $status = $this->determineStatus($activeSchedule, $now);

            DB::transaction(function () use ($student, $activeSchedule, $today, $now, $status) {
                $this->attendanceRepository->create([
                    'student_id' => $student->id,
                    'schedule_id' => $activeSchedule->id,
                    'attendance_date' => $today,
                    'check_in_time' => $now->format('H:i:s'),
                    'status' => $status->value,
                    'rfid_scan_time' => $now,
                ]);
            });

            $statusLabel = $status === AttendanceStatus::LATE ? ' (Terlambat)' : '';

            return [
                'status' => 'success',
                'message' => 'Berhasil absen di ' . ($activeSchedule->subject->name ?? '-') . $statusLabel,
                'card_uid' => $rfidCardNumber,
                'timestamp' => $now->toIso8601String(),
                'user' => ['name' => $userName, 'employee_id' => $studentId],
                'subject' => $activeSchedule->subject->name ?? '-',
            ];
        }

        // ===== SCENARIO 2 & 3: No active schedule, check past schedule =====
        $pastSchedule = $this->scheduleRepository->findRecentPastSchedule(
            $classroomId,
            $dayOfWeek,
            $timeString,
            3 // Lookback 3 hours
        );

        if ($pastSchedule !== null) {
            // Check if already recorded for this past schedule
            $existingPast = $this->attendanceRepository->findExisting($student->id, $pastSchedule->id, $today);

            if ($existingPast !== null) {
                // Already has attendance record
                $statusLabel = $existingPast->status->label();

                return [
                    'status' => 'info',
                    'message' => 'Anda sudah tercatat ' . $statusLabel . ' di ' . ($pastSchedule->subject->name ?? '-'),
                    'card_uid' => $rfidCardNumber,
                    'timestamp' => $now->toIso8601String(),
                    'user' => ['name' => $userName, 'employee_id' => $studentId],
                    'subject' => $pastSchedule->subject->name ?? '-',
                ];
            }

            // No attendance record → Auto-mark as ALPHA (absent)
            DB::transaction(function () use ($student, $pastSchedule, $today, $now) {
                $this->attendanceRepository->create([
                    'student_id' => $student->id,
                    'schedule_id' => $pastSchedule->id,
                    'attendance_date' => $today,
                    'status' => AttendanceStatus::ABSENT->value,
                    'notes' => 'Auto-marked ALPHA - scan after schedule ended',
                    'rfid_scan_time' => $now,
                ]);
            });

            return [
                'status' => 'warning',
                'message' => 'Terlambat! Anda dinyatakan ALFA di ' . ($pastSchedule->subject->name ?? '-'),
                'card_uid' => $rfidCardNumber,
                'timestamp' => $now->toIso8601String(),
                'user' => ['name' => $userName, 'employee_id' => $studentId],
                'subject' => $pastSchedule->subject->name ?? '-',
            ];
        }

        // ===== No active or past schedule found =====
        throw new \InvalidArgumentException('Tidak ada mata pelajaran saat ini.');
    }

    /**
     * Record attendance manually.
     *
     * @param array{student_id: int, schedule_id: int, attendance_date: string, status: string, check_in_time?: string|null, check_out_time?: string|null, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function recordAttendanceManual(array $data): Attendance
    {
        $date = Carbon::parse($data['attendance_date']);

        // Check for existing record
        $existing = $this->attendanceRepository->findExisting(
            $data['student_id'],
            $data['schedule_id'],
            $date
        );

        if ($existing !== null) {
            throw new \InvalidArgumentException('Attendance already recorded for this student on this schedule and date.');
        }

        return DB::transaction(function () use ($data, $date) {
            return $this->attendanceRepository->create([
                'student_id' => $data['student_id'],
                'schedule_id' => $data['schedule_id'],
                'attendance_date' => $date,
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Bulk record attendance for a class.
     *
     * @param array<int, array{student_id: int, status: string, notes?: string|null}> $attendanceData
     *
     * @return Collection<int, Attendance>
     *
     * @throws \InvalidArgumentException
     */
    public function bulkRecordAttendance(int $scheduleId, Carbon $date, array $attendanceData): Collection
    {
        $schedule = $this->scheduleRepository->findById($scheduleId);
        if ($schedule === null) {
            throw new \InvalidArgumentException('Schedule not found.');
        }

        return DB::transaction(function () use ($scheduleId, $date, $attendanceData) {
            $records = [];

            foreach ($attendanceData as $data) {
                // Skip if already exists
                $existing = $this->attendanceRepository->findExisting(
                    $data['student_id'],
                    $scheduleId,
                    $date
                );

                if ($existing !== null) {
                    // Update existing record
                    $this->attendanceRepository->update($existing, [
                        'status' => $data['status'],
                        'notes' => $data['notes'] ?? null,
                        'recorded_by' => Auth::id(),
                    ]);
                } else {
                    $records[] = [
                        'student_id' => $data['student_id'],
                        'schedule_id' => $scheduleId,
                        'attendance_date' => $date,
                        'status' => $data['status'],
                        'notes' => $data['notes'] ?? null,
                        'recorded_by' => Auth::id(),
                    ];
                }
            }

            if (count($records) > 0) {
                return $this->attendanceRepository->createMany($records);
            }

            // Return updated records if no new ones created
            return $this->attendanceRepository->getByScheduleAndDate($scheduleId, $date);
        });
    }

    /**
     * Update attendance.
     *
     * @param array{status?: string, check_in_time?: string|null, check_out_time?: string|null, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateAttendance(int $attendanceId, array $data): Attendance
    {
        $attendance = $this->attendanceRepository->findById($attendanceId);

        if ($attendance === null) {
            throw new \InvalidArgumentException("Attendance with ID {$attendanceId} not found.");
        }

        return DB::transaction(function () use ($attendance, $data) {
            $updateData = [];

            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            if (array_key_exists('check_in_time', $data)) {
                $updateData['check_in_time'] = $data['check_in_time'];
            }
            if (array_key_exists('check_out_time', $data)) {
                $updateData['check_out_time'] = $data['check_out_time'];
            }
            if (array_key_exists('notes', $data)) {
                $updateData['notes'] = $data['notes'];
            }

            // Mark as manually updated
            $updateData['recorded_by'] = Auth::id();

            if (count($updateData) > 0) {
                return $this->attendanceRepository->update($attendance, $updateData);
            }

            return $attendance;
        });
    }

    /**
     * Delete attendance.
     *
     * @throws \InvalidArgumentException
     */
    public function deleteAttendance(int $attendanceId): bool
    {
        $attendance = $this->attendanceRepository->findById($attendanceId);

        if ($attendance === null) {
            throw new \InvalidArgumentException("Attendance with ID {$attendanceId} not found.");
        }

        return $this->attendanceRepository->delete($attendance);
    }

    /**
     * Get classroom attendance for a date.
     *
     * @return array{students: Collection, attendances: Collection, schedule: Schedule|null}
     */
    public function getClassroomAttendance(int $classroomId, Carbon $date): array
    {
        // Phase G: Use enrollment-based lookup
        $students = $this->studentRepository->getByClassroomViaEnrollment($classroomId);
        $attendances = $this->attendanceRepository->getByClassroomAndDate($classroomId, $date);

        return [
            'students' => $students,
            'attendances' => $attendances,
        ];
    }

    /**
     * Get student attendance history.
     *
     * @return Collection<int, Attendance>
     */
    public function getStudentAttendanceHistory(int $studentId, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = $this->attendanceRepository->getDataTableQuery()
            ->where('student_id', $studentId);

        if ($startDate !== null) {
            $query->where('attendance_date', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('attendance_date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get statistics for dashboard.
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        return $this->attendanceRepository->getTodayStats();
    }

    /**
     * Get DataTables query builder.
     *
     * @return Builder<Attendance>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->attendanceRepository->getDataTableQuery();
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
     * Get schedules by classroom.
     *
     * @return Collection<int, Schedule>
     */
    public function getSchedulesByClassroom(int $classroomId): Collection
    {
        return $this->scheduleRepository->getByClassroom($classroomId);
    }

    /**
     * Determine attendance status based on schedule time.
     */
    private function determineStatus(Schedule $schedule, Carbon $scanTime): AttendanceStatus
    {
        $startTime = Carbon::parse($schedule->start_time);
        $toleranceMinutes = 15; // 15 minutes tolerance

        $lateThreshold = $startTime->copy()->addMinutes($toleranceMinutes);

        if ($scanTime->format('H:i:s') <= $lateThreshold->format('H:i:s')) {
            return AttendanceStatus::PRESENT;
        }

        return AttendanceStatus::LATE;
    }

    /**
     * Get the classroom ID for a student via enrollment.
     * Phase G: Enrollment is the only source (no fallback).
     */
    private function getStudentClassroomId(\App\Models\Student $student): ?int
    {
        $currentEnrollment = $this->enrollmentRepository->getCurrentEnrollment($student->id);

        return $currentEnrollment?->classroom_id;
    }
}

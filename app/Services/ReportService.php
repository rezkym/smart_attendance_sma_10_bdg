<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected StudentRepositoryInterface $studentRepository,
        protected ClassroomRepositoryInterface $classroomRepository,
        protected AcademicYearRepositoryInterface $academicYearRepository
    ) {}

    /**
     * Get attendance report data by classroom.
     *
     * @return array{
     *     classroom: array,
     *     summary: array<string, int|float>,
     *     students: array<int, array>,
     *     date_range: array{start: string, end: string}
     * }
     */
    public function getAttendanceReportByClassroom(int $classroomId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if ($classroom === null) {
            throw new \InvalidArgumentException("Classroom with ID {$classroomId} not found.");
        }

        // Default date range: current month
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        // Get status counts
        $statusCounts = $this->attendanceRepository->getStatusCountByClassroomAndDateRange(
            $classroomId,
            $startDate,
            $endDate
        );

        // Calculate percentages
        $total = $statusCounts['total'];
        $summary = [
            'total_records' => $total,
            'present' => $statusCounts['present'],
            'late' => $statusCounts['late'],
            'excused' => $statusCounts['excused'],
            'sick' => $statusCounts['sick'],
            'absent' => $statusCounts['absent'],
            'present_percentage' => $total > 0 ? round(($statusCounts['present'] / $total) * 100, 2) : 0,
            'late_percentage' => $total > 0 ? round(($statusCounts['late'] / $total) * 100, 2) : 0,
            'excused_percentage' => $total > 0 ? round(($statusCounts['excused'] / $total) * 100, 2) : 0,
            'sick_percentage' => $total > 0 ? round(($statusCounts['sick'] / $total) * 100, 2) : 0,
            'absent_percentage' => $total > 0 ? round(($statusCounts['absent'] / $total) * 100, 2) : 0,
            'attendance_rate' => $total > 0 ? round((($statusCounts['present'] + $statusCounts['late']) / $total) * 100, 2) : 0,
        ];

        // Get per-student attendance summary
        $students = $this->getStudentAttendanceSummary($classroomId, $startDate, $endDate);

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'grade_level' => $classroom->grade_level,
            ],
            'summary' => $summary,
            'students' => $students,
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    /**
     * Get attendance report data by student.
     *
     * @return array{
     *     student: array,
     *     summary: array<string, int|float>,
     *     records: array,
     *     date_range: array{start: string, end: string}
     * }
     */
    public function getAttendanceReportByStudent(int $studentId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $student = $this->studentRepository->findById($studentId);

        if ($student === null) {
            throw new \InvalidArgumentException("Student with ID {$studentId} not found.");
        }

        // Default date range: current month
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $attendances = $this->attendanceRepository->getByStudentAndDateRange($studentId, $startDate, $endDate);

        // Calculate summary
        $total = $attendances->count();
        $statusCounts = [
            'present' => $attendances->where('status', AttendanceStatus::PRESENT->value)->count(),
            'late' => $attendances->where('status', AttendanceStatus::LATE->value)->count(),
            'excused' => $attendances->where('status', AttendanceStatus::EXCUSED->value)->count(),
            'sick' => $attendances->where('status', AttendanceStatus::SICK->value)->count(),
            'absent' => $attendances->where('status', AttendanceStatus::ABSENT->value)->count(),
        ];

        $summary = [
            'total_records' => $total,
            'present' => $statusCounts['present'],
            'late' => $statusCounts['late'],
            'excused' => $statusCounts['excused'],
            'sick' => $statusCounts['sick'],
            'absent' => $statusCounts['absent'],
            'attendance_rate' => $total > 0 ? round((($statusCounts['present'] + $statusCounts['late']) / $total) * 100, 2) : 0,
        ];

        // Format attendance records
        $records = $attendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'date' => Carbon::parse($attendance->attendance_date)->toDateString(),
                'status' => $attendance->status,
                'status_label' => AttendanceStatus::from($attendance->status)->label(),
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'subject' => $attendance->schedule?->subject?->name ?? '-',
                'notes' => $attendance->notes,
            ];
        })->toArray();

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->display_name ?? 'Unknown',
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'classroom' => $student->currentClassroom()?->name ?? '-',
            ],
            'summary' => $summary,
            'records' => $records,
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    /**
     * Get attendance summary for a specific date (for single day view).
     *
     * @return array{
     *     date: string,
     *     classroom: array,
     *     summary: array<string, int>,
     *     students: array
     * }
     */
    public function getAttendanceSummary(int $classroomId, Carbon $date): array
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if ($classroom === null) {
            throw new \InvalidArgumentException("Classroom with ID {$classroomId} not found.");
        }

        $attendances = $this->attendanceRepository->getByClassroomAndDate($classroomId, $date);

        // Phase E: Use enrollment-based student lookup with fallback
        $students = $this->studentRepository->getByClassroomViaEnrollment($classroomId);

        $studentAttendanceMap = $attendances->keyBy('student_id');

        $summary = [
            'total_students' => $students->count(),
            'present' => $attendances->where('status', AttendanceStatus::PRESENT->value)->count(),
            'late' => $attendances->where('status', AttendanceStatus::LATE->value)->count(),
            'excused' => $attendances->where('status', AttendanceStatus::EXCUSED->value)->count(),
            'sick' => $attendances->where('status', AttendanceStatus::SICK->value)->count(),
            'absent' => $students->count() - $attendances->count()
                + $attendances->where('status', AttendanceStatus::ABSENT->value)->count(),
        ];

        $studentList = $students->map(function ($student) use ($studentAttendanceMap) {
            $attendance = $studentAttendanceMap->get($student->id);

            return [
                'id' => $student->id,
                'name' => $student->user?->display_name ?? 'Unknown',
                'nisn' => $student->nisn,
                'status' => $attendance?->status ?? AttendanceStatus::ABSENT->value,
                'status_label' => $attendance
                    ? AttendanceStatus::from($attendance->status)->label()
                    : 'Tidak Hadir',
                'check_in_time' => $attendance?->check_in_time,
                'check_out_time' => $attendance?->check_out_time,
            ];
        })->toArray();

        return [
            'date' => $date->toDateString(),
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'grade_level' => $classroom->grade_level,
            ],
            'summary' => $summary,
            'students' => $studentList,
        ];
    }

    /**
     * Get monthly attendance statistics.
     *
     * @return array{
     *     month: int,
     *     year: int,
     *     classroom: array,
     *     summary: array<string, int|float>,
     *     daily_trend: array
     * }
     */
    public function getMonthlyAttendanceStats(int $classroomId, int $year, int $month): array
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if ($classroom === null) {
            throw new \InvalidArgumentException("Classroom with ID {$classroomId} not found.");
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $dailySummary = $this->attendanceRepository->getDailyAttendanceSummary(
            $classroomId,
            $startDate,
            $endDate
        );

        $statusCounts = $this->attendanceRepository->getStatusCountByClassroomAndDateRange(
            $classroomId,
            $startDate,
            $endDate
        );

        $total = $statusCounts['total'];

        $summary = [
            'total_records' => $total,
            'present' => $statusCounts['present'],
            'late' => $statusCounts['late'],
            'excused' => $statusCounts['excused'],
            'sick' => $statusCounts['sick'],
            'absent' => $statusCounts['absent'],
            'attendance_rate' => $total > 0 ? round((($statusCounts['present'] + $statusCounts['late']) / $total) * 100, 2) : 0,
        ];

        // Format daily trend for chart
        $dailyTrend = [];
        foreach ($dailySummary as $date => $counts) {
            $dailyTrend[] = [
                'date' => $date,
                'present' => $counts['present'] + $counts['late'],
                'absent' => $counts['absent'],
                'excused' => $counts['excused'],
                'sick' => $counts['sick'],
                'total' => $counts['total'],
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'grade_level' => $classroom->grade_level,
            ],
            'summary' => $summary,
            'daily_trend' => $dailyTrend,
        ];
    }

    /**
     * Get weekly attendance trend data for charts.
     *
     * @return array{
     *     labels: array<string>,
     *     datasets: array<string, array<int>>
     * }
     */
    public function getWeeklyAttendanceTrend(int $classroomId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if ($classroom === null) {
            throw new \InvalidArgumentException("Classroom with ID {$classroomId} not found.");
        }

        // Default: last 4 weeks
        $endDate = $endDate ?? Carbon::now();
        $startDate = $startDate ?? Carbon::now()->subWeeks(4);

        $dailySummary = $this->attendanceRepository->getDailyAttendanceSummary(
            $classroomId,
            $startDate,
            $endDate
        );

        $labels = [];
        $presentData = [];
        $absentData = [];
        $sickData = [];
        $excusedData = [];

        foreach ($dailySummary as $date => $counts) {
            $labels[] = Carbon::parse($date)->format('d M');
            $presentData[] = $counts['present'] + $counts['late'];
            $absentData[] = $counts['absent'];
            $sickData[] = $counts['sick'];
            $excusedData[] = $counts['excused'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                'present' => $presentData,
                'absent' => $absentData,
                'sick' => $sickData,
                'excused' => $excusedData,
            ],
        ];
    }

    /**
     * Export attendance report to CSV.
     */
    public function exportAttendanceReport(int $classroomId, Carbon $startDate, Carbon $endDate): string
    {
        $report = $this->getAttendanceReportByClassroom($classroomId, $startDate, $endDate);

        $csvContent = [];

        // Header
        $csvContent[] = [
            'Laporan Kehadiran - ' . $report['classroom']['name'],
            '',
            '',
            '',
            '',
            '',
        ];
        $csvContent[] = [
            'Periode: ' . $report['date_range']['start'] . ' s/d ' . $report['date_range']['end'],
            '',
            '',
            '',
            '',
            '',
        ];
        $csvContent[] = [];

        // Summary
        $csvContent[] = ['RINGKASAN'];
        $csvContent[] = ['Total Record', $report['summary']['total_records']];
        $csvContent[] = ['Hadir', $report['summary']['present'], $report['summary']['present_percentage'] . '%'];
        $csvContent[] = ['Terlambat', $report['summary']['late'], $report['summary']['late_percentage'] . '%'];
        $csvContent[] = ['Izin', $report['summary']['excused'], $report['summary']['excused_percentage'] . '%'];
        $csvContent[] = ['Sakit', $report['summary']['sick'], $report['summary']['sick_percentage'] . '%'];
        $csvContent[] = ['Alpha', $report['summary']['absent'], $report['summary']['absent_percentage'] . '%'];
        $csvContent[] = ['Tingkat Kehadiran', $report['summary']['attendance_rate'] . '%'];
        $csvContent[] = [];

        // Student detail
        $csvContent[] = ['DETAIL PER SISWA'];
        $csvContent[] = ['No', 'Nama Siswa', 'NISN', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Tingkat Kehadiran'];

        $number = 1;
        foreach ($report['students'] as $student) {
            $csvContent[] = [
                $number++,
                $student['name'],
                $student['nisn'],
                $student['present'],
                $student['late'],
                $student['excused'],
                $student['sick'],
                $student['absent'],
                $student['attendance_rate'] . '%',
            ];
        }

        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            throw new \RuntimeException('Failed to create CSV output stream.');
        }

        foreach ($csvContent as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        if ($csv === false) {
            throw new \RuntimeException('Failed to generate CSV content.');
        }

        return $csv;
    }

    /**
     * Get available classrooms for report filter.
     *
     * @return Collection<int, \App\Models\Classroom>
     */
    public function getAvailableClassrooms(): Collection
    {
        return $this->classroomRepository->getAllActive();
    }

    /**
     * Get available academic years for report filter.
     *
     * @return Collection<int, \App\Models\AcademicYear>
     */
    public function getAvailableAcademicYears(): Collection
    {
        return $this->academicYearRepository->getAll();
    }

    /**
     * Get per-student attendance summary for a classroom.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     nisn: string|null,
     *     present: int,
     *     late: int,
     *     excused: int,
     *     sick: int,
     *     absent: int,
     *     total: int,
     *     attendance_rate: float
     * }>
     */
    private function getStudentAttendanceSummary(int $classroomId, Carbon $startDate, Carbon $endDate): array
    {
        // Phase E: Use enrollment-based student lookup with fallback
        $students = $this->studentRepository->getByClassroomViaEnrollment($classroomId);
        $attendances = $this->attendanceRepository->getByClassroomAndDateRange($classroomId, $startDate, $endDate);

        $studentSummary = [];

        foreach ($students as $student) {
            $studentAttendances = $attendances->where('student_id', $student->id);
            $total = $studentAttendances->count();

            $statusCounts = [
                'present' => $studentAttendances->where('status', AttendanceStatus::PRESENT->value)->count(),
                'late' => $studentAttendances->where('status', AttendanceStatus::LATE->value)->count(),
                'excused' => $studentAttendances->where('status', AttendanceStatus::EXCUSED->value)->count(),
                'sick' => $studentAttendances->where('status', AttendanceStatus::SICK->value)->count(),
                'absent' => $studentAttendances->where('status', AttendanceStatus::ABSENT->value)->count(),
            ];

            $studentSummary[] = [
                'id' => $student->id,
                'name' => $student->user?->display_name ?? 'Unknown',
                'nisn' => $student->nisn,
                'present' => $statusCounts['present'],
                'late' => $statusCounts['late'],
                'excused' => $statusCounts['excused'],
                'sick' => $statusCounts['sick'],
                'absent' => $statusCounts['absent'],
                'total' => $total,
                'attendance_rate' => $total > 0 ? round((($statusCounts['present'] + $statusCounts['late']) / $total) * 100, 2) : 0,
            ];
        }

        return $studentSummary;
    }
}

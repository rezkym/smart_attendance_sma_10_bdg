<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        protected StudentRepositoryInterface $studentRepository,
        protected TeacherRepositoryInterface $teacherRepository,
        protected ClassroomRepositoryInterface $classroomRepository,
        protected SubjectRepositoryInterface $subjectRepository,
        protected AttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * Get today's attendance statistics.
     *
     * @return array{
     *     total_students: int,
     *     present: int,
     *     late: int,
     *     excused: int,
     *     sick: int,
     *     absent: int,
     *     total_recorded: int,
     *     attendance_percentage: float
     * }
     */
    public function getTodayAttendanceStats(): array
    {
        $todayStats = $this->attendanceRepository->getTodayStats();
        $totalStudents = $this->studentRepository->getActiveCount();

        $totalRecorded = $todayStats['present'] + $todayStats['late'] + $todayStats['excused']
            + $todayStats['sick'] + $todayStats['absent'];

        // Calculate percentage based on recorded attendance
        $attendedCount = $todayStats['present'] + $todayStats['late'];
        $attendancePercentage = $totalRecorded > 0
            ? round(($attendedCount / $totalRecorded) * 100, 2)
            : 0;

        return [
            'total_students' => $totalStudents,
            'present' => $todayStats['present'],
            'late' => $todayStats['late'],
            'excused' => $todayStats['excused'],
            'sick' => $todayStats['sick'],
            'absent' => $todayStats['absent'],
            'total_recorded' => $totalRecorded,
            'attendance_percentage' => $attendancePercentage,
        ];
    }

    /**
     * Get weekly attendance trend data for charts.
     *
     * @return array{
     *     labels: array<string>,
     *     datasets: array{
     *         present: array<int>,
     *         absent: array<int>,
     *         excused: array<int>,
     *         sick: array<int>
     *     }
     * }
     */
    public function getWeeklyAttendanceTrend(): array
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6);

        $labels = [];
        $presentData = [];
        $absentData = [];
        $excusedData = [];
        $sickData = [];

        // Generate data for each day in the range
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $labels[] = $date->format('D, d M');

            $dayStats = $this->getDayAttendanceStats($date);
            $presentData[] = $dayStats['present'] + $dayStats['late'];
            $absentData[] = $dayStats['absent'];
            $excusedData[] = $dayStats['excused'];
            $sickData[] = $dayStats['sick'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                'present' => $presentData,
                'absent' => $absentData,
                'excused' => $excusedData,
                'sick' => $sickData,
            ],
        ];
    }

    /**
     * Get classroom attendance ranking.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     grade_level: int,
     *     attendance_rate: float,
     *     total_students: int
     * }>
     */
    public function getClassroomAttendanceRanking(int $limit = 5): array
    {
        $classrooms = $this->classroomRepository->getAllActive();

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        $rankings = [];

        foreach ($classrooms as $classroom) {
            $stats = $this->attendanceRepository->getStatsByClassroom(
                $classroom->id,
                $startDate,
                $endDate
            );

            $rankings[] = [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'grade_level' => $classroom->grade_level,
                'attendance_rate' => (float) $stats['attendance_rate'],
                // Phase F: Use enrollment-based student count with fallback
                'total_students' => $this->studentRepository->getByClassroomViaEnrollment($classroom->id)->count(),
            ];
        }

        // Sort by attendance rate descending
        usort($rankings, function ($a, $b) {
            return $b['attendance_rate'] <=> $a['attendance_rate'];
        });

        // Return top N
        return array_slice($rankings, 0, $limit);
    }

    /**
     * Get master data statistics for dashboard cards.
     *
     * @return array{
     *     total_students: int,
     *     active_students: int,
     *     total_teachers: int,
     *     active_teachers: int,
     *     total_classrooms: int,
     *     active_classrooms: int,
     *     total_subjects: int,
     *     active_subjects: int
     * }
     */
    public function getMasterDataStats(): array
    {
        return [
            'total_students' => $this->studentRepository->getTotalCount(),
            'active_students' => $this->studentRepository->getActiveCount(),
            'total_teachers' => $this->teacherRepository->getTotalCount(),
            'active_teachers' => $this->teacherRepository->getActiveCount(),
            'total_classrooms' => $this->classroomRepository->getTotalCount(),
            'active_classrooms' => $this->classroomRepository->getActiveCount(),
            'total_subjects' => $this->subjectRepository->getTotalCount(),
            'active_subjects' => $this->subjectRepository->getActiveCount(),
        ];
    }

    /**
     * Get all dashboard data combined.
     *
     * @return array{
     *     master_data: array,
     *     today_attendance: array,
     *     weekly_trend: array,
     *     classroom_ranking: array
     * }
     */
    public function getAllDashboardData(): array
    {
        return [
            'master_data' => $this->getMasterDataStats(),
            'today_attendance' => $this->getTodayAttendanceStats(),
            'weekly_trend' => $this->getWeeklyAttendanceTrend(),
            'classroom_ranking' => $this->getClassroomAttendanceRanking(),
        ];
    }

    /**
     * Get attendance stats for a specific day.
     *
     * @return array<string, int>
     */
    private function getDayAttendanceStats(Carbon $date): array
    {
        return $this->attendanceRepository->getStatsByDate($date);
    }
}


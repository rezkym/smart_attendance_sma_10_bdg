<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\GetSchedulesRequest;
use App\Http\Requests\Teacher\ViewScheduleRequest;
use App\Models\Schedule;
use App\Services\TeacherDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MyScheduleController extends Controller
{
    public function __construct(
        protected TeacherDashboardService $service
    ) {}

    /**
     * Display the my schedules page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $teacher = $this->service->getTeacherByUserId($user->id);

        if ($teacher === null) {
            abort(403, 'You do not have a teacher profile.');
        }

        return view('content.pages.teacher.MySchedules', [
            'teacher' => $teacher,
        ]);
    }

    /**
     * Get schedules list for DataTables.
     */
    public function list(GetSchedulesRequest $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $this->service->getTeacherByUserId($user->id);

        if ($teacher === null) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
            ], 403);
        }

        $filters = $request->getFilters();
        $query = $this->service->getDataTableQuery($teacher->id, $filters);

        return DataTables::eloquent($query)
            ->addColumn('day_name', function (Schedule $schedule): string {
                $days = [
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                    7 => 'Sunday',
                ];

                return $days[$schedule->day_of_week] ?? '-';
            })
            ->addColumn('subject_name', function (Schedule $schedule): string {
                return $schedule->subject?->name ?? '-';
            })
            ->addColumn('classroom_name', function (Schedule $schedule): string {
                return $schedule->classroom?->name ?? '-';
            })
            ->addColumn('time_range', function (Schedule $schedule): string {
                $start = substr($schedule->start_time, 0, 5);
                $end = substr($schedule->end_time, 0, 5);

                return "{$start} - {$end}";
            })
            ->addColumn('academic_year', function (Schedule $schedule): string {
                return $schedule->academicYear?->name ?? '-';
            })
            ->addColumn('semester', function (Schedule $schedule): string {
                return $schedule->semester?->name ?? '-';
            })
            ->rawColumns([])
            ->toJson();
    }

    /**
     * Get today's schedules.
     */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $this->service->getTeacherByUserId($user->id);

        if ($teacher === null) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
            ], 403);
        }

        $todaySchedules = $this->service->getFormattedTodaySchedules($teacher->id);

        return response()->json([
            'success' => true,
            'message' => 'Today schedules retrieved successfully.',
            'data' => $todaySchedules,
        ]);
    }

    /**
     * Show schedule detail.
     */
    public function show(ViewScheduleRequest $request): JsonResponse
    {
        $schedule = $request->getSchedule();

        return response()->json([
            'success' => true,
            'message' => 'Schedule retrieved successfully.',
            'data' => [
                'id' => $schedule->id,
                'subject' => $schedule->subject?->name ?? '-',
                'classroom' => $schedule->classroom?->name ?? '-',
                'day_of_week' => $schedule->day_of_week,
                'day_name' => $this->getDayName($schedule->day_of_week),
                'start_time' => substr($schedule->start_time, 0, 5),
                'end_time' => substr($schedule->end_time, 0, 5),
                'academic_year' => $schedule->academicYear?->name ?? '-',
                'semester' => $schedule->semester?->name ?? '-',
                'is_active' => $schedule->is_active,
            ],
        ]);
    }

    /**
     * Get day name from day of week number.
     */
    private function getDayName(int $dayOfWeek): string
    {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        return $days[$dayOfWeek] ?? '-';
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\DayOfWeek;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    /**
     * Display schedules list page.
     */
    public function index(): View
    {
        $stats = $this->scheduleService->getStats();
        $academicYears = $this->scheduleService->getAvailableAcademicYears();
        $classrooms = $this->scheduleService->getAvailableClassrooms();
        $subjects = $this->scheduleService->getAvailableSubjects();
        $teachers = $this->scheduleService->getAvailableTeachers();
        $daysOfWeek = DayOfWeek::toArray();

        return view('content.pages.admin.schedules', compact(
            'stats',
            'academicYears',
            'classrooms',
            'subjects',
            'teachers',
            'daysOfWeek'
        ));
    }

    /**
     * DataTables server-side data.
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->scheduleService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('classroom_name', function ($schedule) {
                return $schedule->classroom->name ?? '-';
            })
            ->addColumn('subject_name', function ($schedule) {
                return $schedule->subject->name ?? '-';
            })
            ->addColumn('teacher_name', function ($schedule) {
                return $schedule->teacher?->user?->name ?? '-';
            })
            ->addColumn('academic_year_name', function ($schedule) {
                return $schedule->academicYear->name ?? '-';
            })
            ->addColumn('day_label', function ($schedule) {
                return $schedule->day_of_week->label();
            })
            ->addColumn('time_range', function ($schedule) {
                return $schedule->start_time . ' - ' . $schedule->end_time;
            })
            ->addColumn('status', function ($schedule) {
                return $schedule->is_active;
            })
            ->addColumn('actions', function ($schedule) {
                return $schedule->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get all dropdown data for schedule form.
     */
    public function dropdownData(): JsonResponse
    {
        $academicYears = $this->scheduleService->getAvailableAcademicYears();
        $classrooms = $this->scheduleService->getAvailableClassrooms();
        $subjects = $this->scheduleService->getAvailableSubjects();
        $teachers = $this->scheduleService->getAvailableTeachers();

        return response()->json([
            'success' => true,
            'data' => [
                'academicYears' => $academicYears->map(function ($year) {
                    return [
                        'id' => $year->id,
                        'name' => $year->name,
                        'is_active' => $year->is_active,
                    ];
                }),
                'classrooms' => $classrooms->map(function ($classroom) {
                    return [
                        'id' => $classroom->id,
                        'name' => $classroom->name,
                        'grade_level' => $classroom->grade_level,
                    ];
                }),
                'subjects' => $subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name,
                    ];
                }),
                'teachers' => $teachers->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->user?->name ?? '',
                        'display_name' => $teacher->user?->name ?? '',
                        'nip' => $teacher->nip,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get schedules by classroom (for dropdowns/calendar).
     */
    public function getByClassroom(int $classroom): JsonResponse
    {
        $schedules = $this->scheduleService->getSchedulesByClassroom($classroom);

        return response()->json([
            'success' => true,
            'data' => $schedules->map(function ($schedule) {
                return $this->formatScheduleData($schedule);
            }),
        ]);
    }

    /**
     * Get schedules by teacher.
     */
    public function getByTeacher(int $teacher): JsonResponse
    {
        $schedules = $this->scheduleService->getSchedulesByTeacher($teacher);

        return response()->json([
            'success' => true,
            'data' => $schedules->map(function ($schedule) {
                return $this->formatScheduleData($schedule);
            }),
        ]);
    }

    /**
     * Store a new schedule.
     */
    public function store(StoreScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->createSchedule($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully.',
                'data' => $this->formatScheduleData($schedule),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get schedule data for viewing/editing.
     */
    public function show(int $schedule): JsonResponse
    {
        $scheduleData = $this->scheduleService->getScheduleById($schedule);

        if ($scheduleData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatScheduleData($scheduleData),
        ]);
    }

    /**
     * Update an existing schedule.
     */
    public function update(UpdateScheduleRequest $request, int $schedule): JsonResponse
    {
        try {
            $updatedSchedule = $this->scheduleService->updateSchedule($schedule, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully.',
                'data' => $this->formatScheduleData($updatedSchedule),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a schedule.
     */
    public function destroy(int $schedule): JsonResponse
    {
        try {
            $this->scheduleService->deleteSchedule($schedule);

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available classrooms for dropdown.
     */
    public function availableClassrooms(): JsonResponse
    {
        $classrooms = $this->scheduleService->getAvailableClassrooms();

        return response()->json([
            'success' => true,
            'data' => $classrooms->map(function ($classroom) {
                return [
                    'id' => $classroom->id,
                    'name' => $classroom->name,
                    'grade_level' => $classroom->grade_level,
                ];
            }),
        ]);
    }

    /**
     * Get available subjects for dropdown.
     */
    public function availableSubjects(): JsonResponse
    {
        $subjects = $this->scheduleService->getAvailableSubjects();

        return response()->json([
            'success' => true,
            'data' => $subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'code' => $subject->code,
                    'name' => $subject->name,
                ];
            }),
        ]);
    }

    /**
     * Get available teachers for dropdown.
     */
    public function availableTeachers(): JsonResponse
    {
        $teachers = $this->scheduleService->getAvailableTeachers();

        return response()->json([
            'success' => true,
            'data' => $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name ?? '',
                    'nip' => $teacher->nip,
                ];
            }),
        ]);
    }

    /**
     * Get available academic years for dropdown.
     */
    public function availableAcademicYears(): JsonResponse
    {
        $academicYears = $this->scheduleService->getAvailableAcademicYears();

        return response()->json([
            'success' => true,
            'data' => $academicYears->map(function ($year) {
                return [
                    'id' => $year->id,
                    'name' => $year->name,
                    'is_active' => $year->is_active,
                ];
            }),
        ]);
    }

    /**
     * Get days of week for dropdown.
     */
    public function daysOfWeek(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DayOfWeek::toArray(),
        ]);
    }

    /**
     * Format schedule data for JSON response.
     *
     * @return array<string, mixed>
     */
    private function formatScheduleData(\App\Models\Schedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'classroom_id' => $schedule->classroom_id,
            'classroom_name' => $schedule->classroom->name ?? '',
            'subject_id' => $schedule->subject_id,
            'subject_name' => $schedule->subject->name ?? '',
            'subject_code' => $schedule->subject->code ?? '',
            'teacher_id' => $schedule->teacher_id,
            'teacher_name' => $schedule->teacher?->user?->name ?? '',
            'academic_year_id' => $schedule->academic_year_id,
            'academic_year_name' => $schedule->academicYear->name ?? '',
            'semester_id' => $schedule->semester_id,
            'semester_type' => $schedule->semester?->type?->label() ?? '',
            'day_of_week' => $schedule->day_of_week->value,
            'day_label' => $schedule->day_of_week->label(),
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'is_active' => $schedule->is_active,
            'notes' => $schedule->notes,
        ];
    }
}

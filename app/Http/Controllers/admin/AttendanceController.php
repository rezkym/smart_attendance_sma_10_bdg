<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\BulkAttendanceRequest;
use App\Http\Requests\Attendance\RfidAttendanceRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display attendances list page.
     */
    public function index(): View
    {
        $stats = $this->attendanceService->getStats();
        $classrooms = $this->attendanceService->getAvailableClassrooms();
        $statuses = AttendanceStatus::toArrayWithColors();

        return view('content.pages.admin.attendances', compact(
            'stats',
            'classrooms',
            'statuses'
        ));
    }

    /**
     * DataTables server-side data.
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->attendanceService->getDataTableQuery();

        // Apply filters
        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->input('date'));
        }

        if ($request->filled('classroom_id')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('classroom_id', $request->input('classroom_id'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return DataTables::eloquent($query)
            ->addColumn('student_name', function ($attendance) {
                return $attendance->student?->user?->name ?? '-';
            })
            ->addColumn('student_nisn', function ($attendance) {
                return $attendance->student?->nisn ?? '-';
            })
            ->addColumn('classroom_name', function ($attendance) {
                return $attendance->schedule?->classroom?->name ?? '-';
            })
            ->addColumn('subject_name', function ($attendance) {
                return $attendance->schedule?->subject?->name ?? '-';
            })
            ->addColumn('schedule_time', function ($attendance) {
                return $attendance->schedule
                    ? $attendance->schedule->start_time . ' - ' . $attendance->schedule->end_time
                    : '-';
            })
            ->addColumn('status_label', function ($attendance) {
                return $attendance->status->label();
            })
            ->addColumn('status_color', function ($attendance) {
                return $attendance->status->color();
            })
            ->addColumn('date_formatted', function ($attendance) {
                return $attendance->attendance_date->format('d M Y');
            })
            ->addColumn('recorded_by_name', function ($attendance) {
                return $attendance->recorder?->name ?? 'RFID Scan';
            })
            ->addColumn('actions', function ($attendance) {
                return $attendance->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get classroom attendance for a specific date.
     */
    public function classroomAttendance(int $classroom, string $date): JsonResponse
    {
        try {
            $attendanceDate = Carbon::parse($date);
            $data = $this->attendanceService->getClassroomAttendance($classroom, $attendanceDate);

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $data['students']->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->user?->name ?? '',
                            'nisn' => $student->nisn,
                            'nis' => $student->nis,
                        ];
                    }),
                    'attendances' => $data['attendances']->map(function ($attendance) {
                        return $this->formatAttendanceData($attendance);
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Record attendance by RFID scan.
     */
    public function recordByRfid(RfidAttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->recordAttendanceByRfid(
                $request->validated('rfid_card_number'),
                $request->validated('schedule_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'data' => $this->formatAttendanceData($attendance),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk record attendance for a class.
     */
    public function bulkRecord(BulkAttendanceRequest $request): JsonResponse
    {
        try {
            $date = Carbon::parse($request->validated('attendance_date'));
            $attendances = $this->attendanceService->bulkRecordAttendance(
                $request->validated('schedule_id'),
                $date,
                $request->validated('attendances')
            );

            return response()->json([
                'success' => true,
                'message' => 'Bulk attendance recorded successfully.',
                'data' => $attendances->map(function ($attendance) {
                    return $this->formatAttendanceData($attendance);
                }),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store a new attendance manually.
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->recordAttendanceManual($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'data' => $this->formatAttendanceData($attendance),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get attendance data for viewing/editing.
     */
    public function show(int $attendance): JsonResponse
    {
        $attendanceData = $this->attendanceService->getAttendanceById($attendance);

        if ($attendanceData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatAttendanceData($attendanceData),
        ]);
    }

    /**
     * Update an existing attendance.
     */
    public function update(UpdateAttendanceRequest $request, int $attendance): JsonResponse
    {
        try {
            $updatedAttendance = $this->attendanceService->updateAttendance($attendance, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully.',
                'data' => $this->formatAttendanceData($updatedAttendance),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an attendance.
     */
    public function destroy(int $attendance): JsonResponse
    {
        try {
            $this->attendanceService->deleteAttendance($attendance);

            return response()->json([
                'success' => true,
                'message' => 'Attendance deleted successfully.',
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
        $classrooms = $this->attendanceService->getAvailableClassrooms();

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
     * Get schedules for a classroom.
     */
    public function schedulesByClassroom(int $classroom): JsonResponse
    {
        $schedules = $this->attendanceService->getSchedulesByClassroom($classroom);

        return response()->json([
            'success' => true,
            'data' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject_name' => $schedule->subject?->name ?? '',
                    'teacher_name' => $schedule->teacher?->user?->name ?? '',
                    'day_label' => $schedule->day_of_week->label(),
                    'day_of_week' => $schedule->day_of_week->value,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                ];
            }),
        ]);
    }

    /**
     * Get attendance statuses for dropdown.
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => AttendanceStatus::toArrayWithColors(),
        ]);
    }

    /**
     * Get today's statistics.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->attendanceService->getStats(),
        ]);
    }

    /**
     * Format attendance data for JSON response.
     *
     * @return array<string, mixed>
     */
    private function formatAttendanceData(\App\Models\Attendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'student_id' => $attendance->student_id,
            'student_name' => $attendance->student?->user?->name ?? '',
            'student_nisn' => $attendance->student?->nisn ?? '',
            'student_nis' => $attendance->student?->nis ?? '',
            'schedule_id' => $attendance->schedule_id,
            'classroom_name' => $attendance->schedule?->classroom?->name ?? '',
            'subject_name' => $attendance->schedule?->subject?->name ?? '',
            'teacher_name' => $attendance->schedule?->teacher?->user?->name ?? '',
            'schedule_time' => $attendance->schedule
                ? $attendance->schedule->start_time . ' - ' . $attendance->schedule->end_time
                : '',
            'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
            'attendance_date_formatted' => $attendance->attendance_date->format('d M Y'),
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'status' => $attendance->status->value,
            'status_label' => $attendance->status->label(),
            'status_color' => $attendance->status->color(),
            'rfid_scan_time' => $attendance->rfid_scan_time?->format('Y-m-d H:i:s'),
            'notes' => $attendance->notes,
            'recorded_by' => $attendance->recorded_by,
            'recorded_by_name' => $attendance->recorder?->name ?? 'RFID Scan',
        ];
    }
}

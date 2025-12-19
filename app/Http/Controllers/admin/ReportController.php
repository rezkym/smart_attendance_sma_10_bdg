<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttendanceReportByClassroomRequest;
use App\Http\Requests\Admin\AttendanceReportByStudentRequest;
use App\Http\Requests\Admin\AttendanceReportExportRequest;
use App\Http\Requests\Admin\AttendanceSummaryRequest;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display the attendance report page.
     */
    public function index(): View
    {
        $classrooms = $this->reportService->getAvailableClassrooms();
        $academicYears = $this->reportService->getAvailableAcademicYears();

        return view('content.pages.admin.reports.attendance', compact('classrooms', 'academicYears'));
    }

    /**
     * Get attendance report data by classroom (AJAX).
     */
    public function attendanceByClassroom(AttendanceReportByClassroomRequest $request): JsonResponse
    {
        try {
            $classroomId = (int) $request->validated('classroom_id');
            $startDate = $request->filled('start_date') ? Carbon::parse($request->validated('start_date')) : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->validated('end_date')) : null;

            $report = $this->reportService->getAttendanceReportByClassroom($classroomId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'message' => 'Attendance report retrieved successfully.',
                'data' => $report,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get attendance report data by student (AJAX).
     */
    public function attendanceByStudent(AttendanceReportByStudentRequest $request): JsonResponse
    {
        try {
            $studentId = (int) $request->validated('student_id');
            $startDate = $request->filled('start_date') ? Carbon::parse($request->validated('start_date')) : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->validated('end_date')) : null;

            $report = $this->reportService->getAttendanceReportByStudent($studentId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'message' => 'Student attendance report retrieved successfully.',
                'data' => $report,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get attendance summary for a specific date (AJAX).
     */
    public function attendanceSummary(AttendanceSummaryRequest $request): JsonResponse
    {
        try {
            $classroomId = (int) $request->validated('classroom_id');
            $date = Carbon::parse($request->validated('date'));

            $summary = $this->reportService->getAttendanceSummary($classroomId, $date);

            return response()->json([
                'success' => true,
                'message' => 'Attendance summary retrieved successfully.',
                'data' => $summary,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get weekly attendance trend data for charts (AJAX).
     */
    public function weeklyTrend(AttendanceReportByClassroomRequest $request): JsonResponse
    {
        try {
            $classroomId = (int) $request->validated('classroom_id');
            $startDate = $request->filled('start_date') ? Carbon::parse($request->validated('start_date')) : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->validated('end_date')) : null;

            $trend = $this->reportService->getWeeklyAttendanceTrend($classroomId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'message' => 'Weekly attendance trend retrieved successfully.',
                'data' => $trend,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Export attendance report to CSV.
     */
    public function export(AttendanceReportExportRequest $request): Response
    {
        try {
            $classroomId = (int) $request->validated('classroom_id');
            $startDate = Carbon::parse($request->validated('start_date'));
            $endDate = Carbon::parse($request->validated('end_date'));

            $csvContent = $this->reportService->exportAttendanceReport($classroomId, $startDate, $endDate);

            $classroom = $this->reportService->getAvailableClassrooms()->firstWhere('id', $classroomId);
            $filename = 'attendance_report_' . ($classroom?->name ?? 'classroom') . '_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

            return response($csvContent, Response::HTTP_OK, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }
}

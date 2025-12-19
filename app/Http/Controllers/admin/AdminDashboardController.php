<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $dashboardData = $this->dashboardService->getAllDashboardData();

        return view('content.pages.admin.AdminDashboard', compact('dashboardData'));
    }

    /**
     * Get dashboard statistics (AJAX).
     */
    public function stats(): JsonResponse
    {
        $masterData = $this->dashboardService->getMasterDataStats();
        $todayAttendance = $this->dashboardService->getTodayAttendanceStats();
        $classroomRanking = $this->dashboardService->getClassroomAttendanceRanking();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics retrieved successfully.',
            'data' => [
                'master_data' => $masterData,
                'today_attendance' => $todayAttendance,
                'classroom_ranking' => $classroomRanking,
            ],
        ]);
    }

    /**
     * Get attendance trend data for charts (AJAX).
     */
    public function attendanceTrend(): JsonResponse
    {
        $trend = $this->dashboardService->getWeeklyAttendanceTrend();

        return response()->json([
            'success' => true,
            'message' => 'Attendance trend retrieved successfully.',
            'data' => $trend,
        ]);
    }
}


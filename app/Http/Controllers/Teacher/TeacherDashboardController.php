<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\TeacherDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __construct(
        protected TeacherDashboardService $service
    ) {}

    /**
     * Display the teacher dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $teacher = $this->service->getTeacherByUserId($user->id);

        if ($teacher === null) {
            abort(403, 'You do not have a teacher profile.');
        }

        $dashboardStats = $this->service->getDashboardStats($user->id);
        $todaySchedules = $this->service->getFormattedTodaySchedules($teacher->id);

        return view('content.pages.teacher.TeacherDashboard', [
            'teacher' => $teacher,
            'dashboardStats' => $dashboardStats,
            'todaySchedules' => $todaySchedules,
        ]);
    }

    /**
     * Get dashboard statistics (AJAX).
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $this->service->getTeacherByUserId($user->id);

        if ($teacher === null) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
            ], 403);
        }

        $dashboardStats = $this->service->getDashboardStats($user->id);
        $todaySchedules = $this->service->getFormattedTodaySchedules($teacher->id);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics retrieved successfully.',
            'data' => [
                'stats' => $dashboardStats,
                'today_schedules' => $todaySchedules,
            ],
        ]);
    }
}

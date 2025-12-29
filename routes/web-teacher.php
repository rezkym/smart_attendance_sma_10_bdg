<?php

declare(strict_types=1);

use App\Http\Controllers\Teacher\MyScheduleController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Portal Routes
|--------------------------------------------------------------------------
|
| Routes for the Teacher Portal feature. All routes require authentication
| and either the 'teacher' or 'homeroom' role.
|
*/

Route::middleware(['auth', 'role:teacher|homeroom'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        // Dashboard routes - requires teacher-dashboard.view permission
        Route::middleware(['permission:teacher-dashboard.view'])->group(function () {
            Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
            Route::get('dashboard/stats', [TeacherDashboardController::class, 'stats'])->name('dashboard.stats');
        });

        // My Schedules routes - requires teacher-schedules.view-own permission
        Route::middleware(['permission:teacher-schedules.view-own'])->group(function () {
            Route::get('my-schedules', [MyScheduleController::class, 'index'])->name('my-schedules.index');
            Route::get('my-schedules/list', [MyScheduleController::class, 'list'])->name('my-schedules.list');
            Route::get('my-schedules/today', [MyScheduleController::class, 'today'])->name('my-schedules.today');
            Route::get('my-schedules/{schedule}', [MyScheduleController::class, 'show'])->name('my-schedules.show');
        });
    });

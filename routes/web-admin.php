<?php

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\AcademicYearController;
use App\Http\Controllers\admin\PermissionController;
use App\Http\Controllers\admin\ReportController;
use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\admin\SubjectController;
use App\Http\Controllers\admin\TeacherController;
use App\Http\Controllers\admin\ClassroomController;
use App\Http\Controllers\admin\AttendanceController;
use App\Http\Controllers\admin\ScheduleController;
use App\Http\Controllers\admin\StudentController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\IotDeviceController;
use App\Http\Controllers\admin\IotLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard - requires dashboard.view permission
    Route::middleware(['permission:dashboard.view'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('dashboard/attendance-trend', [AdminDashboardController::class, 'attendanceTrend'])->name('dashboard.attendance-trend');
    });

    // Users Management - requires users.view permission
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('users/list', [UserController::class, 'list'])->name('users.list');
        Route::get('users/stats', [UserController::class, 'stats'])->name('users.stats');
        Route::resource('users', UserController::class)->except(['create', 'edit']);
    });

    // Academic Year Management - requires academic-years.view permission
    Route::middleware(['permission:academic-years.view'])->group(function () {
        Route::get('academic-years/list', [AcademicYearController::class, 'list'])->name('academic-years.list');
        Route::post('academic-years/{academic_year}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active');
        Route::resource('academic-years', AcademicYearController::class)->except(['create', 'edit']);
    });

    // Subject Management - requires subjects.view permission
    Route::middleware(['permission:subjects.view'])->group(function () {
        Route::get('subjects/list', [SubjectController::class, 'list'])->name('subjects.list');
        Route::resource('subjects', SubjectController::class)->except(['create', 'edit']);
    });

    // Teacher Management - requires teachers.view permission
    Route::middleware(['permission:teachers.view'])->group(function () {
        Route::get('teachers/list', [TeacherController::class, 'list'])->name('teachers.list');
        Route::get('teachers/available-users', [TeacherController::class, 'availableUsers'])->name('teachers.available-users');
        Route::resource('teachers', TeacherController::class)->except(['create', 'edit']);
    });

    // Student Management - requires students.view permission
    Route::middleware(['permission:students.view'])->group(function () {
        Route::get('students/list', [StudentController::class, 'list'])->name('students.list');
        Route::get('students/available-classrooms', [StudentController::class, 'availableClassrooms'])->name('students.available-classrooms');
        Route::get('students/available-users', [StudentController::class, 'availableUsers'])->name('students.available-users');
        Route::resource('students', StudentController::class)->except(['create', 'edit']);
    });

    // Roles Management - requires roles.view permission
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::get('access-roles/list', [RoleController::class, 'list'])->name('access-roles.list');
        Route::get('access-roles/users', [RoleController::class, 'users'])->name('access-roles.users');
        Route::resource('access-roles', RoleController::class)->except(['create', 'edit']);
    });

    // Classroom Management - requires classrooms.view permission
    Route::middleware(['permission:classrooms.view'])->group(function () {
        Route::get('classrooms/list', [ClassroomController::class, 'list'])->name('classrooms.list');
        Route::get('classrooms/by-academic-year/{academic_year}', [ClassroomController::class, 'getByAcademicYear'])->name('classrooms.by-academic-year');
        Route::get('classrooms/available-homeroom-teachers', [ClassroomController::class, 'availableHomeroomTeachers'])->name('classrooms.available-homeroom-teachers');
        Route::post('classrooms/{classroom}/assign-homeroom', [ClassroomController::class, 'assignHomeroom'])->name('classrooms.assign-homeroom');
        Route::resource('classrooms', ClassroomController::class)->except(['create', 'edit']);
    });

    // Permissions Management - requires permissions.view permission
    Route::middleware(['permission:permissions.view'])->group(function () {
        Route::get('access-permission/list', [PermissionController::class, 'list'])->name('access-permission.list');
        Route::resource('access-permission', PermissionController::class)->only(['index', 'store', 'show', 'destroy']);
    });

    // Schedule Management - requires schedules.view permission
    Route::middleware(['permission:schedules.view'])->group(function () {
        Route::get('schedules/list', [ScheduleController::class, 'list'])->name('schedules.list');
        Route::get('schedules/by-classroom/{classroom}', [ScheduleController::class, 'getByClassroom'])->name('schedules.by-classroom');
        Route::get('schedules/by-teacher/{teacher}', [ScheduleController::class, 'getByTeacher'])->name('schedules.by-teacher');
        Route::get('schedules/dropdown-data', [ScheduleController::class, 'dropdownData'])->name('schedules.dropdown-data');
        Route::get('schedules/available-classrooms', [ScheduleController::class, 'availableClassrooms'])->name('schedules.available-classrooms');
        Route::get('schedules/available-subjects', [ScheduleController::class, 'availableSubjects'])->name('schedules.available-subjects');
        Route::get('schedules/available-teachers', [ScheduleController::class, 'availableTeachers'])->name('schedules.available-teachers');
        Route::get('schedules/available-academic-years', [ScheduleController::class, 'availableAcademicYears'])->name('schedules.available-academic-years');
        Route::get('schedules/days-of-week', [ScheduleController::class, 'daysOfWeek'])->name('schedules.days-of-week');
        Route::resource('schedules', ScheduleController::class)->except(['create', 'edit']);
    });

    // Attendance Management - requires attendances.view permission
    Route::middleware(['permission:attendances.view'])->group(function () {
        Route::get('attendances/list', [AttendanceController::class, 'list'])->name('attendances.list');
        Route::get('attendances/classroom/{classroom}/date/{date}', [AttendanceController::class, 'classroomAttendance'])->name('attendances.classroom');
        Route::get('attendances/available-classrooms', [AttendanceController::class, 'availableClassrooms'])->name('attendances.available-classrooms');
        Route::get('attendances/schedules-by-classroom/{classroom}', [AttendanceController::class, 'schedulesByClassroom'])->name('attendances.schedules-by-classroom');
        Route::get('attendances/statuses', [AttendanceController::class, 'statuses'])->name('attendances.statuses');
        Route::get('attendances/stats', [AttendanceController::class, 'stats'])->name('attendances.stats');
        Route::post('attendances/rfid', [AttendanceController::class, 'recordByRfid'])->name('attendances.rfid');
        Route::post('attendances/bulk', [AttendanceController::class, 'bulkRecord'])->name('attendances.bulk');
        Route::resource('attendances', AttendanceController::class)->except(['create', 'edit']);
    });

    // Reports Management - requires reports.view permission
    Route::middleware(['permission:reports.view'])->group(function () {
        Route::get('reports/attendance', [ReportController::class, 'index'])->name('reports.attendance');
        Route::get('reports/attendance/by-classroom', [ReportController::class, 'attendanceByClassroom'])->name('reports.attendance.by-classroom');
        Route::get('reports/attendance/by-student', [ReportController::class, 'attendanceByStudent'])->name('reports.attendance.by-student');
        Route::get('reports/attendance/summary', [ReportController::class, 'attendanceSummary'])->name('reports.attendance.summary');
        Route::get('reports/attendance/weekly-trend', [ReportController::class, 'weeklyTrend'])->name('reports.attendance.weekly-trend');
        Route::middleware(['permission:reports.export'])->group(function () {
            Route::get('reports/attendance/export', [ReportController::class, 'export'])->name('reports.attendance.export');
        });
    });

    // IoT Device Management - requires iot-devices.view permission
    Route::middleware(['permission:iot-devices.view'])->group(function () {
        Route::get('iot-devices/list', [IotDeviceController::class, 'list'])->name('iot-devices.list');
        Route::get('iot-devices/available-classrooms', [IotDeviceController::class, 'availableClassrooms'])->name('iot-devices.available-classrooms');
        Route::get('iot-devices/statuses', [IotDeviceController::class, 'statuses'])->name('iot-devices.statuses');
        Route::get('iot-devices/stats', [IotDeviceController::class, 'stats'])->name('iot-devices.stats');
        Route::post('iot-devices/{iot_device}/regenerate-key', [IotDeviceController::class, 'regenerateKey'])->name('iot-devices.regenerate-key');
        Route::resource('iot-devices', IotDeviceController::class)->except(['create', 'edit']);
    });

    // IoT Logs Management - requires iot-logs.view permission
    Route::middleware(['permission:iot-logs.view'])->group(function () {
        Route::get('iot-logs/list', [IotLogController::class, 'list'])->name('iot-logs.list');
        Route::get('iot-logs/devices', [IotLogController::class, 'devices'])->name('iot-logs.devices');
        Route::get('iot-logs/log-types', [IotLogController::class, 'logTypes'])->name('iot-logs.log-types');
        Route::get('iot-logs/stats', [IotLogController::class, 'stats'])->name('iot-logs.stats');
        Route::middleware(['permission:iot-logs.export'])->group(function () {
            Route::get('iot-logs/export', [IotLogController::class, 'export'])->name('iot-logs.export');
        });
        Route::resource('iot-logs', IotLogController::class)->only(['index', 'show']);
    });
});



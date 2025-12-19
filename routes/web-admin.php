<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\IotDeviceController;
use App\Http\Controllers\Admin\IotDevicePairingController;
use App\Http\Controllers\Admin\IotLogController;
use App\Http\Controllers\Admin\RfidCardController;
use App\Http\Controllers\Admin\SemesterController;
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

    // Semester Management - requires semesters.view permission
    Route::middleware(['permission:semesters.view'])->group(function () {
        Route::get('semesters/list', [SemesterController::class, 'list'])->name('semesters.list');
        Route::get('semesters/by-academic-year/{academic_year}', [SemesterController::class, 'byAcademicYear'])->name('semesters.by-academic-year');
        Route::post('semesters/{semester}/set-active', [SemesterController::class, 'setActive'])->name('semesters.set-active');
        Route::resource('semesters', SemesterController::class)->except(['create', 'edit']);
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

    // Student Enrollment Management - requires student-enrollments.view permission
    Route::middleware(['permission:student-enrollments.view'])->group(function () {
        Route::get('student-enrollments/list', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'list'])->name('student-enrollments.list');
        Route::get('student-enrollments/by-student/{student}', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'byStudent'])->name('student-enrollments.by-student');
        Route::get('student-enrollments/by-classroom/{classroom}', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'byClassroom'])->name('student-enrollments.by-classroom');
        Route::get('student-enrollments/available-students', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'availableStudents'])->name('student-enrollments.available-students');
        Route::get('student-enrollments/available-classrooms', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'availableClassrooms'])->name('student-enrollments.available-classrooms');
        Route::get('student-enrollments/statuses', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'statuses'])->name('student-enrollments.statuses');
        Route::post('student-enrollments/transfer', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'transfer'])->name('student-enrollments.transfer')->middleware('permission:student-enrollments.transfer');
        Route::post('student-enrollments/graduate', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'graduate'])->name('student-enrollments.graduate')->middleware('permission:student-enrollments.graduate');
        Route::post('student-enrollments/drop', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'drop'])->name('student-enrollments.drop')->middleware('permission:student-enrollments.update');
        Route::resource('student-enrollments', \App\Http\Controllers\Admin\StudentEnrollmentController::class)->except(['create', 'edit']);
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
        
        // Device Pairing Routes
        Route::get('iot-devices/pairing/config', [IotDevicePairingController::class, 'config'])->name('iot-devices.pairing.config');
        Route::post('iot-devices/pairing/complete', [IotDevicePairingController::class, 'complete'])->name('iot-devices.pairing.complete');
        
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

    // RFID Card Management - requires rfid-cards.view permission
    Route::middleware(['permission:rfid-cards.view'])->group(function () {
        Route::get('rfid-cards/list', [RfidCardController::class, 'list'])->name('rfid-cards.list');
        Route::get('rfid-cards/statuses', [RfidCardController::class, 'statuses'])->name('rfid-cards.statuses');
        Route::get('rfid-cards/available-users', [RfidCardController::class, 'availableUsers'])->name('rfid-cards.available-users');
        Route::get('rfid-cards/stats', [RfidCardController::class, 'stats'])->name('rfid-cards.stats');
        Route::get('rfid-cards/card-by-user/{user}', [RfidCardController::class, 'cardByUser'])->name('rfid-cards.card-by-user');
        Route::post('rfid-cards/{rfid_card}/block', [RfidCardController::class, 'block'])->name('rfid-cards.block')->middleware('permission:rfid-cards.block');
        Route::post('rfid-cards/{rfid_card}/unblock', [RfidCardController::class, 'unblock'])->name('rfid-cards.unblock')->middleware('permission:rfid-cards.block');
        
        // Quick Scan Registration routes
        Route::get('rfid-cards/available-devices', [RfidCardController::class, 'availableDevices'])->name('rfid-cards.available-devices');
        Route::get('rfid-cards/available-users-without-card', [RfidCardController::class, 'availableUsersWithoutCard'])->name('rfid-cards.available-users-without-card');
        Route::post('rfid-cards/start-registration', [RfidCardController::class, 'startRegistrationMode'])->name('rfid-cards.start-registration')->middleware('permission:rfid-cards.create');
        Route::post('rfid-cards/check-registration', [RfidCardController::class, 'checkRegistrationStatus'])->name('rfid-cards.check-registration');
        Route::post('rfid-cards/cancel-registration', [RfidCardController::class, 'cancelRegistration'])->name('rfid-cards.cancel-registration');
        
        Route::resource('rfid-cards', RfidCardController::class)->except(['create', 'edit']);
    });

    // Activity Log Management - requires activity-logs.view permission
    Route::middleware(['permission:activity-logs.view'])->group(function () {
        Route::get('activity-logs/list', [\App\Http\Controllers\Admin\ActivityLogController::class, 'list'])->name('activity-logs.list');
        Route::get('activity-logs/log-names', [\App\Http\Controllers\Admin\ActivityLogController::class, 'logNames'])->name('activity-logs.log-names');
        Route::get('activity-logs/event-types', [\App\Http\Controllers\Admin\ActivityLogController::class, 'eventTypes'])->name('activity-logs.event-types');
        Route::resource('activity-logs', \App\Http\Controllers\Admin\ActivityLogController::class)->only(['index', 'show']);
    });
});


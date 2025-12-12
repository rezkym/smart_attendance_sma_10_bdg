<?php

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\AcademicYearController;
use App\Http\Controllers\admin\PermissionController;
use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\admin\SubjectController;
use App\Http\Controllers\admin\TeacherController;
use App\Http\Controllers\admin\ClassroomController;
use App\Http\Controllers\admin\StudentController;
use App\Http\Controllers\admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard - requires dashboard.view permission
    Route::middleware(['permission:dashboard.view'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
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
        Route::resource('classrooms', ClassroomController::class)->except(['create', 'edit']);
    });

    // Permissions Management - requires permissions.view permission
    Route::middleware(['permission:permissions.view'])->group(function () {
        Route::get('access-permission/list', [PermissionController::class, 'list'])->name('access-permission.list');
        Route::resource('access-permission', PermissionController::class)->only(['index', 'store', 'show', 'destroy']);
    });
});

<?php

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\AcademicYearController;
use App\Http\Controllers\admin\PermissionController;
use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\admin\SubjectController;
use App\Http\Controllers\admin\TeacherController;
use App\Http\Controllers\admin\ClassroomController;
use App\Http\Controllers\admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users Management - Custom routes before resource
    Route::get('users/list', [UserController::class, 'list'])->name('users.list');
    Route::get('users/stats', [UserController::class, 'stats'])->name('users.stats');
    Route::resource('users', UserController::class)->except(['create', 'edit']);

    // Academic Year Management - Custom routes before resource
    Route::get('academic-years/list', [AcademicYearController::class, 'list'])->name('academic-years.list');
    Route::post('academic-years/{academic_year}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active');
    Route::resource('academic-years', AcademicYearController::class)->except(['create', 'edit']);

    // Subject Management - Custom routes before resource
    Route::get('subjects/list', [SubjectController::class, 'list'])->name('subjects.list');
    Route::resource('subjects', SubjectController::class)->except(['create', 'edit']);

    // Teacher Management - Custom routes before resource
    Route::get('teachers/list', [TeacherController::class, 'list'])->name('teachers.list');
    Route::get('teachers/available-users', [TeacherController::class, 'availableUsers'])->name('teachers.available-users');
    Route::resource('teachers', TeacherController::class)->except(['create', 'edit']);

    // Roles Management - Custom routes before resource
    Route::get('access-roles/list', [RoleController::class, 'list'])->name('access-roles.list');
    Route::get('access-roles/users', [RoleController::class, 'users'])->name('access-roles.users');
    Route::resource('access-roles', RoleController::class)->except(['create', 'edit']);

    // Classroom Management - Custom routes before resource
    Route::get('classrooms/list', [ClassroomController::class, 'list'])->name('classrooms.list');
    Route::get('classrooms/by-academic-year/{academic_year}', [ClassroomController::class, 'getByAcademicYear'])->name('classrooms.by-academic-year');
    Route::resource('classrooms', ClassroomController::class)->except(['create', 'edit']);

    // Permissions Management - Custom routes before resource
    Route::get('access-permission/list', [PermissionController::class, 'list'])->name('access-permission.list');
    Route::resource('access-permission', PermissionController::class)->only(['index', 'store', 'show', 'destroy']);
});

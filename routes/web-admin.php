<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\StudentController;
use App\Http\Controllers\admin\TeacherController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/available-users', [StudentController::class, 'getAvailableUsers'])->name('students.available-users');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::put('/students/{student}/rfid', [StudentController::class, 'assignRfid'])->name('students.assign-rfid');
    Route::put('/students/{student}/classroom', [StudentController::class, 'assignClassroom'])->name('students.assign-classroom');

    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/available-users', [TeacherController::class, 'getAvailableUsers'])->name('teachers.available-users');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    Route::put('/teachers/{teacher}/subjects', [TeacherController::class, 'assignSubjects'])->name('teachers.assign-subjects');
    Route::put('/teachers/{teacher}/classroom', [TeacherController::class, 'assignClassroom'])->name('teachers.assign-classroom');
});

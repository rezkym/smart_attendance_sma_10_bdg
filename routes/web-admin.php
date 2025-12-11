<?php

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\PermissionController;
use App\Http\Controllers\admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Roles Management - Custom routes before resource
    Route::get('access-roles/list', [RoleController::class, 'list'])->name('access-roles.list');
    Route::get('access-roles/users', [RoleController::class, 'users'])->name('access-roles.users');
    Route::resource('access-roles', RoleController::class)->except(['create', 'edit']);

    // Permissions Management - Custom routes before resource
    Route::get('access-permission/list', [PermissionController::class, 'list'])->name('access-permission.list');
    Route::resource('access-permission', PermissionController::class)->only(['index', 'store', 'show', 'destroy']);
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\AdminDashboardController;
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
});

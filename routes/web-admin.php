<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\pages\admin\AdminDashboard;

Route::get('/admin', [AdminDashboard::class, 'index'])->name('pages-home');
<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Middleware\AuthenticateIotDevice;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// IoT Device API Routes - requires X-API-Key header authentication
Route::middleware([AuthenticateIotDevice::class, 'throttle:60,1'])->group(function () {
    Route::post('/attendance', [AttendanceController::class, 'store']);
});

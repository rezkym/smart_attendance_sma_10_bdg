<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Admin\RfidCardController;
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

// RFID Card Registration Callback (called by ESP32 during Quick Scan)
// No auth required - session ID acts as temporary token
Route::post('/rfid-cards/receive-scan/{sessionId}', [RfidCardController::class, 'receiveScannedCard'])
    ->name('api.rfid-cards.receive-scan');

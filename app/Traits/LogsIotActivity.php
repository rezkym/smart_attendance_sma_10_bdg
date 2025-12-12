<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\IotLogService;
use Illuminate\Http\Request;

trait LogsIotActivity
{
    /**
     * Log IoT API request and response.
     */
    protected function logIotRequest(Request $request, mixed $response, int $durationMs): void
    {
        $iotLogService = app(IotLogService::class);
        $iotLogService->logFromRequestResponse($request, $response, $durationMs);
    }

    /**
     * Log IoT API error.
     */
    protected function logIotError(Request $request, \Throwable $exception, int $durationMs): void
    {
        $device = $request->attributes->get('iot_device');

        if ($device === null) {
            return;
        }

        $iotLogService = app(IotLogService::class);
        $iotLogService->logError(
            deviceId: $device->id,
            endpoint: $request->path(),
            method: $request->method(),
            requestPayload: $request->all(),
            errorMessage: $exception->getMessage(),
            ipAddress: $request->ip() ?? 'unknown',
            userAgent: $request->userAgent(),
            durationMs: $durationMs
        );
    }

    /**
     * Get duration in milliseconds from start time.
     */
    protected function getDurationMs(float $startTime): int
    {
        return (int) round((microtime(true) - $startTime) * 1000);
    }
}

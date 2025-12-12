<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\IotDeviceStatus;
use App\Models\IotDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateIotDevice
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (blank($apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'API key required',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $device = IotDevice::where('api_key', $apiKey)
            ->where('status', IotDeviceStatus::ACTIVE)
            ->first();

        if ($device === null) {
            // Device not found or not active
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Update last seen timestamp and IP address
        $device->update([
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        // Attach device to request for use in controllers and logging
        $request->attributes->set('iot_device', $device);

        return $next($request);
    }
}

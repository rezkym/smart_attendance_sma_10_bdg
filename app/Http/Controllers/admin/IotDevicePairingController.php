<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IotDevice\CompletePairingRequest;
use App\Services\IotDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class IotDevicePairingController extends Controller
{
    public function __construct(
        protected IotDeviceService $iotDeviceService
    ) {}

    /**
     * Get server configuration for pairing.
     * Returns the host, port, and path that the ESP32 should use.
     */
    public function config(): JsonResponse
    {
        $config = $this->iotDeviceService->getServerConfig();

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }

    /**
     * Complete the pairing process.
     * Creates or updates the device record after successful ESP32 pairing.
     */
    public function complete(CompletePairingRequest $request): JsonResponse
    {
        try {
            $device = $this->iotDeviceService->findOrCreateDeviceByCode(
                $request->validated('device_code'),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Device paired successfully.',
                'data' => [
                    'device' => $device,
                    'api_key' => $device->wasRecentlyCreated ? $device->api_key : null,
                ],
            ], $device->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}

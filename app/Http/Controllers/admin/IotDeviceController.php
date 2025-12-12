<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IotDevice\StoreIotDeviceRequest;
use App\Http\Requests\IotDevice\UpdateIotDeviceRequest;
use App\Models\IotDevice;
use App\Services\IotDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class IotDeviceController extends Controller
{
    public function __construct(
        protected IotDeviceService $iotDeviceService
    ) {}

    /**
     * Display IoT devices listing page.
     */
    public function index(): View
    {
        return view('content.pages.admin.iot-devices');
    }

    /**
     * Get IoT devices for DataTables.
     */
    public function list(): JsonResponse
    {
        $query = $this->iotDeviceService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('classroom_name', function (IotDevice $device) {
                return $device->classroom?->name ?? '-';
            })
            ->addColumn('status_badge', function (IotDevice $device) {
                $color = $device->status->badgeColor();
                $label = $device->status->label();
                return "<span class=\"badge bg-{$color}\">{$label}</span>";
            })
            ->addColumn('last_seen_formatted', function (IotDevice $device) {
                if ($device->last_seen_at === null) {
                    return '<span class="text-muted">Never</span>';
                }
                return $device->last_seen_at->diffForHumans();
            })
            ->addColumn('masked_api_key', function (IotDevice $device) {
                return $device->masked_api_key;
            })
            ->addColumn('actions', function (IotDevice $device) {
                return $device->id;
            })
            ->rawColumns(['status_badge', 'last_seen_formatted'])
            ->toJson();
    }

    /**
     * Store a new IoT device.
     */
    public function store(StoreIotDeviceRequest $request): JsonResponse
    {
        try {
            $device = $this->iotDeviceService->createDevice($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IoT device created successfully.',
                'data' => [
                    'device' => $device,
                    'api_key' => $device->api_key, // Return full API key only on creation
                ],
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get IoT device details.
     */
    public function show(int $iotDevice): JsonResponse
    {
        $device = $this->iotDeviceService->getDeviceById($iotDevice);

        if ($device === null) {
            return response()->json([
                'success' => false,
                'message' => 'IoT device not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $device,
        ]);
    }

    /**
     * Update IoT device.
     */
    public function update(UpdateIotDeviceRequest $request, int $iotDevice): JsonResponse
    {
        try {
            $device = $this->iotDeviceService->updateDevice($iotDevice, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IoT device updated successfully.',
                'data' => $device,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Delete IoT device.
     */
    public function destroy(int $iotDevice): JsonResponse
    {
        try {
            $this->iotDeviceService->deleteDevice($iotDevice);

            return response()->json([
                'success' => true,
                'message' => 'IoT device deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Regenerate API key for a device.
     */
    public function regenerateKey(int $iotDevice): JsonResponse
    {
        try {
            $device = $this->iotDeviceService->regenerateApiKey($iotDevice);

            return response()->json([
                'success' => true,
                'message' => 'API key regenerated successfully.',
                'data' => [
                    'api_key' => $device->api_key, // Return new full API key
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get available classrooms for device assignment.
     */
    public function availableClassrooms(): JsonResponse
    {
        $classrooms = $this->iotDeviceService->getAvailableClassrooms();

        return response()->json([
            'success' => true,
            'data' => $classrooms,
        ]);
    }

    /**
     * Get available device statuses.
     */
    public function statuses(): JsonResponse
    {
        $statuses = $this->iotDeviceService->getStatuses();

        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    /**
     * Get statistics for dashboard.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->iotDeviceService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

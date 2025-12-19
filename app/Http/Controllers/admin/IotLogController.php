<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\IotLogType;
use App\Http\Controllers\Controller;
use App\Models\IotLog;
use App\Services\IotLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class IotLogController extends Controller
{
    public function __construct(
        protected IotLogService $iotLogService
    ) {}

    /**
     * Display IoT logs listing page.
     */
    public function index(): View
    {
        return view('content.pages.admin.iot-logs');
    }

    /**
     * Get IoT logs for DataTables.
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->iotLogService->getDataTableQuery();

        // Apply filters
        if ($request->filled('device_id')) {
            $query->where('iot_device_id', $request->input('device_id'));
        }

        if ($request->filled('log_type')) {
            $query->where('log_type', $request->input('log_type'));
        }

        if ($request->filled('response_code')) {
            $query->where('response_code', $request->input('response_code'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return DataTables::eloquent($query)
            ->addColumn('device_name', function (IotLog $log) {
                return $log->device?->name ?? '-';
            })
            ->addColumn('log_type_badge', function (IotLog $log) {
                $color = $log->log_type->badgeColor();
                $label = $log->log_type->label();
                return "<span class=\"badge bg-{$color}\">{$label}</span>";
            })
            ->addColumn('response_code_badge', function (IotLog $log) {
                if ($log->response_code === null) {
                    return '<span class="text-muted">-</span>';
                }

                $color = 'success';
                if ($log->response_code >= 400 && $log->response_code < 500) {
                    $color = 'warning';
                } elseif ($log->response_code >= 500) {
                    $color = 'danger';
                }

                return "<span class=\"badge bg-{$color}\">{$log->response_code}</span>";
            })
            ->addColumn('duration_formatted', function (IotLog $log) {
                return $log->formatted_duration;
            })
            ->addColumn('created_at_formatted', function (IotLog $log) {
                return $log->created_at?->format('Y-m-d H:i:s') ?? '-';
            })
            ->addColumn('actions', function (IotLog $log) {
                return $log->id;
            })
            ->rawColumns(['log_type_badge', 'response_code_badge'])
            ->toJson();
    }

    /**
     * Get IoT log details.
     */
    public function show(int $iotLog): JsonResponse
    {
        $log = $this->iotLogService->getLogById($iotLog);

        if ($log === null) {
            return response()->json([
                'success' => false,
                'message' => 'IoT log not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * Get available devices for filter dropdown.
     */
    public function devices(): JsonResponse
    {
        $devices = $this->iotLogService->getAllDevices();

        return response()->json([
            'success' => true,
            'data' => $devices->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'device_code' => $device->device_code,
                ];
            }),
        ]);
    }

    /**
     * Get available log types for filter dropdown.
     */
    public function logTypes(): JsonResponse
    {
        $types = $this->iotLogService->getLogTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Export IoT logs to CSV.
     */
    public function export(Request $request): Response
    {
        $request->validate([
            'device_id' => 'nullable|integer|exists:iot_devices,id',
            'log_type' => 'nullable|in:' . implode(',', IotLogType::values()),
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = $this->iotLogService->getDataTableQuery();

        // Apply filters
        if ($request->filled('device_id')) {
            $query->where('iot_device_id', $request->input('device_id'));
        }

        if ($request->filled('log_type')) {
            $query->where('log_type', $request->input('log_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->limit(10000)->get();

        $csvContent = "ID,Device,Log Type,Endpoint,Method,Response Code,Duration (ms),IP Address,Created At\n";

        foreach ($logs as $log) {
            $csvContent .= implode(',', [
                $log->id,
                '"' . ($log->device?->name ?? '-') . '"',
                $log->log_type->value,
                '"' . $log->endpoint . '"',
                $log->method,
                $log->response_code ?? '-',
                $log->duration_ms ?? '-',
                $log->ip_address ?? '-',
                $log->created_at?->format('Y-m-d H:i:s') ?? '-',
            ]) . "\n";
        }

        $filename = 'iot-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get statistics for dashboard.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->iotLogService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

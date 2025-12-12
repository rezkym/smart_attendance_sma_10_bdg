<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RecordIoTAttendanceRequest;
use App\Services\AttendanceService;
use App\Traits\LogsIotActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    use LogsIotActivity;

    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Record attendance from IoT device.
     */
    public function store(RecordIoTAttendanceRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $result = $this->attendanceService->recordIoTAttendance($request->validated('card_uid'));

            // Determine HTTP status based on result status
            $httpStatus = match ($result['status'] ?? 'success') {
                'success' => Response::HTTP_OK,
                'warning' => Response::HTTP_OK,
                'info' => Response::HTTP_OK,
                default => Response::HTTP_OK,
            };

            $response = response()->json($result, $httpStatus);

            // Log successful request
            $this->logIotRequest($request, $response, $this->getDurationMs($startTime));

            return $response;
        } catch (\InvalidArgumentException $e) {
            // Business logic errors (student not found, no schedule, etc.)
            $response = response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

            // Log error request
            $this->logIotRequest($request, $response, $this->getDurationMs($startTime));

            return $response;
        } catch (\Exception $e) {
            // Log internal errors, but don't expose to client
            Log::error('IoT Attendance Error', [
                'card_uid' => $request->validated('card_uid'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Log error to IoT logs
            $this->logIotError($request, $e, $this->getDurationMs($startTime));

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}



<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IotLogType;
use App\Models\IotLog;
use App\Repositories\Contracts\IotDeviceRepositoryInterface;
use App\Repositories\Contracts\IotLogRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IotLogService
{
    public function __construct(
        protected IotLogRepositoryInterface $iotLogRepository,
        protected IotDeviceRepositoryInterface $iotDeviceRepository
    ) {}

    /**
     * Get logs by device ID.
     *
     * @return Collection<int, IotLog>
     */
    public function getLogsByDevice(int $deviceId): Collection
    {
        return $this->iotLogRepository->getByDevice($deviceId);
    }

    /**
     * Get log by ID.
     */
    public function getLogById(int $logId): ?IotLog
    {
        return $this->iotLogRepository->findById($logId);
    }

    /**
     * Log an IoT API request.
     *
     * @param array<string, mixed>|null $requestPayload
     * @param array<string, mixed>|null $responsePayload
     */
    public function logRequest(
        int $deviceId,
        string $endpoint,
        string $method,
        ?array $requestPayload,
        ?array $responsePayload,
        int $responseCode,
        string $ipAddress,
        ?string $userAgent,
        int $durationMs
    ): IotLog {
        // Sanitize request payload to mask sensitive data
        $sanitizedRequestPayload = $requestPayload !== null
            ? $this->sanitizePayload($requestPayload)
            : null;

        return $this->iotLogRepository->create([
            'iot_device_id' => $deviceId,
            'log_type' => $responseCode >= 400 ? IotLogType::ERROR->value : IotLogType::REQUEST->value,
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => $sanitizedRequestPayload,
            'response_payload' => $responsePayload,
            'response_code' => $responseCode,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Log an error from IoT request.
     */
    public function logError(
        int $deviceId,
        string $endpoint,
        string $method,
        ?array $requestPayload,
        string $errorMessage,
        string $ipAddress,
        ?string $userAgent,
        int $durationMs
    ): IotLog {
        // Sanitize request payload
        $sanitizedRequestPayload = $requestPayload !== null
            ? $this->sanitizePayload($requestPayload)
            : null;

        return $this->iotLogRepository->create([
            'iot_device_id' => $deviceId,
            'log_type' => IotLogType::ERROR->value,
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => $sanitizedRequestPayload,
            'response_payload' => null,
            'response_code' => 500,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'duration_ms' => $durationMs,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Create log from Request and Response objects.
     */
    public function logFromRequestResponse(
        Request $request,
        mixed $response,
        int $durationMs
    ): ?IotLog {
        $device = $request->attributes->get('iot_device');

        if ($device === null) {
            return null;
        }

        $responsePayload = null;
        $responseCode = 200;

        if ($response instanceof JsonResponse) {
            $responsePayload = $response->getData(true);
            $responseCode = $response->getStatusCode();
        } elseif (method_exists($response, 'getStatusCode')) {
            $responseCode = $response->getStatusCode();
        }

        return $this->logRequest(
            deviceId: $device->id,
            endpoint: $request->path(),
            method: $request->method(),
            requestPayload: $request->all(),
            responsePayload: $responsePayload,
            responseCode: $responseCode,
            ipAddress: $request->ip() ?? 'unknown',
            userAgent: $request->userAgent(),
            durationMs: $durationMs
        );
    }

    /**
     * Clean up old logs.
     */
    public function cleanupOldLogs(int $days = 90): int
    {
        return $this->iotLogRepository->deleteOlderThan($days);
    }

    /**
     * Get logs by date range.
     *
     * @return Collection<int, IotLog>
     */
    public function getLogsByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->iotLogRepository->getByDateRange($startDate, $endDate);
    }

    /**
     * Get all log types.
     *
     * @return array<string, string>
     */
    public function getLogTypes(): array
    {
        return IotLogType::toArray();
    }

    /**
     * Get all devices for filter dropdown.
     *
     * @return Collection<int, \App\Models\IotDevice>
     */
    public function getAllDevices(): Collection
    {
        return $this->iotDeviceRepository->getAll();
    }

    /**
     * Get statistics for dashboard.
     *
     * @return array{total: int, errors: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->iotLogRepository->getTotalCount(),
            'errors' => $this->iotLogRepository->getErrorCount(),
        ];
    }

    /**
     * Get DataTables query builder.
     *
     * @return Builder<IotLog>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->iotLogRepository->getDataTableQuery();
    }

    /**
     * Sanitize payload to mask sensitive data.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitiveFields = ['card_uid', 'rfid', 'password', 'api_key', 'token'];

        foreach ($sensitiveFields as $field) {
            if (isset($payload[$field]) && is_string($payload[$field])) {
                $value = $payload[$field];
                if (strlen($value) > 4) {
                    $payload[$field] = substr($value, 0, 4) . str_repeat('*', strlen($value) - 4);
                } else {
                    $payload[$field] = str_repeat('*', strlen($value));
                }
            }
        }

        return $payload;
    }
}

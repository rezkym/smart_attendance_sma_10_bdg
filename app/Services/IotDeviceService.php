<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IotDeviceStatus;
use App\Models\IotDevice;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\IotDeviceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class IotDeviceService
{
    public function __construct(
        protected IotDeviceRepositoryInterface $iotDeviceRepository,
        protected ClassroomRepositoryInterface $classroomRepository
    ) {}

    /**
     * Get all IoT devices.
     *
     * @return Collection<int, IotDevice>
     */
    public function getAllDevices(): Collection
    {
        return $this->iotDeviceRepository->getAll();
    }

    /**
     * Get all active IoT devices.
     *
     * @return Collection<int, IotDevice>
     */
    public function getAllActiveDevices(): Collection
    {
        return $this->iotDeviceRepository->getAllActive();
    }

    /**
     * Get device by ID.
     */
    public function getDeviceById(int $deviceId): ?IotDevice
    {
        return $this->iotDeviceRepository->findById($deviceId);
    }

    /**
     * Get device by API key.
     */
    public function getDeviceByApiKey(string $apiKey): ?IotDevice
    {
        return $this->iotDeviceRepository->findByApiKey($apiKey);
    }

    /**
     * Get device by device code.
     */
    public function getDeviceByDeviceCode(string $deviceCode): ?IotDevice
    {
        return $this->iotDeviceRepository->findByDeviceCode($deviceCode);
    }

    /**
     * Create a new IoT device.
     *
     * @param array{name: string, device_code: string, description?: string|null, location?: string|null, classroom_id?: int|null, status?: string, firmware_version?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function createDevice(array $data): IotDevice
    {
        // Check device code uniqueness
        if ($this->iotDeviceRepository->findByDeviceCode($data['device_code']) !== null) {
            throw new \InvalidArgumentException('Device code already registered.');
        }

        // Generate API key
        $data['api_key'] = $this->generateApiKey();

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = IotDeviceStatus::ACTIVE->value;
        }

        return $this->iotDeviceRepository->create($data);
    }

    /**
     * Update IoT device.
     *
     * @param array{name?: string, device_code?: string, description?: string|null, location?: string|null, classroom_id?: int|null, status?: string, firmware_version?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateDevice(int $deviceId, array $data): IotDevice
    {
        $device = $this->iotDeviceRepository->findById($deviceId);

        if ($device === null) {
            throw new \InvalidArgumentException("Device with ID {$deviceId} not found.");
        }

        // Check device code uniqueness (ignore current)
        if (isset($data['device_code']) && $data['device_code'] !== $device->device_code) {
            $existingDevice = $this->iotDeviceRepository->findByDeviceCode($data['device_code']);
            if ($existingDevice !== null) {
                throw new \InvalidArgumentException('Device code already registered by another device.');
            }
        }

        return $this->iotDeviceRepository->update($device, $data);
    }

    /**
     * Delete IoT device.
     *
     * @throws \InvalidArgumentException
     */
    public function deleteDevice(int $deviceId): bool
    {
        $device = $this->iotDeviceRepository->findById($deviceId);

        if ($device === null) {
            throw new \InvalidArgumentException("Device with ID {$deviceId} not found.");
        }

        return $this->iotDeviceRepository->delete($device);
    }

    /**
     * Regenerate API key for a device.
     *
     * @throws \InvalidArgumentException
     */
    public function regenerateApiKey(int $deviceId): IotDevice
    {
        $device = $this->iotDeviceRepository->findById($deviceId);

        if ($device === null) {
            throw new \InvalidArgumentException("Device with ID {$deviceId} not found.");
        }

        $newApiKey = $this->generateApiKey();

        return $this->iotDeviceRepository->update($device, ['api_key' => $newApiKey]);
    }

    /**
     * Generate a secure API key.
     */
    public function generateApiKey(): string
    {
        return Str::random(64);
    }

    /**
     * Update last seen timestamp for a device.
     */
    public function updateLastSeen(IotDevice $device, string $ipAddress): void
    {
        $this->iotDeviceRepository->updateLastSeen($device, $ipAddress);
    }

    /**
     * Get available classrooms for device assignment.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom>
     */
    public function getAvailableClassrooms(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->classroomRepository->getAllActive();
    }

    /**
     * Get all device statuses.
     *
     * @return array<string, string>
     */
    public function getStatuses(): array
    {
        return IotDeviceStatus::toArray();
    }

    /**
     * Get statistics for dashboard.
     *
     * @return array{total: int, active: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->iotDeviceRepository->getTotalCount(),
            'active' => $this->iotDeviceRepository->getActiveCount(),
        ];
    }

    /**
     * Get DataTables query builder.
     *
     * @return Builder<IotDevice>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->iotDeviceRepository->getDataTableQuery();
    }
}

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

    /**
     * Get server configuration for device pairing.
     *
     * @return array{host: string, port: int, path: string, api_key: string}
     */
    public function getServerConfig(): array
    {
        // Generate a new API key for the device being paired
        $apiKey = $this->generateApiKey();

        // Get the actual local IP address of this machine (not 127.0.0.1)
        $host = $this->getLocalIpAddress();
        $port = (int) (request()->getPort() ?? 80);

        return [
            'host' => $host,
            'port' => $port,
            'path' => '/api/attendance',
            'api_key' => $apiKey,
        ];
    }

    /**
     * Get the local network IP address of this machine.
     * Falls back to config value or request host if detection fails.
     */
    private function getLocalIpAddress(): string
    {
        // Try to get local IP using hostname
        $hostname = gethostname();
        if ($hostname !== false) {
            $ip = gethostbyname($hostname);
            // Check if it's a valid non-localhost, non-0.0.0.0 IP
            if ($ip !== $hostname && $this->isValidLanIp($ip)) {
                return $ip;
            }
        }

        // Fallback: Try to detect via network interface (Mac: WiFi)
        $output = shell_exec("ipconfig getifaddr en0 2>/dev/null");
        if ($output !== null) {
            $ip = trim($output);
            if ($this->isValidLanIp($ip)) {
                return $ip;
            }
        }

        // Fallback: Try to detect via network interface (Mac: Ethernet)
        $output = shell_exec("ipconfig getifaddr en1 2>/dev/null");
        if ($output !== null) {
            $ip = trim($output);
            if ($this->isValidLanIp($ip)) {
                return $ip;
            }
        }

        // Fallback: Try Linux command
        $output = shell_exec("hostname -I 2>/dev/null | awk '{print $1}'");
        if ($output !== null) {
            $ip = trim($output);
            if ($this->isValidLanIp($ip)) {
                return $ip;
            }
        }

        // Fallback: Check config for a manually set IP
        $configuredIp = config('iot.server_ip');
        if ($configuredIp && filter_var($configuredIp, FILTER_VALIDATE_IP)) {
            return $configuredIp;
        }

        // Last resort: return request host (but NOT if it's 0.0.0.0)
        $requestHost = request()->getHost();
        if ($this->isValidLanIp($requestHost)) {
            return $requestHost;
        }

        // If all else fails, throw an exception so user knows to configure manually
        throw new \RuntimeException('Cannot detect local IP address. Please set IOT_SERVER_IP in your .env file.');
    }

    /**
     * Check if IP is a valid LAN IP (not localhost, not 0.0.0.0).
     */
    private function isValidLanIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        // Exclude localhost and binding addresses
        $invalidIps = ['127.0.0.1', '0.0.0.0', 'localhost'];
        return !in_array($ip, $invalidIps, true);
    }

    /**
     * Find existing device by code or create a new one.
     *
     * @param array{device_code: string, ip_address: string, api_key: string, name?: string|null, location?: string|null, firmware_version?: string|null} $data
     */
    public function findOrCreateDeviceByCode(string $deviceCode, array $data): IotDevice
    {
        $existingDevice = $this->iotDeviceRepository->findByDeviceCode($deviceCode);

        if ($existingDevice !== null) {
            // Update existing device with new IP, API key, and info
            return $this->iotDeviceRepository->update($existingDevice, [
                'ip_address' => $data['ip_address'],
                'api_key' => $data['api_key'], // Update API key on re-pair
                'name' => $data['name'] ?? $existingDevice->name,
                'location' => $data['location'] ?? $existingDevice->location,
                'firmware_version' => $data['firmware_version'] ?? $existingDevice->firmware_version,
                'last_seen_at' => now(),
                'status' => IotDeviceStatus::ACTIVE->value,
            ]);
        }

        // Create new device with the provided API key
        $newData = [
            'device_code' => $deviceCode,
            'name' => $data['name'] ?? 'IoT Device ' . $deviceCode,
            'ip_address' => $data['ip_address'],
            'location' => $data['location'] ?? null,
            'firmware_version' => $data['firmware_version'] ?? null,
            'api_key' => $data['api_key'], // Use the API key that was sent to ESP32
            'status' => IotDeviceStatus::ACTIVE->value,
            'last_seen_at' => now(),
        ];

        return $this->iotDeviceRepository->create($newData);
    }
}


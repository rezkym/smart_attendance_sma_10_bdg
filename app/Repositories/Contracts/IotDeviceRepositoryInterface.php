<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\IotDevice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface IotDeviceRepositoryInterface
{
    /**
     * Get all IoT devices.
     *
     * @return Collection<int, IotDevice>
     */
    public function getAll(): Collection;

    /**
     * Get all active IoT devices.
     *
     * @return Collection<int, IotDevice>
     */
    public function getAllActive(): Collection;

    /**
     * Find device by ID.
     */
    public function findById(int $deviceId): ?IotDevice;

    /**
     * Find device by API key.
     */
    public function findByApiKey(string $apiKey): ?IotDevice;

    /**
     * Find device by device code.
     */
    public function findByDeviceCode(string $deviceCode): ?IotDevice;

    /**
     * Create a new device.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): IotDevice;

    /**
     * Update a device.
     *
     * @param array<string, mixed> $data
     */
    public function update(IotDevice $device, array $data): IotDevice;

    /**
     * Delete a device.
     */
    public function delete(IotDevice $device): bool;

    /**
     * Update last seen timestamp and IP address.
     */
    public function updateLastSeen(IotDevice $device, string $ipAddress): void;

    /**
     * Get DataTables query builder.
     *
     * @return Builder<IotDevice>
     */
    public function getDataTableQuery(): Builder;

    /**
     * Get total count of devices.
     */
    public function getTotalCount(): int;

    /**
     * Get count of active devices.
     */
    public function getActiveCount(): int;
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\IotDeviceStatus;
use App\Models\IotDevice;
use App\Repositories\Contracts\IotDeviceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class IotDeviceRepository implements IotDeviceRepositoryInterface
{
    public function __construct(
        protected IotDevice $model
    ) {}

    /**
     * @return Collection<int, IotDevice>
     */
    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with('classroom')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, IotDevice>
     */
    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->with('classroom')
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function findById(int $deviceId): ?IotDevice
    {
        return $this->model->newQuery()
            ->with('classroom')
            ->find($deviceId);
    }

    public function findByApiKey(string $apiKey): ?IotDevice
    {
        return $this->model->newQuery()
            ->with('classroom')
            ->where('api_key', $apiKey)
            ->first();
    }

    public function findByDeviceCode(string $deviceCode): ?IotDevice
    {
        return $this->model->newQuery()
            ->with('classroom')
            ->where('device_code', $deviceCode)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): IotDevice
    {
        $device = $this->model->newQuery()->create($data);

        return $device->load('classroom');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(IotDevice $device, array $data): IotDevice
    {
        $device->update($data);

        return $device->fresh()->load('classroom');
    }

    public function delete(IotDevice $device): bool
    {
        return (bool) $device->delete();
    }

    public function updateLastSeen(IotDevice $device, string $ipAddress): void
    {
        $device->update([
            'last_seen_at' => now(),
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * @return Builder<IotDevice>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('classroom')
            ->orderBy('name');
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getActiveCount(): int
    {
        return $this->model->newQuery()
            ->where('status', IotDeviceStatus::ACTIVE)
            ->count();
    }
}

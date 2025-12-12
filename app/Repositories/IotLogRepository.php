<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\IotLogType;
use App\Models\IotLog;
use App\Repositories\Contracts\IotLogRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class IotLogRepository implements IotLogRepositoryInterface
{
    public function __construct(
        protected IotLog $model
    ) {}

    /**
     * @return Collection<int, IotLog>
     */
    public function getByDevice(int $deviceId): Collection
    {
        return $this->model->newQuery()
            ->with('device')
            ->byDevice($deviceId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById(int $logId): ?IotLog
    {
        return $this->model->newQuery()
            ->with('device')
            ->find($logId);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): IotLog
    {
        // Set created_at if not provided
        if (!isset($data['created_at'])) {
            $data['created_at'] = now();
        }

        return $this->model->newQuery()->create($data);
    }

    public function deleteOlderThan(int $days): int
    {
        $cutoffDate = Carbon::now()->subDays($days);

        return $this->model->newQuery()
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * @return Builder<IotLog>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('device')
            ->orderByDesc('created_at');
    }

    /**
     * @return Collection<int, IotLog>
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->newQuery()
            ->with('device')
            ->byDateRange($startDate, $endDate)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getErrorCount(): int
    {
        return $this->model->newQuery()
            ->where('log_type', IotLogType::ERROR)
            ->count();
    }
}

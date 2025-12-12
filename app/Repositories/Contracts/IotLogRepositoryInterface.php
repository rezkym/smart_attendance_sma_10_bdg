<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\IotLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface IotLogRepositoryInterface
{
    /**
     * Get logs by device ID.
     *
     * @return Collection<int, IotLog>
     */
    public function getByDevice(int $deviceId): Collection;

    /**
     * Find log by ID.
     */
    public function findById(int $logId): ?IotLog;

    /**
     * Create a new log entry.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): IotLog;

    /**
     * Delete logs older than specified days.
     */
    public function deleteOlderThan(int $days): int;

    /**
     * Get DataTables query builder.
     *
     * @return Builder<IotLog>
     */
    public function getDataTableQuery(): Builder;

    /**
     * Get logs by date range.
     *
     * @return Collection<int, IotLog>
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get total count of logs.
     */
    public function getTotalCount(): int;

    /**
     * Get count of error logs.
     */
    public function getErrorCount(): int;
}

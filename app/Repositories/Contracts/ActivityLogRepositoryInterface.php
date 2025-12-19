<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

interface ActivityLogRepositoryInterface
{
    /**
     * Get query builder for DataTables
     *
     * @return Builder<Activity>
     */
    public function getDataTableQuery(): Builder;

    /**
     * Find activity log by ID
     */
    public function findById(int $id): ?Activity;

    /**
     * Get activities by subject (model type and ID)
     *
     * @return Collection<int, Activity>
     */
    public function getBySubject(string $subjectType, int $subjectId): Collection;

    /**
     * Get activities by causer (user who performed the action)
     *
     * @return Collection<int, Activity>
     */
    public function getByCauser(int $userId): Collection;

    /**
     * Get activities by log name
     *
     * @return Collection<int, Activity>
     */
    public function getByLogName(string $logName): Collection;

    /**
     * Get activities within date range
     *
     * @return Collection<int, Activity>
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection;

    /**
     * Get all distinct log names
     *
     * @return array<int, string>
     */
    public function getDistinctLogNames(): array;

    /**
     * Get all distinct event types
     *
     * @return array<int, string>
     */
    public function getDistinctEventTypes(): array;
}

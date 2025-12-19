<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function __construct(
        protected Activity $model
    ) {}

    /**
     * Get query builder for DataTables
     *
     * @return Builder<Activity>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with(['causer', 'subject'])
            ->latest();
    }

    /**
     * Find activity log by ID
     */
    public function findById(int $id): ?Activity
    {
        return $this->model->newQuery()
            ->with(['causer', 'subject'])
            ->find($id);
    }

    /**
     * Get activities by subject (model type and ID)
     *
     * @return Collection<int, Activity>
     */
    public function getBySubject(string $subjectType, int $subjectId): Collection
    {
        return $this->model->newQuery()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->with(['causer'])
            ->latest()
            ->get();
    }

    /**
     * Get activities by causer (user who performed the action)
     *
     * @return Collection<int, Activity>
     */
    public function getByCauser(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('causer_id', $userId)
            ->with(['subject'])
            ->latest()
            ->get();
    }

    /**
     * Get activities by log name
     *
     * @return Collection<int, Activity>
     */
    public function getByLogName(string $logName): Collection
    {
        return $this->model->newQuery()
            ->where('log_name', $logName)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();
    }

    /**
     * Get activities within date range
     *
     * @return Collection<int, Activity>
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model->newQuery()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->with(['causer', 'subject'])
            ->latest()
            ->get();
    }

    /**
     * Get all distinct log names
     *
     * @return array<int, string>
     */
    public function getDistinctLogNames(): array
    {
        return $this->model->newQuery()
            ->distinct()
            ->pluck('log_name')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get all distinct event types
     *
     * @return array<int, string>
     */
    public function getDistinctEventTypes(): array
    {
        return $this->model->newQuery()
            ->distinct()
            ->pluck('event')
            ->filter()
            ->values()
            ->toArray();
    }
}

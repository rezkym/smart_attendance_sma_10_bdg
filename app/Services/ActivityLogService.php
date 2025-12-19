<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function __construct(
        protected ActivityLogRepositoryInterface $activityLogRepository
    ) {}

    /**
     * Get query builder for DataTables with optional filters
     *
     * @param array{
     *     log_name?: string,
     *     event?: string,
     *     causer_id?: int,
     *     subject_type?: string,
     *     subject_id?: int,
     *     date_from?: string,
     *     date_to?: string
     * } $filters
     * @return Builder<Activity>
     */
    public function getDataTableQuery(array $filters = []): Builder
    {
        $query = $this->activityLogRepository->getDataTableQuery();

        if (filled($filters['log_name'] ?? null)) {
            $query->where('log_name', $filters['log_name']);
        }

        if (filled($filters['event'] ?? null)) {
            $query->where('event', $filters['event']);
        }

        if (filled($filters['causer_id'] ?? null)) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (filled($filters['subject_type'] ?? null)) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (filled($filters['subject_id'] ?? null)) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (filled($filters['date_from'] ?? null)) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (filled($filters['date_to'] ?? null)) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Get activity log by ID
     */
    public function getLogById(int $id): ?Activity
    {
        return $this->activityLogRepository->findById($id);
    }

    /**
     * Get logs for a specific subject (model)
     *
     * @return Collection<int, Activity>
     */
    public function getLogsBySubject(string $subjectType, int $subjectId): Collection
    {
        return $this->activityLogRepository->getBySubject($subjectType, $subjectId);
    }

    /**
     * Get logs by a specific user
     *
     * @return Collection<int, Activity>
     */
    public function getLogsByCauser(int $userId): Collection
    {
        return $this->activityLogRepository->getByCauser($userId);
    }

    /**
     * Get available log names for filtering
     *
     * @return array<int, string>
     */
    public function getAvailableLogNames(): array
    {
        return $this->activityLogRepository->getDistinctLogNames();
    }

    /**
     * Get available event types for filtering
     *
     * @return array<int, string>
     */
    public function getAvailableEventTypes(): array
    {
        return $this->activityLogRepository->getDistinctEventTypes();
    }

    /**
     * Format activity properties for display
     *
     * @return array{old: mixed, attributes: mixed}
     */
    public function formatActivityProperties(Activity $activity): array
    {
        $properties = $activity->properties ?? collect();

        return [
            'old' => $properties->get('old', []),
            'attributes' => $properties->get('attributes', []),
        ];
    }

    /**
     * Get human-readable subject type name
     */
    public function getSubjectTypeName(string $subjectType): string
    {
        $mapping = [
            'App\Models\User' => 'User',
            'App\Models\Student' => 'Student',
            'App\Models\Teacher' => 'Teacher',
            'App\Models\Classroom' => 'Classroom',
            'App\Models\Schedule' => 'Schedule',
            'App\Models\Attendance' => 'Attendance',
            'App\Models\IotDevice' => 'IoT Device',
            'App\Models\Semester' => 'Semester',
            'App\Models\StudentEnrollment' => 'Student Enrollment',
            'App\Models\RfidCard' => 'RFID Card',
        ];

        return $mapping[$subjectType] ?? class_basename($subjectType);
    }
}

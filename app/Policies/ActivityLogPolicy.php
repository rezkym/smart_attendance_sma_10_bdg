<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy
{
    /**
     * Determine whether the user can view any activity logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('activity-logs.view');
    }

    /**
     * Determine whether the user can view the activity log.
     */
    public function view(User $user, Activity $activity): bool
    {
        return $user->can('activity-logs.view');
    }
}

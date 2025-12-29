<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class TeacherSchedulePolicy
{
    /**
     * Determine if the user can view their own schedules.
     */
    public function viewOwn(User $user, Schedule $schedule): bool
    {
        // User must have the permission
        if (! $user->can('teacher-schedules.view-own')) {
            return false;
        }

        // User must have a teacher profile
        $teacher = $user->teacher;
        if ($teacher === null) {
            return false;
        }

        // Schedule must belong to the teacher
        return $schedule->teacher_id === $teacher->id;
    }

    /**
     * Determine if the user can view any schedule (for listing).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('teacher-schedules.view-own') && $user->teacher !== null;
    }
}

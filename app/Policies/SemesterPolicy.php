<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Semester;
use App\Models\User;

class SemesterPolicy
{
    /**
     * Determine whether the user can view any semesters.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('semesters.view');
    }

    /**
     * Determine whether the user can view the semester.
     */
    public function view(User $user, Semester $semester): bool
    {
        return $user->can('semesters.view');
    }

    /**
     * Determine whether the user can create semesters.
     */
    public function create(User $user): bool
    {
        return $user->can('semesters.create');
    }

    /**
     * Determine whether the user can update the semester.
     */
    public function update(User $user, Semester $semester): bool
    {
        return $user->can('semesters.update');
    }

    /**
     * Determine whether the user can delete the semester.
     */
    public function delete(User $user, Semester $semester): bool
    {
        return $user->can('semesters.delete');
    }
}


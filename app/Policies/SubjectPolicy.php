<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    /**
     * Determine whether the user can view any subjects.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('subjects.view');
    }

    /**
     * Determine whether the user can view the subject.
     */
    public function view(User $user, Subject $subject): bool
    {
        return $user->can('subjects.view');
    }

    /**
     * Determine whether the user can create subjects.
     */
    public function create(User $user): bool
    {
        return $user->can('subjects.create');
    }

    /**
     * Determine whether the user can update the subject.
     */
    public function update(User $user, Subject $subject): bool
    {
        return $user->can('subjects.update');
    }

    /**
     * Determine whether the user can delete the subject.
     */
    public function delete(User $user, Subject $subject): bool
    {
        return $user->can('subjects.delete');
    }

    /**
     * Determine whether the user can assign teachers to subject.
     */
    public function assignTeachers(User $user, Subject $subject): bool
    {
        return $user->can('subjects.assign-teachers');
    }

    /**
     * Determine whether the user can assign classrooms to subject.
     */
    public function assignClassrooms(User $user, Subject $subject): bool
    {
        return $user->can('subjects.assign-classrooms');
    }
}

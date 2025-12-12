<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    /**
     * Determine whether the user can view any teachers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('teachers.view');
    }

    /**
     * Determine whether the user can view the teacher.
     */
    public function view(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.view');
    }

    /**
     * Determine whether the user can create teachers.
     */
    public function create(User $user): bool
    {
        return $user->can('teachers.create');
    }

    /**
     * Determine whether the user can update the teacher.
     */
    public function update(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.update');
    }

    /**
     * Determine whether the user can delete the teacher.
     */
    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.delete');
    }

    /**
     * Determine whether the user can assign subjects to teacher.
     */
    public function assignSubjects(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.assign-subjects');
    }

    /**
     * Determine whether the user can assign classroom to teacher.
     */
    public function assignClassroom(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.assign-classroom');
    }
}

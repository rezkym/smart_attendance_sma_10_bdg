<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    /**
     * Determine whether the user can view any classrooms.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('classrooms.view');
    }

    /**
     * Determine whether the user can view the classroom.
     */
    public function view(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.view');
    }

    /**
     * Determine whether the user can create classrooms.
     */
    public function create(User $user): bool
    {
        return $user->can('classrooms.create');
    }

    /**
     * Determine whether the user can update the classroom.
     */
    public function update(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.update');
    }

    /**
     * Determine whether the user can delete the classroom.
     */
    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.delete');
    }

    /**
     * Determine whether the user can assign homeroom teacher.
     */
    public function assignHomeroom(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.assign-homeroom');
    }

    /**
     * Determine whether the user can assign students to classroom.
     */
    public function assignStudents(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.assign-students');
    }

    /**
     * Determine whether the user can assign subjects to classroom.
     */
    public function assignSubjects(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.assign-subjects');
    }
}

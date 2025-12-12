<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can view any students.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('students.view');
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        return $user->can('students.view');
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->can('students.create');
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->can('students.update');
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->can('students.delete');
    }

    /**
     * Determine whether the user can assign RFID to student.
     */
    public function assignRfid(User $user, Student $student): bool
    {
        return $user->can('students.assign-rfid');
    }

    /**
     * Determine whether the user can assign classroom to student.
     */
    public function assignClassroom(User $user, Student $student): bool
    {
        return $user->can('students.assign-classroom');
    }
}

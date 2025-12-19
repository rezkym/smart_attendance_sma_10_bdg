<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StudentEnrollment;
use App\Models\User;

class StudentEnrollmentPolicy
{
    /**
     * Determine whether the user can view any enrollments.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('student-enrollments.view');
    }

    /**
     * Determine whether the user can view the enrollment.
     */
    public function view(User $user, StudentEnrollment $enrollment): bool
    {
        return $user->can('student-enrollments.view');
    }

    /**
     * Determine whether the user can create enrollments.
     */
    public function create(User $user): bool
    {
        return $user->can('student-enrollments.create');
    }

    /**
     * Determine whether the user can update the enrollment.
     */
    public function update(User $user, StudentEnrollment $enrollment): bool
    {
        return $user->can('student-enrollments.update');
    }

    /**
     * Determine whether the user can delete the enrollment.
     */
    public function delete(User $user, StudentEnrollment $enrollment): bool
    {
        return $user->can('student-enrollments.delete');
    }

    /**
     * Determine whether the user can transfer a student.
     */
    public function transfer(User $user): bool
    {
        return $user->can('student-enrollments.transfer');
    }

    /**
     * Determine whether the user can graduate a student.
     */
    public function graduate(User $user): bool
    {
        return $user->can('student-enrollments.graduate');
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    /**
     * Determine whether the user can view any academic years.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('academic-years.view');
    }

    /**
     * Determine whether the user can view the academic year.
     */
    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('academic-years.view');
    }

    /**
     * Determine whether the user can create academic years.
     */
    public function create(User $user): bool
    {
        return $user->can('academic-years.create');
    }

    /**
     * Determine whether the user can update the academic year.
     */
    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('academic-years.update');
    }

    /**
     * Determine whether the user can delete the academic year.
     */
    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('academic-years.delete');
    }
}

<?php

namespace App\Policies;

use App\Models\User;

class MasterDataPolicy
{
    public function view(User $user): bool
    {
        return $user->can('classes.manage') 
            || $user->can('subjects.manage') 
            || $user->can('majors.manage') 
            || $user->can('academic_years.manage');
    }

    public function manageAcademicYears(User $user): bool
    {
        return $user->can('academic_years.manage');
    }

    public function manageMajors(User $user): bool
    {
        return $user->can('majors.manage');
    }

    public function manageClasses(User $user): bool
    {
        return $user->can('classes.manage');
    }

    public function manageSubjects(User $user): bool
    {
        return $user->can('subjects.manage');
    }
}

<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExamAttemptPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('guru');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExamAttempt $examAttempt): bool
    {
        // student can only view their own attempt
        if ($user->hasRole('siswa')) {
            $student = $user->student;
            if (!$student) return false;
            return $examAttempt->student_id === $student->id;
        }

        if ($user->hasRole('guru')) {
            return $examAttempt->exam->created_by === $user->id;
        }

        // admin and kurikulum can view all
        return $user->hasRole('super_admin') || $user->hasRole('kurikulum');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExamAttempt $examAttempt): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExamAttempt $examAttempt): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExamAttempt $examAttempt): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExamAttempt $examAttempt): bool
    {
        return false;
    }

    public function viewResult(User $user, ExamAttempt $examAttempt): bool
    {
        // Must be submitted at least
        if (!in_array($examAttempt->status, ['SUBMITTED', 'AUTO_SUBMITTED'])) {
            return false;
        }

        // Student viewing their own result
        if ($user->hasRole('siswa')) {
            $student = $user->student;
            if (!$student) return false;
            if (is_null($examAttempt->exam->results_published_at)) return false;
            return $examAttempt->student_id === $student->id;
        }

        // Admin/Guru viewing specific attempt result
        if ($user->hasRole('guru')) {
            return $examAttempt->exam->created_by === $user->id;
        }

        return $user->hasRole('super_admin');
    }

    public function heartbeat(User $user, ExamAttempt $examAttempt): bool
    {
        if ($user->hasRole('siswa')) {
            $student = $user->student;
            if (!$student) return false;
            return $examAttempt->student_id === $student->id;
        }
        return false;
    }

    public function storeIntegrityEvent(User $user, ExamAttempt $examAttempt): bool
    {
        // Same logic as heartbeat
        return $this->heartbeat($user, $examAttempt);
    }
}

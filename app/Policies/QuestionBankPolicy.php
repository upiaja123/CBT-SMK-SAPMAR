<?php

namespace App\Policies;

use App\Models\QuestionBank;
use App\Models\User;

class QuestionBankPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('question_banks.view');
    }

    public function view(User $user, QuestionBank $questionBank): bool
    {
        if (!$user->hasPermissionTo('question_banks.view')) {
            return false;
        }

        // Jika user adalah guru, pastikan dia adalah pemiliknya
        if ($user->hasRole('guru')) {
            return $questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('question_banks.create');
    }

    public function update(User $user, QuestionBank $questionBank): bool
    {
        if (!$user->hasPermissionTo('question_banks.update')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }

    public function delete(User $user, QuestionBank $questionBank): bool
    {
        if (!$user->hasPermissionTo('question_banks.archive')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }
}

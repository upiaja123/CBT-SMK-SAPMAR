<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('question_banks.view');
    }

    public function view(User $user, Question $question): bool
    {
        if (!$user->hasPermissionTo('question_banks.view')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $question->questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('questions.create');
    }

    public function update(User $user, Question $question): bool
    {
        if (!$user->hasPermissionTo('questions.update')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $question->questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }

    public function delete(User $user, Question $question): bool
    {
        if (!$user->hasPermissionTo('questions.delete')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $question->questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }

    public function publish(User $user, Question $question): bool
    {
        if (!$user->hasPermissionTo('questions.publish')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $question->questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }

    public function draft(User $user, Question $question): bool
    {
        // Require same permission as publish, or we can use update permission
        if (!$user->hasPermissionTo('questions.update')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $question->questionBank->teacher_id === $user->teacher?->id;
        }

        return true;
    }
}

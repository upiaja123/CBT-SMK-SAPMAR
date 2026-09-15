<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return collect([
            'exams.create',
            'exams.update',
            'exams.schedule',
            'exams.publish',
            'exams.monitor'
        ])->contains(fn ($permission) => $user->hasPermissionTo($permission));
    }

    public function view(User $user, Exam $exam): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('exams.create');
    }

    public function update(User $user, Exam $exam): bool
    {
        if (!$user->hasPermissionTo('exams.update')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $exam->created_by === $user->id;
        }

        return true;
    }

    public function delete(User $user, Exam $exam): bool
    {
        if (!$user->hasPermissionTo('exams.archive')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $exam->created_by === $user->id;
        }

        return true;
    }

    public function schedule(User $user, Exam $exam): bool
    {
        return $user->hasPermissionTo('exams.schedule');
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->hasPermissionTo('exams.publish') || $user->hasRole('super_admin');
    }

    public function grade(User $user, Exam $exam): bool
    {
        if (!$user->hasPermissionTo('essay.grade')) {
            return false;
        }

        if ($user->hasRole('guru')) {
            return $exam->created_by === $user->id;
        }

        return true;
    }

    public function viewResults(User $user, Exam $exam): bool
    {
        // Require a baseline permission for viewing results (or reuse exams.monitor/view)
        // For MVP, we can reuse `exams.monitor` or check role + ownership.
        if ($user->hasRole('guru')) {
            return $exam->created_by === $user->id;
        }

        // Super Admin or Kurikulum (assuming they have 'exams.monitor' or similar)
        return $user->hasPermissionTo('exams.monitor') || $user->hasRole('super_admin');
    }

    public function monitor(User $user, Exam $exam): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('kurikulum')) {
            return $user->hasPermissionTo('exams.monitor');
        }

        if ($user->hasRole('proktor')) {
            if (!$user->hasPermissionTo('exams.monitor')) {
                return false;
            }

            return \App\Models\ProctorExamAssignment::where('proctor_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('active', true)
                ->exists();
        }

        if ($user->hasRole('guru')) {
            return $exam->created_by === $user->id;
        }

        return false;
    }

    public function control(User $user, Exam $exam): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('kurikulum')) {
            return $user->hasPermissionTo('exams.monitor') || $user->hasPermissionTo('exams.lock');
        }

        if ($user->hasRole('proktor')) {
            if (!$user->hasPermissionTo('exams.lock')) {
                return false;
            }
            // Proktor must also be assigned to this specific exam
            return \App\Models\ProctorExamAssignment::where('proctor_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('active', true)
                ->exists();
        }

        if ($user->hasRole('guru')) {
            return false;
        }

        return false;
    }

    public function pause(User $user, Exam $exam): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('kurikulum');
    }

    public function assignProctor(User $user, Exam $exam): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('kurikulum')) {
            // Assume kurikulum needs some permission to manage exams/proctors, or just let them do it
            // For now, allow kurikulum
            return true;
        }

        return false;
    }
}

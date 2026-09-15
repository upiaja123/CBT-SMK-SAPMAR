<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('exam.{examId}.monitoring', function ($user, $examId) {
    $exam = \App\Models\Exam::find($examId);
    if (!$exam) {
        return false;
    }
    
    return $user->can('monitor', $exam);
});

Broadcast::channel('attempt.{attemptId}', function ($user, $attemptId) {
    $attempt = \App\Models\ExamAttempt::find($attemptId);
    if (!$attempt) {
        return false;
    }

    if (!$user->hasRole('siswa') || !$user->student) {
        return false;
    }

    return (int) $attempt->student_id === (int) $user->student->id;
});

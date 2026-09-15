<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\ParticipantAnswer;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ManualGradingService
{
    /**
     * Save manual grade for a specific participant answer.
     * Includes concurrency protection and automatic attempt score aggregation.
     */
    public function saveGrade(ParticipantAnswer $answer, float $score, ?string $feedback, User $grader): void
    {
        // 1. Initial Validation
        if ($score < 0 || $score > $answer->max_score) {
            throw new InvalidArgumentException("Score must be between 0 and {$answer->max_score}.");
        }

        // Must belong to an attempt that is finalized
        $attemptStatus = $answer->examAttempt->status;
        if (!in_array($attemptStatus, ['SUBMITTED', 'AUTO_SUBMITTED'])) {
            throw new InvalidArgumentException("Cannot grade an attempt that is not submitted. Current status: {$attemptStatus}");
        }

        DB::transaction(function () use ($answer, $score, $feedback, $grader) {
            // 2. Concurrency Protection
            // Lock the answer row to prevent concurrent grading of the same answer
            $lockedAnswer = ParticipantAnswer::where('id', $answer->id)->lockForUpdate()->first();
            
            // Lock the attempt row to prevent concurrent aggregation updates
            $lockedAttempt = ExamAttempt::where('id', $lockedAnswer->exam_attempt_id)->lockForUpdate()->first();

            // 3. Update ParticipantAnswer
            $lockedAnswer->update([
                'awarded_score' => $score,
                'feedback' => $feedback,
                'graded_by' => $grader->id,
                'graded_at' => now(),
                'grading_status' => 'MANUALLY_GRADED',
            ]);

            // 4. Recalculate Attempt Total Score
            // Aggregate all awarded scores for this attempt (objective + manual)
            $totalScore = $lockedAttempt->participantAnswers()->sum('awarded_score');
            
            // 5. Finalization Check
            // Check if there are any answers still waiting for manual grading
            $hasWaiting = $lockedAttempt->participantAnswers()->where('grading_status', 'WAITING_MANUAL')->exists();
            $newGradingStatus = $hasWaiting ? 'WAITING_MANUAL' : 'FINAL';

            $lockedAttempt->update([
                'total_score' => $totalScore,
                'grading_status' => $newGradingStatus,
            ]);

            // 6. Audit Log
            AuditLog::create([
                'user_id' => $grader->id,
                'action' => 'exam_answer_manually_graded',
                'auditable_type' => 'participant_answers',
                'auditable_id' => $lockedAnswer->id,
                'new_values' => [
                    'awarded_score' => $score,
                    'grading_status' => $newGradingStatus,
                    'attempt_total_score' => $totalScore
                ]
            ]);
        });
    }
}

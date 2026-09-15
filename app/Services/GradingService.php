<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\AttemptQuestionSnapshot;
use App\Models\ParticipantAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GradingService
{
    /**
     * Grade all answers for a finalized attempt.
     * 
     * Source of truth: AttemptQuestionSnapshot + AttemptOptionSnapshot + ParticipantAnswer
     * NEVER reads from mutable Question/QuestionVersion/QuestionOption.
     *
     * Idempotent: safe to call multiple times.
     */
    public function gradeAttempt(ExamAttempt $attempt): void
    {
        // 1. Only grade finalized attempts
        if (!in_array($attempt->status, ['SUBMITTED', 'AUTO_SUBMITTED'])) {
            throw new \InvalidArgumentException('Attempt harus sudah disubmit sebelum di-grade.');
        }

        // 2. Eager-load all snapshots + options + answers in one query (no N+1)
        $attempt->load([
            'questionSnapshots.optionSnapshots',
            'questionSnapshots.participantAnswer',
        ]);

        $totalScore = 0;
        $maxTotalScore = 0;
        $hasEssay = false;
        $allGraded = true;
        $gradedAt = Carbon::now();

        DB::transaction(function () use ($attempt, &$totalScore, &$maxTotalScore, &$hasEssay, &$allGraded, $gradedAt) {
            foreach ($attempt->questionSnapshots as $snapshot) {
                $answer = $snapshot->participantAnswer;
                $maxScore = (float) $snapshot->weight;
                $maxTotalScore += $maxScore;

                $answerData = $answer ? $answer->answer : null;

                if (!$answer) {
                    // No answer submitted — create a placeholder record
                    $answer = ParticipantAnswer::create([
                        'exam_attempt_id' => $attempt->id,
                        'attempt_question_snapshot_id' => $snapshot->id,
                        'answer' => null,
                        'client_timestamp' => 0,
                        'is_correct' => false,
                        'awarded_score' => 0,
                        'max_score' => $maxScore,
                        'grading_status' => 'NOT_GRADED',
                    ]);
                }

                // Dispatch to type-specific grader using the extracted data
                $result = $this->gradeQuestionData($snapshot, $answerData);

                $answer->update([
                    'is_correct' => $result['is_correct'],
                    'awarded_score' => $result['awarded_score'],
                    'max_score' => $maxScore,
                    'grading_status' => $result['grading_status'],
                    'graded_at' => $gradedAt,
                ]);

                if ($result['grading_status'] === 'WAITING_MANUAL') {
                    $hasEssay = true;
                    $allGraded = false;
                } else {
                    $totalScore += $result['awarded_score'];
                }
            }

            // 3. Determine attempt-level grading status
            // If there's an essay or question needing manual review, it's WAITING_MANUAL.
            // Otherwise, it's totally auto-graded.
            $gradingStatus = $allGraded ? 'AUTO_GRADED' : 'WAITING_MANUAL';

            $attempt->update([
                'grading_status' => $gradingStatus,
                'total_score' => $totalScore,
                'max_total_score' => $maxTotalScore,
                'graded_at' => $gradedAt,
            ]);
        });

        // 4. Audit log
        \App\Models\AuditLog::create([
            'user_id' => null, // system-initiated
            'action' => 'exam_attempt_graded',
            'auditable_type' => 'exam_attempts',
            'auditable_id' => $attempt->id,
            'new_values' => [
                'grading_status' => $attempt->grading_status,
                'total_score' => $totalScore,
                'max_total_score' => $maxTotalScore,
            ],
        ]);
    }

    protected function gradeQuestionData(AttemptQuestionSnapshot $snapshot, ?array $answerData): array
    {
        return match ($snapshot->question_type) {
            'multiple_choice', 'true_false' => $this->gradeMultipleChoice($snapshot, $answerData),
            'multiple_correct' => $this->gradeMultipleCorrect($snapshot, $answerData),
            'matching' => $this->gradeMatching($snapshot, $answerData),
            'short_answer' => $this->gradeShortAnswer($snapshot, $answerData),
            'essay' => $this->gradeEssay($snapshot),
            default => [
                'is_correct' => null,
                'awarded_score' => 0,
                'grading_status' => 'WAITING_MANUAL',
            ],
        };
    }

    /**
     * Multiple Choice / True-False: exact single-option match.
     * Compare student's selected option_id against snapshot option where is_correct=true.
     */
    protected function gradeMultipleChoice(AttemptQuestionSnapshot $snapshot, ?array $answerData): array
    {
        $maxScore = (float) $snapshot->weight;

        if (!$answerData || !isset($answerData['option_id'])) {
            return ['is_correct' => false, 'awarded_score' => 0, 'grading_status' => 'AUTO_GRADED'];
        }

        $selectedOptionId = $answerData['option_id'];

        // Find the correct option from the SNAPSHOT (not from live question bank)
        $correctOption = $snapshot->optionSnapshots->firstWhere('is_correct', true);

        if (!$correctOption) {
            // No correct option defined — cannot grade
            return ['is_correct' => null, 'awarded_score' => 0, 'grading_status' => 'WAITING_MANUAL'];
        }

        $isCorrect = (int) $selectedOptionId === (int) $correctOption->id;

        return [
            'is_correct' => $isCorrect,
            'awarded_score' => $isCorrect ? $maxScore : 0,
            'grading_status' => 'AUTO_GRADED',
        ];
    }

    /**
     * Multiple Correct: exact-set match.
     * Student's selected option_ids must exactly equal the snapshot's correct option IDs.
     * No partial credit for MVP.
     */
    protected function gradeMultipleCorrect(AttemptQuestionSnapshot $snapshot, ?array $answerData): array
    {
        $maxScore = (float) $snapshot->weight;

        if (!$answerData || !isset($answerData['option_ids']) || !is_array($answerData['option_ids'])) {
            return ['is_correct' => false, 'awarded_score' => 0, 'grading_status' => 'AUTO_GRADED'];
        }

        // Get correct option IDs from snapshot
        $correctIds = $snapshot->optionSnapshots
            ->where('is_correct', true)
            ->pluck('id')
            ->sort()
            ->values()
            ->toArray();

        // Student's selection (sorted for comparison)
        $studentIds = collect($answerData['option_ids'])
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values()
            ->toArray();

        $isCorrect = $studentIds === $correctIds;

        return [
            'is_correct' => $isCorrect,
            'awarded_score' => $isCorrect ? $maxScore : 0,
            'grading_status' => 'AUTO_GRADED',
        ];
    }

    /**
     * Matching: proportional scoring.
     * Each correct pair contributes weight × (correct_pairs / total_pairs).
     * Correct pairs defined in scoring_metadata.pairs on the snapshot.
     */
    protected function gradeMatching(AttemptQuestionSnapshot $snapshot, ?array $answerData): array
    {
        $maxScore = (float) $snapshot->weight;
        $metadata = $snapshot->scoring_metadata;

        if (!$metadata || !isset($metadata['pairs']) || !is_array($metadata['pairs'])) {
            return ['is_correct' => null, 'awarded_score' => 0, 'grading_status' => 'WAITING_MANUAL'];
        }

        if (!$answerData || !isset($answerData['pairs']) || !is_array($answerData['pairs'])) {
            return ['is_correct' => false, 'awarded_score' => 0, 'grading_status' => 'AUTO_GRADED'];
        }

        $correctPairs = $metadata['pairs'];
        $studentPairs = $answerData['pairs'];
        $totalPairs = count($correctPairs);

        if ($totalPairs === 0) {
            return ['is_correct' => null, 'awarded_score' => 0, 'grading_status' => 'WAITING_MANUAL'];
        }

        // Build a lookup of correct mappings: left -> right
        $correctMap = [];
        foreach ($correctPairs as $pair) {
            $correctMap[(string) $pair['left']] = (string) $pair['right'];
        }

        $correctCount = 0;
        foreach ($studentPairs as $pair) {
            if (!isset($pair['left'], $pair['right'])) continue;
            $left = (string) $pair['left'];
            $right = (string) $pair['right'];
            if (isset($correctMap[$left]) && $correctMap[$left] === $right) {
                $correctCount++;
            }
        }

        $ratio = $correctCount / $totalPairs;
        $awardedScore = round($maxScore * $ratio, 2);
        $isCorrect = $correctCount === $totalPairs;

        return [
            'is_correct' => $isCorrect,
            'awarded_score' => $awardedScore,
            'grading_status' => 'AUTO_GRADED',
        ];
    }

    /**
     * Short Answer: trimmed + case-insensitive comparison.
     * Accepted answers from scoring_metadata.accepted_answers[] on the snapshot.
     * No fuzzy matching.
     */
    protected function gradeShortAnswer(AttemptQuestionSnapshot $snapshot, ?array $answerData): array
    {
        $maxScore = (float) $snapshot->weight;
        $metadata = $snapshot->scoring_metadata;

        if (!$metadata || !isset($metadata['accepted_answers']) || !is_array($metadata['accepted_answers'])) {
            // No accepted answers defined — cannot auto-grade
            return ['is_correct' => null, 'awarded_score' => 0, 'grading_status' => 'WAITING_MANUAL'];
        }

        if (!$answerData || !isset($answerData['text'])) {
            return ['is_correct' => false, 'awarded_score' => 0, 'grading_status' => 'AUTO_GRADED'];
        }

        $studentText = mb_strtolower(trim($answerData['text']));

        // Normalize each accepted answer and compare
        foreach ($metadata['accepted_answers'] as $accepted) {
            $normalizedAccepted = mb_strtolower(trim($accepted));
            if ($studentText === $normalizedAccepted) {
                return [
                    'is_correct' => true,
                    'awarded_score' => $maxScore,
                    'grading_status' => 'AUTO_GRADED',
                ];
            }
        }

        return [
            'is_correct' => false,
            'awarded_score' => 0,
            'grading_status' => 'AUTO_GRADED',
        ];
    }

    /**
     * Essay: never auto-graded. Mark as WAITING_MANUAL.
     */
    protected function gradeEssay(AttemptQuestionSnapshot $snapshot): array
    {
        return [
            'is_correct' => null,
            'awarded_score' => null,
            'grading_status' => 'WAITING_MANUAL',
        ];
    }
}

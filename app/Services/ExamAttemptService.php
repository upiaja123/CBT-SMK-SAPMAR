<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamAttempt;
use App\Models\AttemptQuestionSnapshot;
use App\Models\AttemptOptionSnapshot;
use App\Models\ExamParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ExamAttemptService
{
    /**
     * Check if a student is eligible to take the exam.
     */
    public function checkEligibility(Exam $exam, Student $student): bool
    {
        // Eligible if explicitly assigned by student_id OR by school_class_id
        $participant = ExamParticipant::where('exam_id', $exam->id)
            ->where(function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->orWhere('school_class_id', $student->school_class_id);
            })
            ->orderBy('is_susulan', 'desc') // prioritize specific student assignment over class
            ->first();

        if (!$participant) {
            return false;
        }

        $now = now();

        // Check if student is doing susulan
        if ($participant->student_id == $student->id && $participant->is_susulan) {
            if ($participant->susulan_start_at && $now->lt($participant->susulan_start_at)) {
                return false;
            }
            if ($participant->susulan_end_at && $now->gt($participant->susulan_end_at)) {
                return false;
            }
            return true;
        }

        // Regular check
        if ($exam->start_at && $now->lt($exam->start_at)) {
            return false;
        }
        if ($exam->end_at && $now->gt($exam->end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Start an exam attempt safely.
     * Uses transaction to ensure atomicity.
     */
    public function startAttempt(Exam $exam, Student $student): ExamAttempt
    {
        if (!$this->checkEligibility($exam, $student)) {
            abort(403, 'Siswa tidak memiliki akses ke ujian ini.');
        }

        // Check if an attempt already exists
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttempt) {
            abort(409, 'Anda sudah pernah atau sedang mengerjakan ujian ini.');
        }

        return DB::transaction(function () use ($exam, $student) {
            $startedAt = Carbon::now();
            $deadlineAt = $exam->duration ? $startedAt->copy()->addMinutes($exam->duration) : null;

            // 1. Create attempt
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'status' => 'IN_PROGRESS',
                'attempt_number' => 1,
                'started_at' => $startedAt,
                'deadline_at' => $deadlineAt,
            ]);

            // 2. Fetch all exam questions with versions and options
            $examQuestions = $exam->examQuestions()->with('questionVersion.options')->get();

            // 3. Prepare snapshots collection
            $questionSnapshotsToInsert = [];

            // Randomize questions if configured
            $questions = $exam->random_question ? $examQuestions->shuffle() : $examQuestions->sortBy('order');

            $qOrder = 1;
            $optionsToInsertByQId = []; // Temporary holding before mass insert

            foreach ($questions as $eq) {
                $qv = $eq->questionVersion;
                if (!$qv)
                    continue;

                $qSnapshot = AttemptQuestionSnapshot::create([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $eq->id,
                    'original_question_version_id' => $qv->id,
                    'question_type' => $qv->type,
                    'content' => $qv->content,
                    'topic' => $qv->topic,
                    'difficulty' => $qv->difficulty,
                    'cognitive_level' => $qv->cognitive_level,
                    'competency' => $qv->competency,
                    'order' => $qOrder++,
                    'weight' => $eq->weight,
                    'scoring_metadata' => $qv->scoring_metadata,
                ]);

                // Handle options
                if ($qv->options->isNotEmpty()) {
                    $options = $exam->random_option ? $qv->options->shuffle() : $qv->options->sortBy('order');
                    $optOrder = 1;

                    $optionsData = [];
                    foreach ($options as $opt) {
                        $optionsData[] = [
                            'attempt_question_snapshot_id' => $qSnapshot->id,
                            'original_option_id' => $opt->id,
                            'content' => $opt->content,
                            'is_correct' => $opt->is_correct,
                            'order' => $optOrder++,
                            'weight' => $opt->weight,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    AttemptOptionSnapshot::insert($optionsData);
                }
            }

            \App\Models\AuditLog::create([
                'user_id' => $student->user_id,
                'action' => 'exam_attempt_started',
                'auditable_type' => 'exam_attempts',
                'auditable_id' => $attempt->id,
                'new_values' => ['exam_id' => $exam->id, 'attempt_id' => $attempt->id],
            ]);

            return $attempt;
        });
    }

    public function saveAnswer(ExamAttempt $attempt, int $snapshotId, $answerData, int $clientTimestamp): void
    {
        // 1. Validate state & deadline
        if ($attempt->status !== 'IN_PROGRESS') {
            throw new \InvalidArgumentException('Sesi ujian sudah tidak aktif.');
        }

        // Reject if attempt is locked by proctor
        if ($attempt->locked_at !== null) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Ujian sementara dikunci oleh pengawas.');
        }

        $serverNow = now();
        if ($attempt->deadline_at && $serverNow->gt($attempt->deadline_at)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Waktu ujian telah habis.');
        }

        // 2. Load Snapshot
        $snapshot = $attempt->questionSnapshots()->where('id', $snapshotId)->first();
        if (!$snapshot) {
            throw new \InvalidArgumentException('Soal tidak ditemukan dalam sesi ini.');
        }

        // 3. Validate Answer Structure based on type
        // The $answerData comes from the client as a JSON payload, e.g., {"option_id": 12}
        $this->validateAnswerData($snapshot, $answerData);

        // 4. Save/Upsert with Race Condition check
        DB::transaction(function () use ($attempt, $snapshot, $answerData, $clientTimestamp) {
            $existing = \App\Models\ParticipantAnswer::where('exam_attempt_id', $attempt->id)
                ->where('attempt_question_snapshot_id', $snapshot->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                // Optimistic lock: only update if client_timestamp is newer
                if ($clientTimestamp > $existing->client_timestamp) {
                    $existing->update([
                        'answer' => $answerData,
                        'client_timestamp' => $clientTimestamp
                    ]);
                }
            } else {
                \App\Models\ParticipantAnswer::create([
                    'exam_attempt_id' => $attempt->id,
                    'attempt_question_snapshot_id' => $snapshot->id,
                    'answer' => $answerData,
                    'client_timestamp' => $clientTimestamp
                ]);
            }
        }, 5);
    }

    protected function validateAnswerData(AttemptQuestionSnapshot $snapshot, $answerData): void
    {
        // Very strict server-side validation against the snapshot's available options.
        if ($snapshot->question_type === 'multiple_choice' || $snapshot->question_type === 'true_false') {
            if (!is_array($answerData) || !isset($answerData['option_id'])) {
                throw new \InvalidArgumentException('Format jawaban tidak valid untuk multiple choice.');
            }

            $optionId = $answerData['option_id'];

            $isValid = $snapshot->optionSnapshots()->where('id', $optionId)->exists();
            if (!$isValid) {
                throw new \InvalidArgumentException('Opsi jawaban tidak valid.');
            }
        } elseif ($snapshot->question_type === 'multiple_correct') {
            if (!is_array($answerData) || !isset($answerData['option_ids']) || !is_array($answerData['option_ids'])) {
                throw new \InvalidArgumentException('Format jawaban tidak valid untuk multiple correct.');
            }

            $validCount = $snapshot->optionSnapshots()->whereIn('id', $answerData['option_ids'])->count();
            if ($validCount !== count($answerData['option_ids'])) {
                throw new \InvalidArgumentException('Beberapa opsi jawaban tidak valid.');
            }
        } elseif ($snapshot->question_type === 'essay' || $snapshot->question_type === 'short_answer') {
            if (!is_array($answerData) || !isset($answerData['text'])) {
                throw new \InvalidArgumentException('Format jawaban tidak valid untuk essay/isian singkat.');
            }
        } elseif ($snapshot->question_type === 'matching') {
            if (!is_array($answerData) || !isset($answerData['pairs']) || !is_array($answerData['pairs'])) {
                throw new \InvalidArgumentException('Format jawaban tidak valid untuk pencocokan.');
            }
        }
    }

    public function finalizeAttempt(ExamAttempt $attempt, bool $isAutoSubmit = false): void
    {
        $wasFinalized = false;

        DB::transaction(function () use ($attempt, $isAutoSubmit, &$wasFinalized) {
            // Lock the row to prevent race conditions
            $lockedAttempt = ExamAttempt::where('id', $attempt->id)->lockForUpdate()->first();

            // If already finalized, do nothing
            if (in_array($lockedAttempt->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'CANCELLED'])) {
                return;
            }

            $serverNow = now();

            // Re-evaluate if manual submit actually missed the deadline
            if (!$isAutoSubmit && $lockedAttempt->deadline_at && $serverNow->gt($lockedAttempt->deadline_at)) {
                $isAutoSubmit = true;
            }

            $lockedAttempt->status = $isAutoSubmit ? 'AUTO_SUBMITTED' : 'SUBMITTED';
            $lockedAttempt->submitted_at = $serverNow;
            $lockedAttempt->save();

            \App\Models\AuditLog::create([
                'user_id' => $lockedAttempt->student->user_id,
                'action' => $isAutoSubmit ? 'exam_attempt_auto_submitted' : 'exam_attempt_manually_submitted',
                'auditable_type' => 'exam_attempts',
                'auditable_id' => $lockedAttempt->id,
                'new_values' => ['mode' => $isAutoSubmit ? 'AUTO' : 'MANUAL', 'submitted_at' => $serverNow->toIso8601String()],
            ]);

            $wasFinalized = true;
        });

        // Trigger grading AFTER finalization transaction commits
        if ($wasFinalized) {
            $attempt->refresh();
            try {
                app(GradingService::class)->gradeAttempt($attempt);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Grading failed for attempt {$attempt->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Add extra time to an in-progress attempt.
     */
    public function addExtraTime(ExamAttempt $attempt, int $minutes, \App\Models\User $actor): void
    {
        if ($minutes <= 0 || $minutes > 120) {
            throw new \InvalidArgumentException('Extra time must be between 1 and 120 minutes.');
        }

        if (in_array($attempt->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'CANCELLED'])) {
            throw new \InvalidArgumentException('Cannot add extra time to a finalized attempt.');
        }

        DB::transaction(function () use ($attempt, $minutes, $actor) {
            $locked = ExamAttempt::where('id', $attempt->id)->lockForUpdate()->first();

            if (in_array($locked->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'CANCELLED'])) {
                throw new \InvalidArgumentException('Cannot add extra time to a finalized attempt.');
            }

            $oldDeadline = $locked->deadline_at;
            $newDeadline = $locked->deadline_at
                ? $locked->deadline_at->addMinutes($minutes)
                : now()->addMinutes($minutes);

            $locked->deadline_at = $newDeadline;
            $locked->save();

            \App\Models\AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'exam_attempt_extra_time_added',
                'auditable_type' => 'exam_attempts',
                'auditable_id' => $locked->id,
                'old_values' => ['deadline_at' => $oldDeadline?->toIso8601String()],
                'new_values' => [
                    'deadline_at' => $newDeadline->toIso8601String(),
                    'minutes_added' => $minutes,
                ],
            ]);

            // Broadcast AFTER transaction commits using closures
            $attempt->refresh();
        });

        $attempt->refresh();
        event(new \App\Events\ExamAttemptControlUpdated($attempt, 'extra_time'));
    }

    /**
     * Lock an attempt.
     */
    public function lockAttempt(ExamAttempt $attempt, \App\Models\User $actor, ?string $reason = null): void
    {
        if (in_array($attempt->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'CANCELLED'])) {
            throw new \InvalidArgumentException('Cannot lock a finalized attempt.');
        }

        DB::transaction(function () use ($attempt, $actor, $reason) {
            $locked = ExamAttempt::where('id', $attempt->id)->lockForUpdate()->first();

            if (in_array($locked->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'CANCELLED'])) {
                throw new \InvalidArgumentException('Cannot lock a finalized attempt.');
            }

            // Idempotent: if already locked, just update reason
            $locked->locked_at = $locked->locked_at ?? now();
            $locked->locked_by = $actor->id;
            $locked->lock_reason = $reason;
            $locked->save();

            \App\Models\AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'exam_attempt_locked',
                'auditable_type' => 'exam_attempts',
                'auditable_id' => $locked->id,
                'new_values' => ['locked_at' => $locked->locked_at->toIso8601String(), 'reason' => $reason],
            ]);
        });

        $attempt->refresh();
        event(new \App\Events\ExamAttemptControlUpdated($attempt, 'lock'));
    }

    /**
     * Unlock an attempt.
     */
    public function unlockAttempt(ExamAttempt $attempt, \App\Models\User $actor): void
    {
        if (in_array($attempt->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'CANCELLED'])) {
            throw new \InvalidArgumentException('Cannot unlock a finalized attempt.');
        }

        DB::transaction(function () use ($attempt, $actor) {
            $locked = ExamAttempt::where('id', $attempt->id)->lockForUpdate()->first();

            // Idempotent: if already unlocked, no-op
            if ($locked->locked_at === null) {
                return;
            }

            $locked->locked_at = null;
            $locked->locked_by = null;
            $locked->lock_reason = null;
            $locked->save();

            \App\Models\AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'exam_attempt_unlocked',
                'auditable_type' => 'exam_attempts',
                'auditable_id' => $locked->id,
                'new_values' => ['unlocked_at' => now()->toIso8601String()],
            ]);
        });

        $attempt->refresh();
        event(new \App\Events\ExamAttemptControlUpdated($attempt, 'unlock'));
    }

    /**
     * Force submit an in-progress attempt as proctor.
     * Delegates to existing finalizeAttempt() to reuse all concurrency/grading logic.
     * Recorded as AUTO_SUBMITTED with audit action 'exam_attempt_force_submitted'.
     */
    public function forceSubmit(ExamAttempt $attempt, \App\Models\User $actor): void
    {
        if (in_array($attempt->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'CANCELLED'])) {
            throw new \InvalidArgumentException('Attempt is already finalized.');
        }

        // Record the force-submit actor before finalization
        \App\Models\AuditLog::create([
            'user_id' => $actor->id,
            'action' => 'exam_attempt_force_submitted',
            'auditable_type' => 'exam_attempts',
            'auditable_id' => $attempt->id,
            'new_values' => ['forced_by' => $actor->id, 'forced_at' => now()->toIso8601String()],
        ]);

        // Reuse finalization with auto-submit semantics for concurrency safety
        $this->finalizeAttempt($attempt, isAutoSubmit: true);

        $attempt->refresh();
        event(new \App\Events\ExamAttemptControlUpdated($attempt, 'force_submit'));
    }
}

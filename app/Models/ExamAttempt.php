<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'status',
        'attempt_number',
        'started_at',
        'submitted_at',
        'deadline_at',
        'metadata',
        'grading_status',
        'total_score',
        'max_total_score',
        'graded_at',
        'last_seen_at',
        'locked_at',
        'locked_by',
        'lock_reason',
        'review_status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'deadline_at' => 'datetime',
            'locked_at' => 'datetime',
            'metadata' => 'json',
            'graded_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'total_score' => 'decimal:2',
            'max_total_score' => 'decimal:2',
        ];
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->locked_at !== null;
    }

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function questionSnapshots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttemptQuestionSnapshot::class)->orderBy('order');
    }

    public function participantAnswers()
    {
        return $this->hasMany(ParticipantAnswer::class);
    }

    public function integrityEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IntegrityEvent::class);
    }

    public function reviewNotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamAttemptReviewNote::class);
    }

    public function getConnectionStatusAttribute(): string
    {
        if (!$this->last_seen_at) {
            return 'UNKNOWN';
        }

        // 30 seconds threshold
        if ($this->last_seen_at->lt(now()->subSeconds(30))) {
            return 'OFFLINE';
        }

        return 'ONLINE';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptQuestionSnapshot extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'exam_attempt_id',
        'exam_question_id',
        'original_question_version_id',
        'question_type',
        'content',
        'topic',
        'difficulty',
        'cognitive_level',
        'competency',
        'order',
        'weight',
        'scoring_metadata',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'scoring_metadata' => 'array',
        ];
    }

    public function attempt(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function optionSnapshots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttemptOptionSnapshot::class, 'attempt_question_snapshot_id')->orderBy('order');
    }

    public function participantAnswer()
    {
        return $this->hasOne(ParticipantAnswer::class, 'attempt_question_snapshot_id');
    }

    public function originalVersion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(QuestionVersion::class, 'original_question_version_id');
    }
}

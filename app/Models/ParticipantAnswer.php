<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantAnswer extends Model
{
    //
    protected $fillable = [
        'exam_attempt_id',
        'attempt_question_snapshot_id',
        'answer',
        'client_timestamp',
        'is_correct',
        'awarded_score',
        'max_score',
        'grading_status',
        'graded_at',
        'graded_by',
        'feedback',
    ];

    protected $casts = [
        'answer' => 'array',
        'is_correct' => 'boolean',
        'awarded_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function examAttempt()
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function questionSnapshot()
    {
        return $this->belongsTo(AttemptQuestionSnapshot::class, 'attempt_question_snapshot_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttemptReviewNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_attempt_id',
        'user_id',
        'note',
    ];

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

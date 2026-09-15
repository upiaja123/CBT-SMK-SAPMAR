<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrityEvent extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'exam_attempt_id',
        'student_id',
        'event_type',
        'occurred_at',
        'server_received_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'server_received_at' => 'datetime',
            'metadata' => 'json',
        ];
    }

    public function attempt(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

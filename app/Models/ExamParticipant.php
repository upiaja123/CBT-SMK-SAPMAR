<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamParticipant extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'exam_id',
        'school_class_id',
        'student_id',
        'is_susulan',
        'susulan_start_at',
        'susulan_end_at',
    ];

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    protected function casts(): array
    {
        return [
            'is_susulan' => 'boolean',
            'susulan_start_at' => 'datetime',
            'susulan_end_at' => 'datetime',
        ];
    }

    public function schoolClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

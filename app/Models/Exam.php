<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'title',
        'code',
        'subject_id',
        'description',
        'exam_type',
        'grade',
        'duration',
        'start_at',
        'end_at',
        'token',
        'status',
        'random_question',
        'random_option',
        'review_summary',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'random_question' => 'boolean',
            'random_option' => 'boolean',
            'review_summary' => 'boolean',
        ];
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the dynamic status of the exam based on real-time.
     */
    public function getDynamicStatusAttribute(): string
    {
        if ($this->status !== 'PUBLISHED') {
            return $this->status;
        }

        $now = now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return 'SCHEDULED';
        }

        if ($this->start_at && $this->end_at && $now->between($this->start_at, $this->end_at)) {
            return 'ONGOING';
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return 'ENDED';
        }

        return $this->status;
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamParticipant::class);
    }

    /**
     * Get all students eligible for this exam (combining classes and specific student assignments).
     */
    public function getEligibleStudentsAttribute()
    {
        $participantClassIds = $this->participants()->whereNotNull('school_class_id')->pluck('school_class_id');
        $participantStudentIds = $this->participants()->whereNotNull('student_id')->pluck('student_id');

        return Student::with(['user', 'schoolClass'])
            ->where(function ($query) use ($participantClassIds, $participantStudentIds) {
                if ($participantClassIds->isNotEmpty()) {
                    $query->whereIn('school_class_id', $participantClassIds);
                }
                if ($participantStudentIds->isNotEmpty()) {
                    $query->orWhereIn('id', $participantStudentIds);
                }
                // If both are empty, this will just return no students, which is correct
                if ($participantClassIds->isEmpty() && $participantStudentIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->get()
            ->sortBy(function ($student) {
                return ($student->schoolClass->name ?? '') . '-' . ($student->user->name ?? '');
            })
            ->values();
    }

    public function examQuestions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function attempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}

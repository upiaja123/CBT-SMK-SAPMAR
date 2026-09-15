<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_class_id',
        'nisn',
        'nis',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function examParticipants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamParticipant::class);
    }
}

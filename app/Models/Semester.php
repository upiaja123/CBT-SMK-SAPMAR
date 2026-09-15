<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Semester extends Model
{
    /** @use HasFactory<\Database\Factories\SemesterFactory> */
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'type',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'odd' => 'Ganjil',
            'even' => 'Genap',
            default => $this->type,
        };
    }

    public function getFullNameAttribute(): string
    {
        return $this->academicYear->name . ' — Semester ' . $this->type_name;
    }
}

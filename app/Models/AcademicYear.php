<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicYearFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'year_start',
        'year_end',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'year_start' => 'integer',
            'year_end' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function activeSemester(): BelongsTo
    {
        return $this->hasOne(Semester::class)->where('is_active', true);
    }

    public static function active(): self
    {
        return self::where('is_active', true)->firstOrFail();
    }
}

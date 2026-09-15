<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionVersion extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'question_id',
        'version',
        'type',
        'cognitive_level',
        'difficulty',
        'topic',
        'competency',
        'content',
        'scoring_metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scoring_metadata' => 'array',
        ];
    }

    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

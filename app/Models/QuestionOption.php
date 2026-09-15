<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'question_version_id',
        'content',
        'is_correct',
        'order',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'weight' => 'decimal:2',
        ];
    }

    public function questionVersion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(QuestionVersion::class);
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}

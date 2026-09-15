<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptOptionSnapshot extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'attempt_question_snapshot_id',
        'original_option_id',
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

    public function questionSnapshot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AttemptQuestionSnapshot::class, 'attempt_question_snapshot_id');
    }
}

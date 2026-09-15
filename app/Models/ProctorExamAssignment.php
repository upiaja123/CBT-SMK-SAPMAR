<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorExamAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'proctor_id',
        'exam_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function proctor()
    {
        return $this->belongsTo(User::class, 'proctor_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}

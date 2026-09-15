<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => \App\Models\Exam::factory(),
            'student_id' => \App\Models\Student::factory(),
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
            'deadline_at' => now()->addMinutes(60),
        ];
    }
}

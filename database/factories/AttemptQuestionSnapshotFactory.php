<?php

namespace Database\Factories;

use App\Models\AttemptQuestionSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttemptQuestionSnapshot>
 */
class AttemptQuestionSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_attempt_id' => \App\Models\ExamAttempt::factory(),
            'question_version_id' => 1, // Assume created elsewhere or seeded
            'order' => 1,
            'question_type' => 'multiple_choice',
            'content' => 'Question text',
        ];
    }
}

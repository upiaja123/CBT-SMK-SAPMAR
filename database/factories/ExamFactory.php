<?php

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'subject_id' => \App\Models\Subject::factory(),
            'description' => $this->faker->paragraph,
            'status' => 'OPEN',
            'duration' => 60,
            'start_at' => now()->subMinutes(30),
            'end_at' => now()->addMinutes(30),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}

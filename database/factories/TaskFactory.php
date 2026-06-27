<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => \App\Models\Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['todo', 'todo', 'in_progress', 'review', 'done']),
            'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high', 'urgent']),
            'assignee_id' => \App\Models\User::factory(),
            'creator_id' => \App\Models\User::factory(),
            'due_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+2 months') : null,
        ];
    }
}

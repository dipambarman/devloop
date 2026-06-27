<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company() . ' ' . fake()->word();
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['active', 'active', 'active', 'completed', 'archived']),
            'color' => fake()->hexColor(),
            'github_repo' => 'devloop/' . \Illuminate\Support\Str::slug($name),
            'owner_id' => \App\Models\User::factory(),
        ];
    }
}

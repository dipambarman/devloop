<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement([ProjectStatus::Active, ProjectStatus::Active, ProjectStatus::OnHold, ProjectStatus::Archived]),
            'color' => fake()->hexColor(),
            'github_repo' => 'devloop/' . Str::slug($name),
            'owner_id' => User::factory(),
        ];
    }
}

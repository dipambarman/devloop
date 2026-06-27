<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $mainUser = User::factory()->create([
            'name' => 'Dipam Barman',
            'email' => 'admin@devloop.app',
            'password' => bcrypt('cringe[]')
        ]);

        $team = User::factory(5)->create();

        // Create tags
        $tags = collect([
            ['name' => 'Bug', 'color' => '#EF4444'],
            ['name' => 'Feature', 'color' => '#8B5CF6'],
            ['name' => 'Enhancement', 'color' => '#06B6D4'],
            ['name' => 'Documentation', 'color' => '#F59E0B'],
            ['name' => 'Hotfix', 'color' => '#F97316'],
            ['name' => 'Refactor', 'color' => '#10B981'],
            ['name' => 'Testing', 'color' => '#EC4899'],
            ['name' => 'DevOps', 'color' => '#6366F1'],
        ])->map(fn ($tag) => \App\Models\Tag::create($tag));

        // Create 3 projects for main user
        $projects = \App\Models\Project::factory(3)->create([
            'owner_id' => $mainUser->id
        ]);

        // Add 10-20 tasks to each project
        foreach ($projects as $project) {
            $tasks = \App\Models\Task::factory(random_int(10, 20))->create([
                'project_id' => $project->id,
                'creator_id' => $mainUser->id,
                // Assign randomly to either main user or team members
                'assignee_id' => fake()->boolean(70) 
                    ? (fake()->boolean(30) ? $mainUser->id : $team->random()->id) 
                    : null
            ]);

            // Attach 0-3 random tags to each task
            foreach ($tasks as $task) {
                $task->tags()->attach(
                    $tags->random(random_int(0, 3))->pluck('id')
                );
            }

            // Add some comments
            foreach ($tasks->random(min(5, $tasks->count())) as $task) {
                \App\Models\Comment::create([
                    'task_id' => $task->id,
                    'user_id' => fake()->boolean(50) ? $mainUser->id : $team->random()->id,
                    'content' => fake()->sentence(random_int(5, 20)),
                ]);
            }
        }
    }
}

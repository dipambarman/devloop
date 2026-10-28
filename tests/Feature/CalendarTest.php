<?php

use App\Enums\TaskPriority;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('unauthenticated users are redirected from calendar to login', function () {
    $this->get(route('calendar.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view calendar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('calendar.index'))
        ->assertStatus(200)
        ->assertSee('Task Calendar');
});

test('calendar displays tasks with due dates in the current viewing month', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $dueDate = now()->startOfMonth()->addDays(5);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Deliver Beta Build',
        'due_date' => $dueDate->format('Y-m-d'),
    ]);

    $response = $this->actingAs($user)
        ->get(route('calendar.index', ['month' => $dueDate->month, 'year' => $dueDate->year]));

    $response->assertStatus(200);
    $response->assertSee('Deliver Beta Build');
});

test('tasks without due dates do not appear on calendar grid', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Unscheduled Backlog Item',
        'due_date' => null,
    ]);

    $response = $this->actingAs($user)
        ->get(route('calendar.index'));

    $response->assertStatus(200);
    $response->assertDontSee('Unscheduled Backlog Item');
});

test('calendar isolates private tasks from other users projects', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $myProject = Project::factory()->create(['owner_id' => $user->id]);
    $secretProject = Project::factory()->create(['owner_id' => $otherUser->id]);

    $date = now()->format('Y-m-d');

    $myTask = Task::factory()->create([
        'project_id' => $myProject->id,
        'creator_id' => $user->id,
        'title' => 'My Public Roadmap Task',
        'due_date' => $date,
    ]);

    $secretTask = Task::factory()->create([
        'project_id' => $secretProject->id,
        'creator_id' => $otherUser->id,
        'title' => 'Secret Competitor Research Task',
        'due_date' => $date,
    ]);

    $response = $this->actingAs($user)->get(route('calendar.index'));

    $response->assertStatus(200);
    $response->assertSee('My Public Roadmap Task');
    $response->assertDontSee('Secret Competitor Research Task');
});

test('user can navigate to past or future months on calendar', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    // Task due in December 2026
    $futureDate = '2026-12-15';
    $futureTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'End of Year Release',
        'due_date' => $futureDate,
    ]);

    $response = $this->actingAs($user)
        ->get(route('calendar.index', ['month' => 12, 'year' => 2026]));

    $response->assertStatus(200);
    $response->assertSee('December 2026');
    $response->assertSee('End of Year Release');
});

test('calendar can be filtered by priority', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $date = now()->format('Y-m-d');

    $urgentTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Critical Hotfix Deployment',
        'priority' => TaskPriority::Urgent->value,
        'due_date' => $date,
    ]);

    $lowTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Minor CSS Polish',
        'priority' => TaskPriority::Low->value,
        'due_date' => $date,
    ]);

    $response = $this->actingAs($user)
        ->get(route('calendar.index', ['priority' => TaskPriority::Urgent->value]));

    $response->assertStatus(200);
    $response->assertSee('Critical Hotfix Deployment');
    $response->assertDontSee('Minor CSS Polish');
});

test('user can view project-scoped calendar', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id, 'name' => 'Mobile App']);

    $response = $this->actingAs($user)
        ->get(route('projects.calendar', $project));

    $response->assertStatus(200);
    $response->assertSee('Mobile App — Calendar');
});

test('user cannot view project calendar for an inaccessible project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $secretProject = Project::factory()->create(['owner_id' => $otherUser->id]);

    $response = $this->actingAs($user)
        ->get(route('projects.calendar', $secretProject));

    $response->assertStatus(403);
});

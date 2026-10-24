<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;

class OnboardingService
{
    /**
     * Set up a personalized starter workspace for a newly registered user.
     * Ensures new users are greeted with interactive demo data and clear guidance.
     */
    public function setupForNewUser(User $user): void
    {
        // 1. Ensure essential global tags exist
        $tags = [
            'Feature' => '#8B5CF6',
            'Bug' => '#EF4444',
            'Enhancement' => '#06B6D4',
            'Documentation' => '#F59E0B',
            'DevOps' => '#6366F1',
        ];

        $tagModels = [];
        foreach ($tags as $name => $color) {
            $tagModels[$name] = Tag::firstOrCreate(
                ['name' => $name],
                ['color' => $color]
            );
        }

        // 2. Create the interactive Guided Starter Project
        $project = $user->projects()->create([
            'name' => '🚀 Welcome to DevLoop',
            'slug' => 'welcome-to-devloop-' . $user->id . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6)),
            'description' => 'Your interactive onboarding project. Test the Kanban board, explore task status workflows, write markdown notes, and manage code snippets.',
            'status' => ProjectStatus::Active,
            'color' => '#6366F1',
            'github_repo' => 'dipambarman/devloop',
        ]);

        // 3. Create starter tasks showcasing the Kanban workflow
        $tasksData = [
            [
                'title' => '👋 Complete your developer profile',
                'description' => "Welcome to DevLoop! Head over to your profile settings to add your bio, skills, and links to your GitHub or LinkedIn.",
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::Medium,
                'tag' => 'Documentation',
                'order' => 1,
            ],
            [
                'title' => '🎯 Test the Kanban board (drag this card)',
                'description' => "Drag this card across columns (In Progress → Review → Done) to test real-time reordering powered by SortableJS.",
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::Urgent,
                'tag' => 'Feature',
                'order' => 1,
            ],
            [
                'title' => '📝 Explore Developer Markdown Notes',
                'description' => "DevLoop features GitHub-flavored markdown notes with instant preview, tag pinning, and project linking. Check the Notes section in the top menu.",
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::High,
                'tag' => 'Enhancement',
                'order' => 1,
            ],
            [
                'title' => '⚡ Save your first reusable code snippet',
                'description' => "Keep your frequently used queries, terminal aliases, and boilerplates syntax-highlighted in the Snippets vault.",
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Medium,
                'tag' => 'DevOps',
                'order' => 2,
            ],
            [
                'title' => '👥 Invite a team member or collaborator',
                'description' => "DevLoop makes teamwork seamless. Invite collaborators by email and assign them either Member or Viewer permissions.",
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Low,
                'tag' => 'Feature',
                'order' => 3,
            ],
        ];

        foreach ($tasksData as $data) {
            $task = $project->tasks()->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'creator_id' => $user->id,
                'assignee_id' => $user->id,
                'order_column' => $data['order'],
                'due_date' => now()->addDays(7),
            ]);

            if (isset($tagModels[$data['tag']])) {
                $task->tags()->attach($tagModels[$data['tag']]->id);
            }
        }

        // 4. Create an informative starter Markdown Note
        $user->notes()->create([
            'project_id' => $project->id,
            'title' => '💡 DevLoop Workspace Guide & Markdown Tips',
            'content' => "## Welcome to your DevLoop Workspace!\n\nDevLoop brings your developer tasks, architecture decisions, and code snippets into a single unified cosmic dark interface.\n\n### Key Features at your fingertips:\n- **Kanban Boards**: Drag cards smoothly between Todo, In Progress, Review, and Done.\n- **Discussions**: Start threaded architectural conversations on any project.\n- **Notes Vault**: Write with standard Markdown (`code blocks`, quotes, tables, lists).\n- **Code Snippets**: Syntax-highlighted storage with language tagging.\n\n```bash\n# Pro-tip: Run tests locally\nphp artisan test\n```\n\n> \"Simplicity is prerequisite for reliability.\" — Edsger W. Dijkstra\n\nEnjoy building! 🚀",
            'is_pinned' => true,
        ]);

        // 5. Create a starter code snippet
        $user->snippets()->create([
            'project_id' => $project->id,
            'title' => '⚡ Helpful Artisan & Docker Commands',
            'language' => 'bash',
            'code' => "# Run local test suite\nphp artisan test\n\n# Optimize production caches\nphp artisan config:cache\nphp artisan route:cache\nphp artisan view:cache\n\n# Start Docker multi-container stack\ndocker compose up -d",
        ]);
    }
}

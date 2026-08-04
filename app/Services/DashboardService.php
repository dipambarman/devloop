<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Get dashboard statistics for the authenticated user.
     */
    public function getStats(User $user): array
    {
        $allProjectIds = $user->allProjects()->pluck('id');

        // Count unique team members across all accessible projects
        $teamMemberCount = \DB::table('project_user')
            ->whereIn('project_id', $allProjectIds)
            ->distinct('user_id')
            ->count('user_id');
        // Include the user themselves
        $teamMemberCount = max($teamMemberCount + 1, 1);

        return [
            'total_projects' => $user->allProjects()->count(),
            'active_tasks' => Task::whereIn('project_id', $allProjectIds)
                ->whereNotIn('status', [TaskStatus::Done])
                ->count(),
            'team_members' => $teamMemberCount,
            'completed_tasks' => Task::whereIn('project_id', $allProjectIds)
                ->where('status', TaskStatus::Done)
                ->count(),
        ];
    }

    /**
     * Get the user's recent projects.
     */
    public function getRecentProjects(User $user, int $limit = 5)
    {
        return $user->allProjects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', TaskStatus::Done);
            }])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get recent tasks across the user's projects.
     */
    public function getRecentTasks(User $user, int $limit = 5)
    {
        return Task::whereIn('project_id', $user->allProjects()->pluck('id'))
            ->with(['project', 'assignee'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get task distribution by status across the user's projects.
     */
    public function getTaskDistribution(User $user): array
    {
        $projectIds = $user->allProjects()->pluck('id');

        return [
            'todo' => Task::whereIn('project_id', $projectIds)->where('status', TaskStatus::Todo)->count(),
            'in_progress' => Task::whereIn('project_id', $projectIds)->where('status', TaskStatus::InProgress)->count(),
            'review' => Task::whereIn('project_id', $projectIds)->where('status', TaskStatus::Review)->count(),
            'done' => Task::whereIn('project_id', $projectIds)->where('status', TaskStatus::Done)->count(),
        ];
    }
}

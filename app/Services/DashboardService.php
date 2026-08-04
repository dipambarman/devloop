<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get all data required for the user dashboard in a single optimized pass.
     */
    public function getDashboardData(User $user): array
    {
        $projectIds = $user->allProjects()->pluck('id');

        return [
            'stats' => $this->getStats($user, $projectIds),
            'recentProjects' => $this->getRecentProjects($user),
            'recentTasks' => $this->getRecentTasks($projectIds),
            'taskDistribution' => $this->getTaskDistribution($projectIds),
        ];
    }

    /**
     * Get dashboard statistics.
     */
    public function getStats(User $user, ?Collection $projectIds = null): array
    {
        $projectIds = $projectIds ?? $user->allProjects()->pluck('id');

        $teamMemberCount = \DB::table('project_user')
            ->whereIn('project_id', $projectIds)
            ->distinct('user_id')
            ->count('user_id');

        return [
            'total_projects' => $user->allProjects()->count(),
            'active_tasks' => Task::whereIn('project_id', $projectIds)
                ->whereNotIn('status', [TaskStatus::Done])
                ->count(),
            'team_members' => max($teamMemberCount + 1, 1),
            'completed_tasks' => Task::whereIn('project_id', $projectIds)
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
    public function getRecentTasks(?Collection $projectIds = null, int $limit = 5)
    {
        if (!$projectIds && auth()->check()) {
            $projectIds = auth()->user()->allProjects()->pluck('id');
        }

        return Task::whereIn('project_id', $projectIds ?? [])
            ->with(['project', 'assignee'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get task distribution by status across the user's projects.
     */
    public function getTaskDistribution(?Collection $projectIds = null): array
    {
        if (!$projectIds && auth()->check()) {
            $projectIds = auth()->user()->allProjects()->pluck('id');
        }

        return [
            'todo' => Task::whereIn('project_id', $projectIds ?? [])->where('status', TaskStatus::Todo)->count(),
            'in_progress' => Task::whereIn('project_id', $projectIds ?? [])->where('status', TaskStatus::InProgress)->count(),
            'review' => Task::whereIn('project_id', $projectIds ?? [])->where('status', TaskStatus::Review)->count(),
            'done' => Task::whereIn('project_id', $projectIds ?? [])->where('status', TaskStatus::Done)->count(),
        ];
    }
}

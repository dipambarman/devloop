<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TimeLogController extends Controller
{
    /**
     * Store a work time log on a task.
     */
    public function store(Request $request, Project $project, Task $task): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to log time on this project.');
        abort_if($task->project_id !== $project->id, 404);

        $validated = $request->validate([
            'hours' => ['nullable', 'integer', 'min:0', 'max:24'],
            'minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'description' => ['nullable', 'string', 'max:500'],
            'logged_at' => ['nullable', 'date'],
        ]);

        $hours = (int) ($validated['hours'] ?? 0);
        $minutes = (int) ($validated['minutes'] ?? 0);
        $totalMinutes = ($hours * 60) + $minutes;

        if ($totalMinutes <= 0) {
            return back()->withErrors(['duration' => 'Please enter at least 1 minute of logged time.']);
        }

        $task->timeLogs()->create([
            'user_id' => auth()->id(),
            'duration_minutes' => $totalMinutes,
            'description' => $validated['description'] ?? null,
            'logged_at' => $validated['logged_at'] ?? now()->toDateString(),
        ]);

        return back()->with('success', "Logged {$totalMinutes} minutes on task #{$task->id}.");
    }

    /**
     * Delete a work time log.
     */
    public function destroy(Project $project, Task $task, TimeLog $timeLog): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_if($task->project_id !== $project->id, 404);
        abort_if($timeLog->task_id !== $task->id, 404);

        // Only author or project owner can delete
        $canDelete = $timeLog->user_id === auth()->id() || $project->owner_id === auth()->id();
        abort_unless($canDelete, 403, 'You can only delete your own time logs.');

        $timeLog->delete();

        return back()->with('success', 'Time log deleted.');
    }

    /**
     * Display a comprehensive team time report for the project.
     */
    public function report(Project $project): View
    {
        Gate::authorize('view', $project);

        $timeLogs = TimeLog::whereHas('task', fn ($q) => $q->where('project_id', $project->id))
            ->with(['user', 'task'])
            ->latest('logged_at')
            ->latest('id')
            ->paginate(25);

        // Summary metrics by user
        $userSummaries = TimeLog::whereHas('task', fn ($q) => $q->where('project_id', $project->id))
            ->join('users', 'time_logs.user_id', '=', 'users.id')
            ->selectRaw('users.id, users.name, SUM(time_logs.duration_minutes) as total_minutes')
            ->groupBy('users.id', 'users.name')
            ->get();

        $totalMinutes = $userSummaries->sum('total_minutes');

        return view('projects.time-report', compact('project', 'timeLogs', 'userSummaries', 'totalMinutes'));
    }
}

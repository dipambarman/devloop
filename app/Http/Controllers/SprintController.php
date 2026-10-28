<?php

namespace App\Http\Controllers;

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SprintController extends Controller
{
    /**
     * Display the Sprint Planning dashboard with active sprint, planned sprints, and backlog.
     */
    public function index(Project $project): View
    {
        Gate::authorize('view', $project);

        $activeSprint = $project->sprints()
            ->where('status', SprintStatus::Active)
            ->with(['tasks.assignee', 'tasks.tags'])
            ->first();

        $planningSprints = $project->sprints()
            ->where('status', SprintStatus::Planning)
            ->with(['tasks.assignee', 'tasks.tags'])
            ->orderBy('created_at', 'asc')
            ->get();

        $completedSprints = $project->sprints()
            ->where('status', SprintStatus::Completed)
            ->withCount('tasks')
            ->latest('updated_at')
            ->take(10)
            ->get();

        $backlogTasks = $project->tasks()
            ->whereNull('sprint_id')
            ->with(['assignee', 'tags'])
            ->latest('order_column')
            ->latest()
            ->get();

        return view('sprints.index', compact(
            'project',
            'activeSprint',
            'planningSprints',
            'completedSprints',
            'backlogTasks'
        ));
    }

    /**
     * Store a newly created sprint in planning status.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to create sprints.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'goal' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project->sprints()->create([
            'name' => $validated['name'],
            'goal' => $validated['goal'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => SprintStatus::Planning,
        ]);

        return redirect()->route('projects.sprints.index', $project)
            ->with('success', "Sprint '{$validated['name']}' created successfully.");
    }

    /**
     * Update sprint details (name, goal, dates).
     */
    public function update(Request $request, Project $project, Sprint $sprint): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to edit sprints.');
        abort_if($sprint->project_id !== $project->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'goal' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $sprint->update($validated);

        return redirect()->route('projects.sprints.index', $project)
            ->with('success', "Sprint '{$sprint->name}' updated.");
    }

    /**
     * Start/activate a planning sprint.
     */
    public function start(Request $request, Project $project, Sprint $sprint): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to start sprints.');
        abort_if($sprint->project_id !== $project->id, 404);

        // Ensure no other sprint is currently active in this project
        $existingActive = $project->sprints()->where('status', SprintStatus::Active)->first();
        if ($existingActive && $existingActive->id !== $sprint->id) {
            return back()->with('error', "Sprint '{$existingActive->name}' is currently active. Please complete it before starting a new sprint.");
        }

        $sprint->status = SprintStatus::Active;
        if (!$sprint->start_date) {
            $sprint->start_date = now()->startOfDay();
        }
        $sprint->save();

        return redirect()->route('projects.sprints.index', $project)
            ->with('success', "Sprint '{$sprint->name}' is now active!");
    }

    /**
     * Complete an active sprint and rollover uncompleted tasks.
     */
    public function complete(Request $request, Project $project, Sprint $sprint): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to complete sprints.');
        abort_if($sprint->project_id !== $project->id, 404);

        $validated = $request->validate([
            'rollover_action' => ['nullable', 'string', 'in:backlog,next_sprint'],
            'target_sprint_id' => ['nullable', 'exists:sprints,id'],
        ]);

        $rolloverAction = $validated['rollover_action'] ?? 'backlog';
        $targetSprintId = $validated['target_sprint_id'] ?? null;

        // Roll over incomplete tasks
        $incompleteTasks = $sprint->tasks()->where('status', '!=', TaskStatus::Done)->get();

        if ($incompleteTasks->isNotEmpty()) {
            if ($rolloverAction === 'next_sprint' && $targetSprintId) {
                // Ensure target sprint belongs to the same project
                $targetSprint = $project->sprints()->findOrFail($targetSprintId);
                $incompleteTasks->each(fn(Task $task) => $task->update(['sprint_id' => $targetSprint->id]));
            } else {
                // Return to backlog
                $incompleteTasks->each(fn(Task $task) => $task->update(['sprint_id' => null]));
            }
        }

        $sprint->status = SprintStatus::Completed;
        if (!$sprint->end_date) {
            $sprint->end_date = now()->startOfDay();
        }
        $sprint->save();

        return redirect()->route('projects.sprints.index', $project)
            ->with('success', "Sprint '{$sprint->name}' completed. Incomplete tasks moved to " . ($rolloverAction === 'next_sprint' ? 'next sprint' : 'backlog') . ".");
    }

    /**
     * Assign a task to a sprint or return it to the backlog.
     */
    public function assignTask(Request $request, Project $project, Task $task): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to modify task sprints.');
        abort_if($task->project_id !== $project->id, 404);

        $validated = $request->validate([
            'sprint_id' => ['nullable', 'exists:sprints,id'],
        ]);

        if (!empty($validated['sprint_id'])) {
            $targetSprint = $project->sprints()->findOrFail($validated['sprint_id']);
            $task->sprint_id = $targetSprint->id;
        } else {
            $task->sprint_id = null;
        }

        $task->save();

        return back()->with('success', 'Task sprint allocation updated.');
    }

    /**
     * Delete a sprint and return its tasks to the backlog.
     */
    public function destroy(Project $project, Sprint $sprint): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to delete sprints.');
        abort_if($sprint->project_id !== $project->id, 404);

        // Move all tasks in this sprint to backlog
        $sprint->tasks()->update(['sprint_id' => null]);
        $sprint->delete();

        return redirect()->route('projects.sprints.index', $project)
            ->with('success', "Sprint '{$sprint->name}' deleted and tasks moved to backlog.");
    }
}

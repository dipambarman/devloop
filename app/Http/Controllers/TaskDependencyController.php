<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\TaskDependencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskDependencyController extends Controller
{
    public function __construct(
        protected TaskDependencyService $dependencyService
    ) {}

    /**
     * Add a prerequisite blocker dependency to a task.
     */
    public function store(Request $request, Project $project, Task $task): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to manage task dependencies.');
        abort_if($task->project_id !== $project->id, 404);

        $validated = $request->validate([
            'depends_on_task_id' => ['required', 'exists:tasks,id'],
        ]);

        $prerequisiteTask = $project->tasks()->findOrFail($validated['depends_on_task_id']);

        $this->dependencyService->addDependency($task, $prerequisiteTask);

        return back()->with('success', "Prerequisite dependency added: Task #{$prerequisiteTask->id} must be resolved first.");
    }

    /**
     * Remove a prerequisite dependency from a task.
     */
    public function destroy(Project $project, Task $task, Task $dependency): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to manage task dependencies.');
        abort_if($task->project_id !== $project->id, 404);

        $task->dependencies()->detach($dependency->id);

        return back()->with('success', 'Prerequisite dependency removed.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\MilestoneStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MilestoneController extends Controller
{
    /**
     * Display listing of project milestones with completion progress bars.
     */
    public function index(Project $project): View
    {
        Gate::authorize('view', $project);

        $openMilestones = $project->milestones()
            ->where('status', MilestoneStatus::Open)
            ->with(['tasks.assignee', 'tasks.tags'])
            ->orderBy('due_date', 'asc')
            ->get();

        $closedMilestones = $project->milestones()
            ->where('status', MilestoneStatus::Closed)
            ->with(['tasks.assignee', 'tasks.tags'])
            ->latest('updated_at')
            ->get();

        $unassignedTasks = $project->tasks()
            ->whereNull('milestone_id')
            ->with('assignee')
            ->latest()
            ->get();

        return view('milestones.index', compact(
            'project',
            'openMilestones',
            'closedMilestones',
            'unassignedTasks'
        ));
    }

    /**
     * Store a new release milestone.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to create milestones.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->milestones()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'status' => MilestoneStatus::Open,
        ]);

        return redirect()->route('projects.milestones.index', $project)
            ->with('success', "Milestone '{$validated['title']}' created successfully.");
    }

    /**
     * Update milestone metadata.
     */
    public function update(Request $request, Project $project, Milestone $milestone): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to edit milestones.');
        abort_if($milestone->project_id !== $project->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $milestone->update($validated);

        return redirect()->route('projects.milestones.index', $project)
            ->with('success', "Milestone '{$milestone->title}' updated.");
    }

    /**
     * Toggle milestone status between open and closed.
     */
    public function toggleStatus(Project $project, Milestone $milestone): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to change milestone status.');
        abort_if($milestone->project_id !== $project->id, 404);

        $milestone->status = $milestone->isOpen() ? MilestoneStatus::Closed : MilestoneStatus::Open;
        $milestone->save();

        $action = $milestone->isOpen() ? 'reopened' : 'closed';

        return redirect()->route('projects.milestones.index', $project)
            ->with('success', "Milestone '{$milestone->title}' {$action}.");
    }

    /**
     * Assign a task to a milestone or unassign it.
     */
    public function assignTask(Request $request, Project $project, Task $task): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to assign milestones.');
        abort_if($task->project_id !== $project->id, 404);

        $validated = $request->validate([
            'milestone_id' => ['nullable', 'exists:milestones,id'],
        ]);

        if (! empty($validated['milestone_id'])) {
            $milestone = $project->milestones()->findOrFail($validated['milestone_id']);
            $task->milestone_id = $milestone->id;
        } else {
            $task->milestone_id = null;
        }

        $task->save();

        return back()->with('success', 'Task milestone allocation updated.');
    }

    /**
     * Delete milestone and unlink its tasks.
     */
    public function destroy(Project $project, Milestone $milestone): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to delete milestones.');
        abort_if($milestone->project_id !== $project->id, 404);

        $milestone->tasks()->update(['milestone_id' => null]);
        $milestone->delete();

        return redirect()->route('projects.milestones.index', $project)
            ->with('success', "Milestone '{$milestone->title}' deleted and tasks unassigned.");
    }
}

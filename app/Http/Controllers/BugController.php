<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use App\Models\Task;
use App\Services\BugIngestService;
use App\Services\BugStateMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BugController extends Controller
{
    public function __construct(
        protected BugIngestService $ingestService,
        protected BugStateMachine $stateMachine
    ) {}

    /**
     * Display a listing of the project's fingerprinted bugs.
     */
    public function index(Request $request, Project $project): View
    {
        Gate::authorize('view', $project);

        $query = $project->bugs()->latest('last_seen_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('fingerprint', 'like', "%{$search}%")
                    ->orWhere('raw_message', 'like', "%{$search}%");
            });
        }

        $bugs = $query->with('assignee')->paginate(15)->withQueryString();

        // Calculate project-level error statistics
        $stats = [
            'total' => $project->bugs()->count(),
            'open' => $project->bugs()->where('status', 'open')->count(),
            'in_progress' => $project->bugs()->where('status', 'in_progress')->count(),
            'fixed' => $project->bugs()->where('status', 'fixed')->count(),
            'regressions' => $project->bugs()->sum('regressions'),
            'total_occurrences' => $project->bugs()->sum('occurrence_count'),
        ];

        return view('bugs.index', compact('project', 'bugs', 'stats'));
    }

    /**
     * Display the specified bug details and immutable timeline.
     */
    public function show(Project $project, Bug $bug): View
    {
        Gate::authorize('view', $project);
        abort_if($bug->project_id !== $project->id, 404);

        $bug->load(['assignee', 'events.user']);

        return view('bugs.show', compact('project', 'bug'));
    }

    /**
     * Manually ingest an error via the web UI modal.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to manually ingest bugs into this project.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'stack' => ['nullable', 'string', 'max:50000'],
            'severity' => ['nullable', 'string', 'in:low,medium,high,critical'],
        ]);

        $bug = $this->ingestService->ingest(
            project: $project,
            message: $validated['message'],
            stack: $validated['stack'] ?? null,
            severity: $validated['severity'] ?? 'medium'
        );

        return redirect()->route('projects.bugs.show', [$project, $bug])
            ->with('success', 'Error ingested and fingerprinted successfully!');
    }

    /**
     * Transition bug status using the state machine with optimistic locking.
     */
    public function updateStatus(Request $request, Project $project, Bug $bug): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to update bug status in this project.');
        abort_if($bug->project_id !== $project->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,fixed,wontfix'],
            'version' => ['nullable', 'integer'],
        ]);

        $this->stateMachine->transition(
            bug: $bug,
            toStatus: $validated['status'],
            user: auth()->user(),
            clientVersion: isset($validated['version']) ? (int) $validated['version'] : null
        );

        return back()->with('success', "Bug status transitioned to '{$validated['status']}'.");
    }

    /**
     * Convert this recurrent bug into an actionable Kanban Task.
     */
    public function convertToTask(Request $request, Project $project, Bug $bug): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'You do not have permission to convert bugs to tasks in this project.');
        abort_if($bug->project_id !== $project->id, 404);

        $priority = match ($bug->severity) {
            'critical', 'high' => 'high',
            'medium' => 'medium',
            default => 'low',
        };

        $task = Task::create([
            'project_id' => $project->id,
            'title' => "Fix [{$bug->error_type}]: ".mb_substr($bug->title, 0, 80),
            'description' => "### Root Cause Investigation\n\n"
                ."**Fingerprint:** `{$bug->fingerprint}`\n"
                ."**Occurrences:** {$bug->occurrence_count}\n"
                ."**Regressions:** {$bug->regressions}\n"
                .'**Last Seen:** '.($bug->last_seen_at?->toDayDateTimeString() ?? 'Unknown')."\n\n"
                ."#### Error Message:\n```\n{$bug->raw_message}\n```\n\n"
                ."#### Stack Trace:\n```\n{$bug->raw_stack}\n```",
            'priority' => $priority,
            'status' => 'todo',
            'creator_id' => auth()->id(),
            'assignee_id' => $bug->assignee_id,
        ]);

        // Transition bug status to in_progress if currently open
        if ($bug->status === 'open') {
            $this->stateMachine->transition($bug, 'in_progress', auth()->user());
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', "Task #{$task->id} created from Bug #{$bug->id}!");
    }

    /**
     * Remove the specified bug.
     */
    public function destroy(Project $project, Bug $bug): RedirectResponse
    {
        Gate::authorize('update', $project);
        abort_if($bug->project_id !== $project->id, 404);

        $bug->delete();

        return redirect()->route('projects.bugs.index', $project)
            ->with('success', 'Bug deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\ProjectRole;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = auth()->user()->allProjects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', TaskStatus::Done);
            }])
            ->latest()
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $project = auth()->user()->projects()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'color' => $request->color ?? '#6366F1',
            'github_repo' => $request->github_repo,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $project->loadCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
            $query->where('status', TaskStatus::Done);
        }]);

        $project->load(['members', 'owner']);

        $tasksQuery = $project->tasks()
            ->with('assignee');

        if ($request->filled('search')) {
            $search = $request->search;
            $tasksQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $tasksQuery->where('status', $request->status);
        }

        $priorityFilter = $request->input('priority') ?? $request->input('severity');
        if (!empty($priorityFilter)) {
            $tasksQuery->where('priority', $priorityFilter);
        }

        $tasks = $tasksQuery->latest()->paginate(15)->withQueryString();

        $activities = $project->activityLogs()
            ->with('user', 'subject')
            ->latest()
            ->take(20)
            ->get();

        return view('projects.show', compact('project', 'tasks', 'activities'));
    }

    /**
     * Display the Kanban board for the project with sprint filtering.
     */
    public function board(\Illuminate\Http\Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $sprintFilter = $request->query('sprint', 'all');
        $sprints = $project->sprints()->latest('created_at')->get();
        $activeSprint = $project->sprints()->where('status', \App\Enums\SprintStatus::Active)->first();

        $query = $project->tasks()->with(['assignee', 'tags', 'sprint'])->withCount('comments')->orderBy('order_column')->latest();

        if ($sprintFilter === 'active') {
            if ($activeSprint) {
                $query->where('sprint_id', $activeSprint->id);
            }
        } elseif ($sprintFilter === 'backlog') {
            $query->whereNull('sprint_id');
        } elseif (is_numeric($sprintFilter)) {
            $query->where('sprint_id', (int) $sprintFilter);
        }

        $tasks = $query->get();

        // Group tasks by status
        $tasksByStatus = [
            'todo' => $tasks->where('status', TaskStatus::Todo)->values(),
            'in_progress' => $tasks->where('status', TaskStatus::InProgress)->values(),
            'review' => $tasks->where('status', TaskStatus::Review)->values(),
            'done' => $tasks->where('status', TaskStatus::Done)->values(),
        ];

        return view('projects.board', compact('project', 'tasksByStatus', 'sprints', 'activeSprint', 'sprintFilter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        Gate::authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $project->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'status' => $request->status ?? $project->status,
            'color' => $request->color ?? $project->color,
            'github_repo' => $request->github_repo,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully!');
    }

    /**
     * Add a member to the project by email.
     */
    public function addMember(Request $request, Project $project)
    {
        Gate::authorize('manageMembers', $project);

        $request->validate([
            'email' => 'required|string',
            'role' => ['required', Rule::enum(ProjectRole::class)],
        ]);

        $identifier = trim($request->email);
        $cleanUsername = ltrim($identifier, '@');

        $user = User::where('email', $identifier)
            ->orWhere('github_username', $cleanUsername)
            ->first();

        if (!$user) {
            $errorMessage = (str_contains($identifier, '@') && str_contains($identifier, '.'))
                ? 'No user found with that email address.'
                : "No user found with email or GitHub username '{$identifier}'.";

            return redirect()->back()->with('error', $errorMessage);
        }

        if ($user->id === $project->owner_id) {
            return redirect()->back()->with('error', 'The project owner is already a member.');
        }

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('error', 'This user is already a member of this project.');
        }

        $role = ProjectRole::from($request->role);
        $project->members()->attach($user->id, ['role' => $role->value]);

        \App\Events\MemberAdded::dispatch($project, auth()->user(), $user, $role);

        return redirect()->back()->with('success', "{$user->name} has been added to the project!");
    }

    /**
     * Remove a member from the project.
     */
    public function removeMember(Project $project, User $user)
    {
        Gate::authorize('manageMembers', $project);

        if ($user->id === $project->owner_id) {
            return redirect()->back()->with('error', 'Cannot remove the project owner.');
        }

        $project->members()->detach($user->id);

        \App\Events\MemberRemoved::dispatch($project, auth()->user(), $user);

        return redirect()->back()->with('success', "{$user->name} has been removed from the project.");
    }
}


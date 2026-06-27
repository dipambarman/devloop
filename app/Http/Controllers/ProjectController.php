<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = auth()->user()->allProjects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'done');
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
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        $project->loadCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
            $query->where('status', 'done');
        }]);

        $project->load(['members', 'owner']);

        $tasks = $project->tasks()
            ->with('assignee')
            ->latest()
            ->paginate(15);

        $activities = $project->activityLogs()
            ->with('user', 'subject')
            ->latest()
            ->take(20)
            ->get();

        return view('projects.show', compact('project', 'tasks', 'activities'));
    }

    /**
     * Display the Kanban board for the project.
     */
    public function board(Project $project)
    {
        Gate::authorize('view', $project);

        $project->load(['tasks' => function ($query) {
            $query->with(['assignee', 'tags'])->withCount('comments')->orderBy('order_column')->latest();
        }]);

        // Group tasks by status
        $tasksByStatus = [
            'todo' => $project->tasks->where('status', 'todo')->values(),
            'in_progress' => $project->tasks->where('status', 'in_progress')->values(),
            'review' => $project->tasks->where('status', 'review')->values(),
            'done' => $project->tasks->where('status', 'done')->values(),
        ];

        return view('projects.board', compact('project', 'tasksByStatus'));
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
            'email' => 'required|email',
            'role' => 'required|in:member,viewer',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'No user found with that email address.');
        }

        if ($user->id === $project->owner_id) {
            return redirect()->back()->with('error', 'The project owner is already a member.');
        }

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('error', 'This user is already a member of this project.');
        }

        $project->members()->attach($user->id, ['role' => $request->role]);

        \App\Events\MemberAdded::dispatch($project, auth()->user(), $user, $request->role);

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


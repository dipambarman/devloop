<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Tag;
use App\Models\User;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Task::where(function ($q) {
            $q->whereHas('project', function ($pq) {
                $pq->where('owner_id', auth()->id());
            })->orWhere('assignee_id', auth()->id());
        })->with(['assignee', 'project', 'tags']);

        // Filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->latest()->paginate(15);
        $projects = auth()->user()->projects()->where('status', ProjectStatus::Active)->get();

        return view('tasks.index', compact('tasks', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(\Illuminate\Http\Request $request)
    {
        $projects = auth()->user()->allProjects()->where('status', ProjectStatus::Active)->get();
        $selectedProject = $request->query('project_id');
        $users = collect();

        if ($selectedProject) {
            $project = \App\Models\Project::find($selectedProject);
            if ($project && $project->isAccessibleBy(auth()->user())) {
                $users = $project->members()->get()->push($project->owner)->unique('id');
            }
        }

        $tags = Tag::all();

        return view('tasks.create', compact('projects', 'users', 'tags', 'selectedProject'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? TaskStatus::Todo,
            'priority' => $request->priority ?? TaskPriority::Medium,
            'assignee_id' => $request->assignee_id,
            'creator_id' => auth()->id(),
            'due_date' => $request->due_date,
        ]);

        if ($request->has('tags')) {
            $task->tags()->sync($request->tags);
        }

        \App\Events\TaskCreated::dispatch($task->project, auth()->user(), $task);

        return redirect()->route('projects.show', $task->project)
            ->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        $task->load(['project', 'assignee', 'creator', 'tags', 'comments.user']);
        $projectMembers = $task->project->members()->get()->push($task->project->owner)->unique('id');

        return view('tasks.show', compact('task', 'projectMembers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        Gate::authorize('update', $task);

        $task->load(['project', 'tags']);
        $users = $task->project->members()->get()->push($task->project->owner)->unique('id');
        $tags = Tag::all();

        return view('tasks.edit', compact('task', 'users', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        $oldStatus = $task->status;
        $oldPriority = $task->priority;
        $oldAssigneeId = $task->assignee_id;
        $oldAssignee = $task->assignee; // Load the model before it changes

        $task->update($request->only([
            'title', 'description', 'status', 'priority', 'assignee_id', 'due_date'
        ]));

        if ($request->has('tags')) {
            $task->tags()->sync($request->tags);
        }

        if ($oldStatus !== $task->status) {
            \App\Events\TaskStatusChanged::dispatch($task->project, auth()->user(), $task, $oldStatus, $task->status);
        }
        
        if ($oldPriority !== $task->priority) {
            \App\Events\TaskPriorityChanged::dispatch($task->project, auth()->user(), $task, $oldPriority, $task->priority);
        }

        if ($oldAssigneeId !== $task->assignee_id) {
            \App\Events\TaskAssigneeChanged::dispatch($task->project, auth()->user(), $task, $oldAssignee, $task->assignee);
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        $project = $task->project;
        $task->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Task deleted successfully!');
    }

    /**
     * Update the task status and order via AJAX (Kanban board).
     */
    public function updateStatus(\Illuminate\Http\Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate([
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'order' => 'array'
        ]);

        $oldStatus = $task->status;

        $task->update([
            'status' => $request->status
        ]);

        if ($oldStatus !== $task->status) {
            \App\Events\TaskStatusChanged::dispatch($task->project, auth()->user(), $task, $oldStatus, $task->status);
        }

        if ($request->has('order')) {
            $projectTaskIds = $task->project->tasks()->pluck('id')->toArray();
            foreach ($request->order as $index => $taskId) {
                if (in_array($taskId, $projectTaskIds)) {
                    Task::where('id', $taskId)->update(['order_column' => $index]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update the task status inline.
     */
    public function updateStatusInline(\Illuminate\Http\Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate(['status' => ['required', Rule::enum(TaskStatus::class)]]);

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        if ($oldStatus !== $task->status) {
            \App\Events\TaskStatusChanged::dispatch($task->project, auth()->user(), $task, $oldStatus, $task->status);
        }

        return redirect()->back()->with('success', 'Status updated.');
    }

    /**
     * Update the task priority inline.
     */
    public function updatePriorityInline(\Illuminate\Http\Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate(['priority' => ['required', Rule::enum(TaskPriority::class)]]);

        $oldPriority = $task->priority;
        $task->update(['priority' => $request->priority]);

        if ($oldPriority !== $task->priority) {
            \App\Events\TaskPriorityChanged::dispatch($task->project, auth()->user(), $task, $oldPriority, $task->priority);
        }

        return redirect()->back()->with('success', 'Priority updated.');
    }

    /**
     * Update the task assignee inline.
     */
    public function updateAssigneeInline(\Illuminate\Http\Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate(['assignee_id' => 'nullable|exists:users,id']);

        $oldAssigneeId = $task->assignee_id;
        $oldAssignee = $task->assignee;

        $task->update(['assignee_id' => $request->assignee_id]);

        if ($oldAssigneeId != $task->assignee_id) { // intentional loose comparison to handle null vs empty string
            \App\Events\TaskAssigneeChanged::dispatch($task->project, auth()->user(), $task, $oldAssignee, $task->fresh()->assignee);
        }

        return redirect()->back()->with('success', 'Assignee updated.');
    }
}

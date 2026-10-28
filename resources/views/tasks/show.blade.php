<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $task->project) }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>

            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">{{ $task->title }}</h2>
                    @php
                        $statusColors = ['todo' => 'gray', 'in_progress' => 'primary', 'review' => 'warning', 'done' => 'success'];
                        $statusLabels = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'];
                        $priorityColors = ['low' => 'gray', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                    @endphp
                    <form action="{{ route('tasks.updateStatusInline', $task) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="text-xs font-medium px-2.5 py-1 rounded-full border-0 focus:ring-2 focus:ring-primary cursor-pointer bg-surface border border-border text-primary-text">
                            @foreach(\App\Enums\TaskStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ $task->status === $status ? 'selected' : '' }} class="bg-background text-primary-text">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </form>
                    
                    <form action="{{ route('tasks.updatePriorityInline', $task) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <select name="priority" onchange="this.form.submit()" class="text-xs font-medium px-2.5 py-1 rounded-full border-0 focus:ring-2 focus:ring-primary cursor-pointer bg-surface border border-border text-primary-text">
                            @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" {{ $task->priority === $priority ? 'selected' : '' }} class="bg-background text-primary-text">{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <p class="text-sm text-secondary-text mt-1">
                    in <a href="{{ route('projects.show', $task->project) }}" class="text-primary hover:underline">{{ $task->project->name }}</a>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('tasks.edit', $task) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    @if($task->isBlocked())
        <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between gap-3 text-amber-300">
            <div class="flex items-center gap-2.5 text-xs font-semibold">
                <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>This task is currently blocked by <strong>{{ $task->unresolvedDependenciesCount() }}</strong> unresolved prerequisite task(s).</span>
            </div>
            <a href="#dependencies-section" class="text-xs text-amber-400 underline font-bold">View Blockers</a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Description</h3>
                </x-slot>

                @if($task->description)
                    <div class="prose prose-invert max-w-none text-sm text-primary-text leading-relaxed whitespace-pre-wrap">{{ $task->description }}</div>
                @else
                    <p class="text-sm text-secondary-text italic">No description provided.</p>
                @endif
            </x-card>

            <!-- Task Dependencies & Blockers -->
            <x-card id="dependencies-section">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-primary-text flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            Dependencies & Blockers
                        </h3>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <!-- Blocked By (Prerequisites) -->
                    <div>
                        <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider mb-2">
                            Blocked By (Prerequisites: {{ $task->dependencies->count() }})
                        </h4>

                        @if($task->dependencies->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($task->dependencies as $dep)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-surface border border-border text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <x-badge :value="$dep->status" size="sm" />
                                            <a href="{{ route('tasks.show', $dep) }}" class="font-medium text-primary-text hover:text-primary transition truncate">
                                                {{ $dep->title }}
                                            </a>
                                        </div>

                                        <form action="{{ route('projects.tasks.dependencies.destroy', [$task->project, $task, $dep]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-secondary-text hover:text-red-400 transition" title="Remove Dependency">
                                                &times;
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-secondary-text italic">No prerequisite dependencies. This task is ready to work on.</p>
                        @endif

                        <!-- Add Prerequisite Form -->
                        @if($availablePrerequisites->isNotEmpty())
                            <form action="{{ route('projects.tasks.dependencies.store', [$task->project, $task]) }}" method="POST" class="mt-3 flex items-center gap-2">
                                @csrf
                                <select name="depends_on_task_id" required class="flex-1 text-xs rounded-xl bg-background border border-border text-primary-text p-2 focus:ring-primary">
                                    <option value="">+ Add prerequisite task...</option>
                                    @foreach($availablePrerequisites as $avail)
                                        <option value="{{ $avail->id }}">#{{ $avail->id }} &bull; {{ $avail->title }} ({{ $avail->status->value }})</option>
                                    @endforeach
                                </select>
                                <x-primary-button class="!text-xs !px-3 !py-2 shrink-0">
                                    Add Blocker
                                </x-primary-button>
                            </form>
                        @endif
                    </div>

                    <!-- Blocks (Dependents) -->
                    @if($task->dependents->isNotEmpty())
                        <div class="pt-3 border-t border-border">
                            <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider mb-2">
                                Blocks ({{ $task->dependents->count() }} dependent tasks)
                            </h4>
                            <div class="space-y-1.5">
                                @foreach($task->dependents as $child)
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-background text-xs">
                                        <span class="text-amber-400 font-bold">&rarr;</span>
                                        <x-badge :value="$child->status" size="sm" />
                                        <a href="{{ route('tasks.show', $child) }}" class="text-primary-text hover:text-primary truncate">
                                            {{ $child->title }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Time Tracking & Work Logs -->
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-primary-text flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Time Tracking
                        </h3>
                        <span class="text-xs font-bold text-emerald-400 px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30">
                            Total: {{ $task->formattedTotalTime() }}
                        </span>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <!-- Progress vs Estimate -->
                    @if($task->estimated_hours)
                        @php
                            $pct = min(100, round(($task->totalLoggedHours() / $task->estimated_hours) * 100));
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-secondary-text">Logged vs Estimated</span>
                                <span class="text-primary-text">{{ $task->totalLoggedHours() }}h / {{ $task->estimated_hours }}h ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-background border border-border overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-indigo-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Log Work Time Form -->
                    <form action="{{ route('projects.tasks.timeLogs.store', [$task->project, $task]) }}" method="POST" class="p-3.5 rounded-xl bg-background border border-border space-y-3">
                        @csrf
                        <div class="text-xs font-bold text-primary-text">Log Work Time</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] text-secondary-text mb-1">Hours</label>
                                <input type="number" name="hours" min="0" max="24" placeholder="0" class="w-full text-xs rounded-lg bg-surface border border-border text-primary-text p-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-[11px] text-secondary-text mb-1">Minutes</label>
                                <input type="number" name="minutes" min="0" max="59" placeholder="30" class="w-full text-xs rounded-lg bg-surface border border-border text-primary-text p-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-[11px] text-secondary-text mb-1">Date Logged</label>
                                <input type="date" name="logged_at" value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg bg-surface border border-border text-primary-text p-2 focus:ring-primary">
                            </div>
                        </div>
                        <div>
                            <input type="text" name="description" placeholder="Work notes (optional, e.g. refactored auth flow)..." class="w-full text-xs rounded-lg bg-surface border border-border text-primary-text p-2 focus:ring-primary">
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button class="!text-xs !px-3 !py-1.5">
                                Log Time
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Work Log History -->
                    @if($task->timeLogs->isNotEmpty())
                        <div class="space-y-2 pt-2 border-t border-border">
                            <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider">Work Entries ({{ $task->timeLogs->count() }})</h4>
                            <div class="space-y-1.5">
                                @foreach($task->timeLogs as $log)
                                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-surface text-xs border border-border">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <x-avatar :name="$log->user->name" size="xs" />
                                            <span class="font-bold text-primary-text">{{ $log->formattedDuration() }}</span>
                                            <span class="text-secondary-text">&bull; {{ $log->logged_at->format('M d') }}</span>
                                            @if($log->description)
                                                <span class="text-secondary-text italic truncate">&bull; {{ $log->description }}</span>
                                            @endif
                                        </div>

                                        @if($log->user_id === auth()->id() || $task->project->owner_id === auth()->id())
                                            <form action="{{ route('projects.tasks.timeLogs.destroy', [$task->project, $task, $log]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-secondary-text hover:text-red-400 transition" title="Delete log">
                                                    &times;
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Comments -->
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Comments ({{ $task->comments->count() }})</h3>
                </x-slot>

                @if($task->comments->count())
                    <div class="space-y-4">
                        @foreach($task->comments as $comment)
                            <div class="flex gap-3">
                                <x-avatar :name="$comment->user->name" size="sm" />
                                <div class="flex-1 bg-surface rounded-xl p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-primary-text">{{ $comment->user->name }}</span>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-secondary-text">{{ $comment->created_at->diffForHumans() }}</span>
                                            @if($comment->user_id === auth()->id() || $task->project->owner_id === auth()->id())
                                                <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this comment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-secondary-text hover:text-red-500 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-primary-text/80 whitespace-pre-wrap">{{ $comment->content }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-secondary-text italic">No comments yet.</p>
                @endif

                <!-- Add Comment Form -->
                <form action="{{ route('comments.store', $task) }}" method="POST" class="mt-4 pt-4 border-t border-border">
                    @csrf
                    <div class="flex gap-3">
                        <x-avatar :name="auth()->user()->name" size="sm" />
                        <div class="flex-1">
                            <textarea name="content" rows="2" placeholder="Write a comment..." class="block w-full border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm placeholder-secondary-text/50 text-sm" required></textarea>
                            <div class="flex justify-end mt-2">
                                <x-primary-button class="!text-xs !px-3 !py-1.5">
                                    Comment
                                </x-primary-button>
                            </div>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Details</h3>
                </x-slot>

                <div class="space-y-4">
                    <!-- Milestone -->
                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Milestone</h4>
                        <form action="{{ route('projects.tasks.assignMilestone', [$task->project, $task]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="milestone_id" onchange="this.form.submit()" class="text-xs rounded-xl bg-background border border-border text-primary-text py-1.5 px-3 w-full focus:ring-primary">
                                <option value="">No Milestone</option>
                                @foreach($projectMilestones as $pm)
                                    <option value="{{ $pm->id }}" {{ $task->milestone_id == $pm->id ? 'selected' : '' }}>
                                        🎯 {{ $pm->title }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <!-- Sprint -->
                    @if($task->sprint)
                        <div>
                            <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-1">Sprint</h4>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 text-xs font-bold">
                                ⚡ {{ $task->sprint->name }}
                            </span>
                        </div>
                    @endif
                    <!-- Assignee -->
                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Assignee</h4>
                        <form action="{{ route('tasks.updateAssigneeInline', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="flex items-center gap-2">
                                @if($task->assignee)
                                    <x-avatar :name="$task->assignee->name" size="sm" />
                                @else
                                    <div class="w-8 h-8 rounded-full border border-dashed border-border flex items-center justify-center text-secondary-text bg-surface">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                @endif
                                <select name="assignee_id" onchange="this.form.submit()" class="text-sm bg-transparent border-0 border-b border-transparent hover:border-border focus:border-primary focus:ring-0 py-1 pl-1 pr-6 cursor-pointer text-primary-text">
                                    <option value="" class="bg-background text-primary-text">Unassigned</option>
                                    @foreach($projectMembers as $member)
                                        <option value="{{ $member->id }}" {{ $task->assignee_id == $member->id ? 'selected' : '' }} class="bg-background text-primary-text">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Due Date</h4>
                        @if($task->due_date)
                            <span class="text-sm text-primary-text {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-500 font-medium' : '' }}">
                                {{ $task->due_date->format('M d, Y') }}
                                @if($task->due_date->isPast() && $task->status !== 'done')
                                    (Overdue)
                                @endif
                            </span>
                        @else
                            <p class="text-sm text-secondary-text italic">No due date</p>
                        @endif
                    </div>

                    <!-- Tags -->
                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Tags</h4>
                        @if($task->tags->count())
                            <div class="flex flex-wrap gap-1">
                                @foreach($task->tags as $tag)
                                    <span class="text-xs font-medium px-2 py-1 rounded-lg text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-secondary-text italic">No tags</p>
                        @endif
                    </div>

                    <!-- Creator -->
                    <div class="pt-4 border-t border-border">
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Created By</h4>
                        @if($task->creator)
                            <div class="flex items-center gap-2">
                                <x-avatar :name="$task->creator->name" size="xs" />
                                <span class="text-sm text-primary-text">{{ $task->creator->name }}</span>
                            </div>
                        @endif
                        <p class="text-xs text-secondary-text mt-1">{{ $task->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Danger Zone -->
            <x-card class="border-red-500/20">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-red-500">Delete this task</h4>
                        <p class="text-xs text-secondary-text mt-1">This action cannot be undone.</p>
                    </div>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-500/10 border border-transparent rounded-xl font-semibold text-xs text-red-500 uppercase tracking-widest hover:bg-red-500/20 transition">
                            Delete
                        </button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>

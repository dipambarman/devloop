<x-app-layout>
    <x-slot name="header">
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('projects.show', $project) }}" class="text-secondary-text hover:text-primary-text transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white font-bold text-sm shadow-lg" style="background-color: {{ $project->color }}">
                    {{ strtoupper(substr($project->name, 0, 2)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text truncate">
                        {{ $project->name }} <span class="text-secondary-text font-normal">/ Sprint Planning</span>
                    </h2>
                </div>
            </div>

            <!-- Navigation Bar for Project -->
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('projects.show', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Overview
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.board', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Kanban Board
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.sprints.index', $project) }}">
                    <x-primary-button class="!bg-primary text-white shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Sprints
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.discussions.index', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Discussions
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.bugs.index', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Errors ({{ $project->bugs()->count() }})
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" 
         x-data="{ 
             showCreateModal: false,
             showCompleteModal: false,
             showEditModal: false,
             completeSprintId: null,
             completeSprintName: '',
             editSprint: { id: null, name: '', goal: '', start_date: '', end_date: '' }
         }">

        <!-- Header Actions Banner -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-surface border border-border">
            <div>
                <h3 class="text-lg font-bold text-primary-text flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Sprint & Backlog Management
                </h3>
                <p class="text-xs text-secondary-text mt-1">Organize tasks into iterative, time-boxed sprints or prioritize work in the backlog.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button type="button" @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-hover text-white shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Sprint
                </button>
            </div>
        </div>

        <!-- 1. Active Sprint Hero Card -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Current Active Sprint
                </h4>
                @if($activeSprint)
                    <a href="{{ route('projects.board', [$project, 'sprint' => 'active']) }}" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                        View Active Sprint on Board &rarr;
                    </a>
                @endif
            </div>

            @if($activeSprint)
                <div class="p-6 rounded-2xl bg-surface border border-emerald-500/30 shadow-lg relative overflow-hidden space-y-5">
                    <div class="absolute -right-16 -top-16 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full {{ $activeSprint->status->badgeClasses() }}">
                                    ● Active Sprint
                                </span>
                                <h3 class="text-xl font-black text-primary-text">{{ $activeSprint->name }}</h3>
                            </div>
                            @if($activeSprint->goal)
                                <p class="text-xs text-secondary-text mt-1 italic leading-relaxed">
                                    "{{ $activeSprint->goal }}"
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Countdown Pill -->
                            @if($activeSprint->daysRemaining() !== null)
                                <div class="px-3 py-1.5 rounded-lg bg-background border border-border text-center">
                                    <div class="text-[10px] text-secondary-text font-bold uppercase">Time Remaining</div>
                                    <div class="text-xs font-bold {{ $activeSprint->daysRemaining() < 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                        {{ $activeSprint->daysRemaining() < 0 ? abs($activeSprint->daysRemaining()) . ' days overdue' : $activeSprint->daysRemaining() . ' days left' }}
                                    </div>
                                </div>
                            @endif

                            <button type="button" 
                                    @click="completeSprintId = {{ $activeSprint->id }}; completeSprintName = '{{ addslashes($activeSprint->name) }}'; showCompleteModal = true"
                                    class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition">
                                Complete Sprint
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-secondary-text">Sprint Progress</span>
                            <span class="text-primary-text">{{ $activeSprint->completedTasksCount() }} of {{ $activeSprint->totalTasksCount() }} tasks done ({{ $activeSprint->progressPercentage() }}%)</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-background border border-border overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500" style="width: {{ $activeSprint->progressPercentage() }}%"></div>
                        </div>
                    </div>

                    <!-- Task List in Active Sprint -->
                    <div class="pt-2 border-t border-border">
                        <div class="text-xs font-bold text-secondary-text uppercase tracking-wider mb-3">Tasks in this Sprint ({{ $activeSprint->tasks->count() }})</div>
                        @if($activeSprint->tasks->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($activeSprint->tasks as $task)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-background border border-border hover:border-border-hover transition">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <x-badge :value="$task->status" size="sm" />
                                            <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-primary-text hover:text-primary transition truncate">
                                                {{ $task->title }}
                                            </a>
                                            @if($task->priority)
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-surface border border-border text-secondary-text">
                                                    {{ ucfirst($task->priority->value) }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-3">
                                            @if($task->assignee)
                                                <div class="flex items-center gap-1.5 text-xs text-secondary-text">
                                                    <x-avatar :name="$task->assignee->name" size="xs" />
                                                    <span class="hidden sm:inline">{{ $task->assignee->name }}</span>
                                                </div>
                                            @endif

                                            <form action="{{ route('projects.tasks.assignSprint', [$project, $task]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="sprint_id" value="">
                                                <button type="submit" class="text-xs text-secondary-text hover:text-red-400 transition" title="Move back to Backlog">
                                                    &times; Move to Backlog
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-secondary-text italic py-2">No tasks assigned to this active sprint yet. Assign tasks from the backlog below!</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-8 rounded-2xl bg-surface border border-dashed border-border text-center space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-primary-text">No active sprint</h3>
                    <p class="text-xs text-secondary-text max-w-md mx-auto">Create and activate a sprint from the planned sprints below to start tracking your sprint velocity and milestone goals.</p>
                </div>
            @endif
        </div>

        <!-- 2. Planned Sprints -->
        <div class="space-y-4">
            <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider">
                Planned Sprints ({{ $planningSprints->count() }})
            </h4>

            @forelse($planningSprints as $sprint)
                <div class="p-5 rounded-2xl bg-surface border border-border shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full {{ $sprint->status->badgeClasses() }}">
                                    Planning
                                </span>
                                <h4 class="text-base font-bold text-primary-text">{{ $sprint->name }}</h4>
                                @if($sprint->start_date && $sprint->end_date)
                                    <span class="text-xs text-secondary-text font-medium">
                                        ({{ $sprint->start_date->format('M d') }} - {{ $sprint->end_date->format('M d, Y') }})
                                    </span>
                                @endif
                            </div>
                            @if($sprint->goal)
                                <p class="text-xs text-secondary-text mt-1 italic">{{ $sprint->goal }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('projects.sprints.start', [$project, $sprint]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-primary hover:bg-primary-hover text-white transition">
                                    Start Sprint
                                </button>
                            </form>

                            <button type="button" 
                                    @click="editSprint = { id: {{ $sprint->id }}, name: '{{ addslashes($sprint->name) }}', goal: '{{ addslashes($sprint->goal ?? '') }}', start_date: '{{ $sprint->start_date?->format('Y-m-d') ?? '' }}', end_date: '{{ $sprint->end_date?->format('Y-m-d') ?? '' }}' }; showEditModal = true"
                                    class="p-1.5 text-secondary-text hover:text-primary-text transition" title="Edit Sprint">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>

                            <form action="{{ route('projects.sprints.destroy', [$project, $sprint]) }}" method="POST" onsubmit="return confirm('Delete this sprint? Its tasks will return to the backlog.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-secondary-text hover:text-red-400 transition" title="Delete Sprint">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Tasks allocated to this planned sprint -->
                    <div class="pt-2 border-t border-border">
                        <div class="text-[11px] font-bold text-secondary-text uppercase tracking-wider mb-2">Planned Tasks ({{ $sprint->tasks->count() }})</div>
                        @if($sprint->tasks->isNotEmpty())
                            <div class="space-y-1.5">
                                @foreach($sprint->tasks as $task)
                                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-background border border-border text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-primary-text hover:text-primary transition truncate">
                                                {{ $task->title }}
                                            </a>
                                        </div>

                                        <form action="{{ route('projects.tasks.assignSprint', [$project, $task]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="sprint_id" value="">
                                            <button type="submit" class="text-secondary-text hover:text-red-400 transition" title="Move to Backlog">
                                                &times;
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-secondary-text italic py-1">No tasks in this sprint yet.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-surface border border-dashed border-border text-center">
                    <p class="text-xs text-secondary-text">No planned sprints. Click "Create Sprint" above to set up your next iteration.</p>
                </div>
            @endforelse
        </div>

        <!-- 3. Backlog Section -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider">
                        Project Backlog ({{ $backlogTasks->count() }})
                    </h4>
                    <p class="text-xs text-secondary-text">Tasks that are not currently allocated to any sprint.</p>
                </div>

                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="text-xs text-primary hover:underline font-semibold flex items-center gap-1">
                    + Add New Task to Backlog
                </a>
            </div>

            <div class="rounded-2xl bg-surface border border-border shadow-sm divide-y divide-border overflow-hidden">
                @forelse($backlogTasks as $task)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-background/40 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <x-badge :value="$task->status" size="sm" />
                            <div class="min-w-0">
                                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-semibold text-primary-text hover:text-primary transition truncate block">
                                    {{ $task->title }}
                                </a>
                                <div class="flex items-center gap-2 mt-0.5 text-xs text-secondary-text">
                                    @if($task->due_date)
                                        <span>Due {{ $task->due_date->format('M d') }}</span>
                                    @endif
                                    @if($task->priority)
                                        <span>&bull; {{ ucfirst($task->priority->value) }} priority</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Allocation Dropdown -->
                        <div class="flex items-center gap-3">
                            @if($task->assignee)
                                <div class="hidden sm:flex items-center gap-1.5 text-xs text-secondary-text">
                                    <x-avatar :name="$task->assignee->name" size="xs" />
                                    <span>{{ $task->assignee->name }}</span>
                                </div>
                            @endif

                            <form action="{{ route('projects.tasks.assignSprint', [$project, $task]) }}" method="POST" class="flex items-center gap-1">
                                @csrf
                                @method('PATCH')
                                <select name="sprint_id" onchange="this.form.submit()" class="text-xs rounded-lg bg-background border-border text-primary-text py-1 px-2.5 focus:ring-primary focus:border-primary">
                                    <option value="">Move to Sprint...</option>
                                    @if($activeSprint)
                                        <option value="{{ $activeSprint->id }}">Active: {{ $activeSprint->name }}</option>
                                    @endif
                                    @foreach($planningSprints as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-secondary-text">
                        Backlog is clean! All tasks are currently assigned to active or planned sprints.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 4. Completed Sprints Archive -->
        @if($completedSprints->isNotEmpty())
            <div class="space-y-4 pt-4 border-t border-border">
                <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider">
                    Completed Sprints History ({{ $completedSprints->count() }})
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($completedSprints as $sprint)
                        <div class="p-4 rounded-xl bg-surface border border-border shadow-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $sprint->status->badgeClasses() }}">
                                    Completed
                                </span>
                                <span class="text-xs text-secondary-text">
                                    {{ $sprint->end_date ? $sprint->end_date->format('M d, Y') : $sprint->updated_at->format('M d, Y') }}
                                </span>
                            </div>

                            <h5 class="text-sm font-bold text-primary-text">{{ $sprint->name }}</h5>
                            <p class="text-xs text-secondary-text">{{ $sprint->tasks_count }} tasks completed</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- =================== MODALS =================== -->

        <!-- Create Sprint Modal -->
        <div x-show="showCreateModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showCreateModal = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary-text">Create Sprint</h3>
                    <button type="button" @click="showCreateModal = false" class="text-secondary-text hover:text-primary-text">&times;</button>
                </div>

                <form action="{{ route('projects.sprints.store', $project) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Sprint Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Sprint 1: MVP Core" class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Sprint Goal</label>
                        <textarea name="goal" rows="2" placeholder="What is the objective of this sprint?" class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-secondary-text mb-1">Start Date</label>
                            <input type="date" name="start_date" class="w-full text-xs rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-secondary-text mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full text-xs rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-border">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-secondary-text hover:text-primary-text transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-primary hover:bg-primary-hover text-white transition">
                            Create Sprint
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Sprint Modal -->
        <div x-show="showEditModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showEditModal = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary-text">Edit Sprint</h3>
                    <button type="button" @click="showEditModal = false" class="text-secondary-text hover:text-primary-text">&times;</button>
                </div>

                <form :action="`/projects/{{ $project->id }}/sprints/${editSprint.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Sprint Name *</label>
                        <input type="text" name="name" x-model="editSprint.name" required class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Sprint Goal</label>
                        <textarea name="goal" x-model="editSprint.goal" rows="2" class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-secondary-text mb-1">Start Date</label>
                            <input type="date" name="start_date" x-model="editSprint.start_date" class="w-full text-xs rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-secondary-text mb-1">End Date</label>
                            <input type="date" name="end_date" x-model="editSprint.end_date" class="w-full text-xs rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-border">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-secondary-text hover:text-primary-text transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-primary hover:bg-primary-hover text-white transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Complete Sprint Modal (Rollover Options) -->
        <div x-show="showCompleteModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showCompleteModal = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
                <div>
                    <h3 class="text-lg font-bold text-primary-text">Complete Sprint</h3>
                    <p class="text-xs text-secondary-text mt-1">Completing <strong class="text-primary-text" x-text="completeSprintName"></strong>. Any tasks that are not done will be rolled over.</p>
                </div>

                <form :action="`/projects/{{ $project->id }}/sprints/${completeSprintId}/complete`" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-2">Move open/incomplete tasks to:</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 p-3 rounded-xl bg-background border border-border cursor-pointer">
                                <input type="radio" name="rollover_action" value="backlog" checked class="text-primary focus:ring-primary">
                                <div class="text-xs">
                                    <div class="font-bold text-primary-text">Project Backlog</div>
                                    <div class="text-secondary-text">Tasks return to unsprinted backlog</div>
                                </div>
                            </label>

                            @if($planningSprints->isNotEmpty())
                                <label class="flex items-center gap-2 p-3 rounded-xl bg-background border border-border cursor-pointer">
                                    <input type="radio" name="rollover_action" value="next_sprint" class="text-primary focus:ring-primary">
                                    <div class="text-xs flex-1">
                                        <div class="font-bold text-primary-text">Next Planned Sprint</div>
                                        <select name="target_sprint_id" class="mt-1 w-full text-xs rounded-lg bg-surface border-border text-primary-text py-1 px-2 focus:ring-primary">
                                            @foreach($planningSprints as $ps)
                                                <option value="{{ $ps->id }}">{{ $ps->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-border">
                        <button type="button" @click="showCompleteModal = false" class="px-4 py-2 text-xs font-semibold text-secondary-text hover:text-primary-text transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white transition">
                            Confirm & Complete
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

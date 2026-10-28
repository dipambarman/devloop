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
                        {{ $project->name }} <span class="text-secondary-text font-normal">/ Milestones & Releases</span>
                    </h2>
                </div>
            </div>

            <!-- Navigation Bar for Project -->
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('projects.show', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        Overview
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.board', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        Board
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.sprints.index', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        Sprints ({{ $project->sprints()->count() }})
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.milestones.index', $project) }}">
                    <x-primary-button class="!bg-primary text-white shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                        Milestones ({{ $project->milestones()->count() }})
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.time-report', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Time Report
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8"
         x-data="{ 
             showCreateModal: false,
             showEditModal: false,
             editMilestone: { id: null, title: '', description: '', due_date: '' }
         }">

        <!-- Banner -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-surface border border-border">
            <div>
                <h3 class="text-lg font-bold text-primary-text flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                    Release Milestones
                </h3>
                <p class="text-xs text-secondary-text mt-1">Group tasks into target delivery goals and major version releases.</p>
            </div>

            <button type="button" @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-hover text-white shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Milestone
            </button>
        </div>

        <!-- 1. Open Milestones -->
        <div class="space-y-4">
            <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Open Milestones ({{ $openMilestones->count() }})
            </h4>

            @forelse($openMilestones as $milestone)
                <div class="p-6 rounded-2xl bg-surface border border-border shadow-sm space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full {{ $milestone->status->badgeClasses() }}">
                                    Open
                                </span>
                                <h4 class="text-lg font-bold text-primary-text">{{ $milestone->title }}</h4>
                                @if($milestone->due_date)
                                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-md border {{ $milestone->isOverdue() ? 'bg-red-500/10 text-red-400 border-red-500/30' : 'bg-background text-secondary-text border-border' }}">
                                        Due {{ $milestone->due_date->format('M d, Y') }}
                                        @if($milestone->isOverdue())
                                            (Overdue)
                                        @endif
                                    </span>
                                @endif
                            </div>
                            @if($milestone->description)
                                <p class="text-xs text-secondary-text mt-1 leading-relaxed">{{ $milestone->description }}</p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <form action="{{ route('projects.milestones.toggleStatus', [$project, $milestone]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-background border border-border text-secondary-text hover:text-emerald-400 transition">
                                    Close Milestone
                                </button>
                            </form>

                            <button type="button" 
                                    @click="editMilestone = { id: {{ $milestone->id }}, title: '{{ addslashes($milestone->title) }}', description: '{{ addslashes($milestone->description ?? '') }}', due_date: '{{ $milestone->due_date?->format('Y-m-d') ?? '' }}' }; showEditModal = true"
                                    class="p-1.5 text-secondary-text hover:text-primary-text transition" title="Edit Milestone">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>

                            <form action="{{ route('projects.milestones.destroy', [$project, $milestone]) }}" method="POST" onsubmit="return confirm('Delete this milestone? Assigned tasks will be unlinked.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-secondary-text hover:text-red-400 transition" title="Delete Milestone">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-secondary-text">Completion Progress</span>
                            <span class="text-primary-text">{{ $milestone->completedTasksCount() }} of {{ $milestone->totalTasksCount() }} tasks done ({{ $milestone->progressPercentage() }}%)</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-background border border-border overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-indigo-500 rounded-full transition-all duration-500" style="width: {{ $milestone->progressPercentage() }}%"></div>
                        </div>
                    </div>

                    <!-- Task List -->
                    <div class="pt-3 border-t border-border">
                        <div class="text-[11px] font-bold text-secondary-text uppercase tracking-wider mb-2">Milestone Tasks ({{ $milestone->tasks->count() }})</div>
                        @if($milestone->tasks->isNotEmpty())
                            <div class="space-y-1.5">
                                @foreach($milestone->tasks as $task)
                                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-background border border-border text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <x-badge :value="$task->status" size="sm" />
                                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-primary-text hover:text-primary transition truncate">
                                                {{ $task->title }}
                                            </a>
                                        </div>

                                        <form action="{{ route('projects.tasks.assignMilestone', [$project, $task]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="milestone_id" value="">
                                            <button type="submit" class="text-secondary-text hover:text-red-400 transition" title="Unlink from Milestone">
                                                &times; Unlink
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-secondary-text italic py-1">No tasks linked to this milestone yet. Allocate tasks below!</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 rounded-2xl bg-surface border border-dashed border-border text-center">
                    <p class="text-xs text-secondary-text">No open milestones. Click "New Milestone" above to establish your target releases.</p>
                </div>
            @endforelse
        </div>

        <!-- 2. Unassigned Tasks Queue -->
        <div class="space-y-4">
            <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider">
                Unassigned Tasks Queue ({{ $unassignedTasks->count() }})
            </h4>

            <div class="rounded-2xl bg-surface border border-border shadow-sm divide-y divide-border overflow-hidden">
                @forelse($unassignedTasks as $task)
                    <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-background/40 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <x-badge :value="$task->status" size="sm" />
                            <a href="{{ route('tasks.show', $task) }}" class="text-xs font-semibold text-primary-text hover:text-primary transition truncate">
                                {{ $task->title }}
                            </a>
                        </div>

                        <form action="{{ route('projects.tasks.assignMilestone', [$project, $task]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="milestone_id" onchange="this.form.submit()" class="text-xs rounded-lg bg-background border-border text-primary-text py-1 px-2.5 focus:ring-primary">
                                <option value="">Assign to Milestone...</option>
                                @foreach($openMilestones as $m)
                                    <option value="{{ $m->id }}">{{ $m->title }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-secondary-text">All tasks are currently assigned to milestones.</div>
                @endforelse
            </div>
        </div>

        <!-- 3. Closed Milestones Archive -->
        @if($closedMilestones->isNotEmpty())
            <div class="space-y-4 pt-4 border-t border-border">
                <h4 class="text-xs font-bold text-secondary-text uppercase tracking-wider">
                    Closed Milestones Archive ({{ $closedMilestones->count() }})
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($closedMilestones as $milestone)
                        <div class="p-4 rounded-xl bg-surface border border-border shadow-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $milestone->status->badgeClasses() }}">
                                    Closed
                                </span>
                                <form action="{{ route('projects.milestones.toggleStatus', [$project, $milestone]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs text-primary hover:underline">Reopen</button>
                                </form>
                            </div>

                            <h5 class="text-sm font-bold text-primary-text">{{ $milestone->title }}</h5>
                            <p class="text-xs text-secondary-text">{{ $milestone->completedTasksCount() }} / {{ $milestone->totalTasksCount() }} tasks completed ({{ $milestone->progressPercentage() }}%)</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Create Milestone Modal -->
        <div x-show="showCreateModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showCreateModal = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary-text">Create Milestone</h3>
                    <button type="button" @click="showCreateModal = false" class="text-secondary-text hover:text-primary-text">&times;</button>
                </div>

                <form action="{{ route('projects.milestones.store', $project) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Title *</label>
                        <input type="text" name="title" required placeholder="e.g. v1.0.0 Public Launch" class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Key deliverables or release notes..." class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Due Date</label>
                        <input type="date" name="due_date" class="w-full text-xs rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-border">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-secondary-text hover:text-primary-text">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-primary hover:bg-primary-hover text-white">Create Milestone</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Milestone Modal -->
        <div x-show="showEditModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showEditModal = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary-text">Edit Milestone</h3>
                    <button type="button" @click="showEditModal = false" class="text-secondary-text hover:text-primary-text">&times;</button>
                </div>

                <form :action="`/projects/{{ $project->id }}/milestones/${editMilestone.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Title *</label>
                        <input type="text" name="title" x-model="editMilestone.title" required class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Description</label>
                        <textarea name="description" x-model="editMilestone.description" rows="2" class="w-full text-sm rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-secondary-text mb-1">Due Date</label>
                        <input type="date" name="due_date" x-model="editMilestone.due_date" class="w-full text-xs rounded-xl bg-background border border-border text-primary-text p-2.5 focus:ring-primary">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-border">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-secondary-text hover:text-primary-text">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-primary hover:bg-primary-hover text-white">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

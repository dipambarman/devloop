<x-app-layout>
    <x-slot name="header">
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('projects.index') }}" class="text-secondary-text hover:text-primary-text transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white font-bold text-sm shadow-lg" style="background-color: {{ $project->color }}">
                    {{ strtoupper(substr($project->name, 0, 2)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold tracking-tight text-primary-text truncate">{{ $project->name }}</h2>
                        <x-badge :value="$project->status" />
                    </div>
                    @if($project->github_repo)
                        <a href="https://github.com/{{ $project->github_repo }}" target="_blank" rel="noopener noreferrer" class="text-sm text-secondary-text hover:text-primary mt-1 inline-flex items-center gap-1 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" /></svg>
                            {{ $project->github_repo }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('projects.discussions.index', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Discussions
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.board', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Board
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.sprints.index', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Sprints ({{ $project->sprints()->count() }})
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
                <!-- Export Dropdown -->
                <div class="relative" x-data="{ exportOpen: false }" @click.outside="exportOpen = false">
                    <button type="button" @click="exportOpen = !exportOpen" class="inline-flex items-center px-4 py-2 bg-surface border border-border rounded-xl font-medium text-xs text-primary-text hover:bg-surface-hover transition-colors shadow-sm focus:outline-none">
                        <svg class="w-4 h-4 mr-2 text-secondary-text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export
                        <svg class="w-3.5 h-3.5 ml-1.5 text-secondary-text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="exportOpen" x-cloak x-transition class="absolute right-0 mt-2 w-52 rounded-xl bg-surface border border-border shadow-2xl py-1 z-30 backdrop-blur-md">
                        <div class="px-3 py-1.5 text-[10px] font-semibold text-secondary-text uppercase tracking-wider border-b border-border/50">Tasks</div>
                        <a href="{{ route('projects.export.tasks', $project) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-primary-text hover:bg-surface-hover hover:text-primary transition-colors">
                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 font-mono">CSV</span>
                            <span>Export Tasks (CSV)</span>
                        </a>
                        <a href="{{ route('projects.export.tasks', [$project, 'format' => 'json']) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-primary-text hover:bg-surface-hover hover:text-primary transition-colors">
                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-500/10 text-blue-400 font-mono">JSON</span>
                            <span>Export Tasks (JSON)</span>
                        </a>
                        <div class="px-3 py-1.5 text-[10px] font-semibold text-secondary-text uppercase tracking-wider border-b border-border/50 mt-1 border-t">Errors & Bugs</div>
                        <a href="{{ route('projects.export.errors', $project) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-primary-text hover:bg-surface-hover hover:text-primary transition-colors">
                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 font-mono">CSV</span>
                            <span>Export Errors (CSV)</span>
                        </a>
                        <a href="{{ route('projects.export.errors', [$project, 'format' => 'json']) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-primary-text hover:bg-surface-hover hover:text-primary transition-colors">
                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-500/10 text-blue-400 font-mono">JSON</span>
                            <span>Export Errors (JSON)</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('projects.edit', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit
                    </x-primary-button>
                </a>
                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}">
                    <x-primary-button>
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Task
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6" x-data="{ activeTab: 'tasks' }">
            <!-- Tabs -->
            <div class="flex items-center gap-4 border-b border-border">
                <button @click="activeTab = 'tasks'" :class="activeTab === 'tasks' ? 'border-primary text-primary' : 'border-transparent text-secondary-text hover:text-primary-text hover:border-border'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                    Tasks
                </button>
                <button @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'border-primary text-primary' : 'border-transparent text-secondary-text hover:text-primary-text hover:border-border'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                    Activity
                </button>
            </div>

            <!-- Tasks Tab -->
            <x-card x-show="activeTab === 'tasks'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <x-slot name="header">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-primary-text">Tasks</h3>
                        <form method="GET" action="{{ route('projects.show', $project) }}" class="flex flex-wrap items-center gap-2">
                            <div class="relative">
                                <x-text-input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..." class="text-xs py-1.5 pl-8 w-36 sm:w-44" />
                                <svg class="w-3.5 h-3.5 text-secondary-text absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <select name="status" onchange="this.form.submit()" class="text-xs py-1.5 bg-background border-border text-primary-text rounded-xl focus:border-primary focus:ring-primary">
                                <option value="">All Statuses</option>
                                <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>To Do</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
                                <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                            </select>
                            @php $pPriority = request('priority', request('severity')); @endphp
                            <select name="priority" onchange="this.form.submit()" class="text-xs py-1.5 bg-background border-border text-primary-text rounded-xl focus:border-primary focus:ring-primary">
                                <option value="">All Priorities</option>
                                <option value="low" {{ $pPriority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $pPriority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $pPriority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $pPriority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @if(request()->anyFilled(['search', 'status', 'priority', 'severity']))
                                <a href="{{ route('projects.show', $project) }}" class="text-xs text-secondary-text hover:text-primary-text underline ml-1">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </x-slot>

                @if($tasks->count())
                    <div class="space-y-1">
                        @foreach($tasks as $task)
                            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-surface-hover transition-colors group border border-transparent hover:border-border cursor-pointer" onclick="window.location='{{ route('tasks.show', $task) }}'">
                                <div class="flex items-center gap-4">
                                    <!-- Status Checkbox -->
                                    <div class="shrink-0">
                                        @if($task->status === 'done')
                                            <div class="w-5 h-5 rounded-full bg-teal flex items-center justify-center text-white">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                        @else
                                            <div class="w-5 h-5 rounded-full border-2 border-secondary-text group-hover:border-primary transition-colors"></div>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium text-primary-text {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through opacity-50' : '' }}">
                                            {{ $task->title }}
                                        </p>
                                        <div class="flex items-center gap-3 mt-1">
                                            <x-badge :value="$task->priority" class="!text-[10px] !py-0 !px-1.5" />

                                            @if($task->due_date)
                                                <span class="text-xs text-secondary-text flex items-center gap-1 {{ $task->due_date->isPast() && $task->status !== \App\Enums\TaskStatus::Done ? 'text-red-500 font-medium' : '' }}">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    {{ $task->due_date->format('M d') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    @if($task->assignee)
                                        <div title="{{ $task->assignee->name }}">
                                            <x-avatar :name="$task->assignee->name" size="xs" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-border">
                        {{ $tasks->links() }}
                    </div>
                @else
                    <x-empty-state 
                        title="No tasks yet" 
                        description="Start filling up this project by creating your first task."
                    >
                        <x-slot name="action">
                            <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}">
                                <x-primary-button>Create Task</x-primary-button>
                            </a>
                        </x-slot>
                    </x-empty-state>
                @endif
            </x-card>

            <!-- Activity Tab -->
            <x-card x-show="activeTab === 'activity'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Project Activity</h3>
                </x-slot>

                @if($activities->count())
                    <div class="relative border-l border-border ml-3 space-y-6">
                        @foreach($activities as $activity)
                            <div class="relative pl-6">
                                <!-- Timeline Dot -->
                                <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full bg-surface border-2 border-primary"></div>
                                
                                <div class="flex items-start gap-3">
                                    <x-avatar :name="$activity->user->name" size="xs" class="shrink-0" />
                                    <div>
                                        <p class="text-sm text-primary-text">
                                            <span class="font-medium">{{ $activity->user->name }}</span>
                                            <span class="text-secondary-text">{{ $activity->description }}</span>
                                        </p>
                                        <span class="text-xs text-secondary-text mt-0.5 block">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-secondary-text italic text-center py-4">No activity yet.</p>
                @endif
            </x-card>
        </div>

        <!-- Sidebar Details -->
        <div class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Project Details</h3>
                </x-slot>

                <div class="space-y-4">
                    @if($project->description)
                        <div>
                            <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-1">Description</h4>
                            <p class="text-sm text-primary-text leading-relaxed whitespace-pre-wrap">{{ $project->description }}</p>
                        </div>
                    @endif

                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-1">Progress</h4>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-primary-text font-medium">{{ $project->completed_tasks_count }} of {{ $project->tasks_count }} tasks done</span>
                            <span class="text-xs font-medium text-primary-text">{{ $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-surface-hover rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-primary to-teal h-full rounded-full transition-all duration-500" style="width: {{ $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-border" x-data="{ showAddMember: false, copied: false }">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider">Team Members</h4>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2000)" class="text-[11px] text-secondary-text hover:text-primary-text transition-colors flex items-center gap-1 px-2 py-0.5 rounded border border-border/60 hover:bg-surface-hover" title="Copy project invite link">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    <span x-text="copied ? 'Copied Link!' : 'Share Link'">Share Link</span>
                                </button>
                                @can('manageMembers', $project)
                                    <button @click="showAddMember = !showAddMember" class="text-xs text-primary hover:text-accent transition-colors font-medium flex items-center gap-1 px-2 py-0.5 rounded border border-primary/30 hover:bg-primary/10">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        <span x-text="showAddMember ? 'Close' : 'Invite'">Invite</span>
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <!-- Add Member Form -->
                        @can('manageMembers', $project)
                            <div x-show="showAddMember" x-collapse class="mb-4 bg-background p-3 rounded-xl border border-border">
                                <form action="{{ route('projects.addMember', $project) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-[11px] font-medium text-secondary-text mb-1">Teammate Email or GitHub Username</label>
                                        <x-text-input name="email" type="text" placeholder="e.g. dev@example.com or @username" class="w-full text-xs py-1.5" required />
                                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                    </div>
                                    <div class="flex gap-2">
                                        <select name="role" class="flex-1 text-xs py-1.5 bg-surface border-border text-primary-text rounded-xl focus:border-primary focus:ring-primary">
                                            <option value="member">Member (Can edit tasks & board)</option>
                                            <option value="viewer">Viewer (Read-only)</option>
                                        </select>
                                        <x-primary-button class="!px-3 !py-1.5 shrink-0 text-xs">Add to Team</x-primary-button>
                                    </div>
                                    <p class="text-[10px] text-secondary-text">Teammates can log in with GitHub to access the project.</p>
                                </form>
                            </div>
                        @endcan

                        <div class="space-y-3 mt-3">
                            <!-- Owner -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-avatar :name="$project->owner->name" size="xs" />
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-primary-text leading-none">{{ $project->owner->name }}</span>
                                        <span class="text-[10px] text-secondary-text mt-0.5">Owner</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Members -->
                            @foreach($project->members as $member)
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center gap-2">
                                        <x-avatar :name="$member->name" size="xs" />
                                        <div class="flex flex-col">
                                            <span class="text-sm text-primary-text leading-none">{{ $member->name }}</span>
                                            <span class="text-[10px] text-secondary-text mt-0.5 capitalize">{{ $member->pivot->role }}</span>
                                        </div>
                                    </div>
                                    
                                    @can('manageMembers', $project)
                                        <form action="{{ route('projects.removeMember', [$project, $member]) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-secondary-text hover:text-red-500 transition-colors p-1" title="Remove Member" onclick="return confirm('Remove this member?');">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- GitHub & Webhooks Integration Card -->
            <x-card x-data="{ copiedUrl: false, copiedSecret: false, showWebhookDetails: false }">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" /></svg>
                            <h3 class="text-sm font-semibold text-primary-text">GitHub Auto-Sync</h3>
                        </div>
                        <button @click="showWebhookDetails = !showWebhookDetails" class="text-xs text-primary hover:underline font-medium">
                            <span x-text="showWebhookDetails ? 'Hide' : 'Configure'">Configure</span>
                        </button>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    <p class="text-xs text-secondary-text leading-relaxed">
                        Auto-resolve tasks when closing PRs or pushing commits with <code class="px-1.5 py-0.5 rounded bg-surface-hover text-primary font-mono text-[11px]">Fixes #12</code> or <code class="px-1.5 py-0.5 rounded bg-surface-hover text-primary font-mono text-[11px]">Closes #12</code>.
                    </p>

                    <div x-show="showWebhookDetails" x-collapse class="space-y-3 pt-2 border-t border-border">
                        <div>
                            <label class="block text-[11px] font-medium text-secondary-text mb-1">Payload URL</label>
                            <div class="flex items-center gap-1.5">
                                <input type="text" readonly value="{{ route('api.projects.webhooks.github', $project) }}" class="w-full text-xs font-mono bg-background border-border text-primary-text rounded-lg px-2.5 py-1.5 select-all focus:border-primary">
                                <button type="button" @click="navigator.clipboard.writeText('{{ route('api.projects.webhooks.github', $project) }}'); copiedUrl = true; setTimeout(() => copiedUrl = false, 2000)" class="px-2.5 py-1.5 text-xs bg-surface border border-border rounded-lg text-secondary-text hover:text-primary-text hover:bg-surface-hover transition-colors shrink-0">
                                    <span x-text="copiedUrl ? 'Copied' : 'Copy'">Copy</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-secondary-text mb-1">Webhook Secret</label>
                            <div class="flex items-center gap-1.5">
                                <input type="text" readonly value="{{ $project->getWebhookSecret() }}" class="w-full text-xs font-mono bg-background border-border text-primary-text rounded-lg px-2.5 py-1.5 select-all focus:border-primary">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $project->getWebhookSecret() }}'); copiedSecret = true; setTimeout(() => copiedSecret = false, 2000)" class="px-2.5 py-1.5 text-xs bg-surface border border-border rounded-lg text-secondary-text hover:text-primary-text hover:bg-surface-hover transition-colors shrink-0">
                                    <span x-text="copiedSecret ? 'Copied' : 'Copy'">Copy</span>
                                </button>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-lg bg-surface-hover/60 border border-border text-[11px] text-secondary-text space-y-1">
                            <div class="font-semibold text-primary-text">GitHub Setup:</div>
                            <ol class="list-decimal list-inside space-y-0.5">
                                <li>In your GitHub repo, open <span class="text-primary-text font-medium">Settings &rarr; Webhooks &rarr; Add webhook</span>.</li>
                                <li>Paste the Payload URL and Secret above.</li>
                                <li>Content type: <span class="text-primary-text font-medium">application/json</span>.</li>
                                <li>Select <span class="text-primary-text font-medium">Just the push event</span> and <span class="text-primary-text font-medium">Pull requests</span>.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>

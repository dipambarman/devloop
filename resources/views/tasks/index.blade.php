<x-app-layout>
    <x-slot name="title">Tasks</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">All Tasks</h2>
                    <p class="text-sm text-secondary-text mt-1">Manage tasks across all your projects.</p>
                </div>
            </div>
            <a href="{{ route('tasks.create') }}">
                <x-primary-button>
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Task
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    <!-- Filters Bar -->
    <div x-data="{ showFilters: false }" class="space-y-6">
        <x-card class="!p-4">
            <form method="GET" action="{{ route('tasks.index') }}" class="space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <!-- Search -->
                    <div class="relative flex-1 w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <x-text-input type="text" name="search" :value="request('search')" placeholder="Search tasks..." class="w-full !pl-10 !py-2" />
                    </div>

                    <button type="button" @click="showFilters = !showFilters" class="inline-flex items-center gap-2 px-4 py-2 bg-surface border border-border rounded-xl text-sm font-medium text-primary-text hover:bg-surface-hover transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filters
                        @if(request()->hasAny(['status', 'priority', 'project_id']))
                            <span class="w-2 h-2 rounded-full bg-primary"></span>
                        @endif
                    </button>

                    <x-primary-button type="submit" class="!py-2">
                        Search
                    </x-primary-button>

                    @if(request()->hasAny(['search', 'status', 'priority', 'project_id']))
                        <a href="{{ route('tasks.index') }}" class="text-sm text-secondary-text hover:text-primary-text transition-colors">Clear</a>
                    @endif
                </div>

                <!-- Expanded Filters -->
                <div x-show="showFilters" x-collapse class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-border">
                    <div>
                        <x-input-label for="filter_status" value="Status" />
                        <select id="filter_status" name="status" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm">
                            <option value="">All Statuses</option>
                            <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
                            <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="filter_priority" value="Priority" />
                        <select id="filter_priority" name="priority" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="filter_project" value="Project" />
                        <select id="filter_project" name="project_id" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </x-card>

        <!-- Task Table -->
        <x-card>
            @if($tasks->count())
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-secondary-text uppercase tracking-wider">Task</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-secondary-text uppercase tracking-wider">Project</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-secondary-text uppercase tracking-wider">Status</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-secondary-text uppercase tracking-wider">Priority</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-secondary-text uppercase tracking-wider">Due Date</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-secondary-text uppercase tracking-wider">Assignee</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            @foreach($tasks as $task)
                                <tr class="hover:bg-surface-hover transition-colors cursor-pointer group" onclick="window.location='{{ route('tasks.show', $task) }}'">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            @if($task->status === \App\Enums\TaskStatus::Done)
                                                <div class="w-5 h-5 rounded-full bg-teal flex items-center justify-center text-white shrink-0">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                            @else
                                                <div class="w-5 h-5 rounded-full border-2 border-border shrink-0"></div>
                                            @endif
                                            <div>
                                                <span class="font-medium text-primary-text group-hover:text-primary transition-colors {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through text-secondary-text' : '' }}">{{ $task->title }}</span>
                                                @if($task->tags->count())
                                                    <div class="flex items-center gap-1 mt-1">
                                                        @foreach($task->tags->take(3) as $tag)
                                                            <span class="text-[9px] font-medium px-1 py-0.5 rounded text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                                        @endforeach
                                                        @if($task->tags->count() > 3)
                                                            <span class="text-[9px] text-secondary-text">+{{ $task->tags->count() - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded shrink-0" style="background-color: {{ $task->project->color }}"></div>
                                            <span class="text-sm text-secondary-text">{{ $task->project->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <x-badge :value="$task->status" class="!text-[10px]" />
                                    </td>
                                    <td class="py-3 px-4">
                                        <x-badge :value="$task->priority" class="!text-[10px]" />
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($task->due_date)
                                            <span class="text-sm {{ $task->due_date->isPast() && $task->status !== \App\Enums\TaskStatus::Done ? 'text-red-500 font-medium' : 'text-secondary-text' }}">
                                                {{ $task->due_date->format('M d, Y') }}
                                                @if($task->due_date->isPast() && $task->status !== \App\Enums\TaskStatus::Done)
                                                    <span class="text-[10px] ml-1">(Overdue)</span>
                                                @elseif($task->due_date->isToday())
                                                    <span class="text-[10px] text-warning ml-1">(Today)</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-sm text-secondary-text/50">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($task->assignee)
                                            <div class="flex items-center gap-2">
                                                <x-avatar :name="$task->assignee->name" size="xs" />
                                                <span class="text-sm text-secondary-text">{{ $task->assignee->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-secondary-text/50">Unassigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-4 pt-4 border-t border-border">
                        {{ $tasks->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <x-empty-state
                    title="No tasks found"
                    description="{{ request()->hasAny(['search', 'status', 'priority', 'project_id']) ? 'Try adjusting your filters.' : 'Create your first task to get started.' }}"
                >
                    <x-slot name="action">
                        @if(request()->hasAny(['search', 'status', 'priority', 'project_id']))
                            <a href="{{ route('tasks.index') }}">
                                <x-primary-button class="!bg-surface !border-border !text-primary-text">Clear Filters</x-primary-button>
                            </a>
                        @else
                            <a href="{{ route('tasks.create') }}">
                                <x-primary-button>Create Task</x-primary-button>
                            </a>
                        @endif
                    </x-slot>
                </x-empty-state>
            @endif
        </x-card>
    </div>
</x-app-layout>

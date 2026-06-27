<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">
                    {{ __('Dashboard') }}
                </h2>
                <p class="text-sm text-secondary-text mt-1">Welcome back, {{ Auth::user()->name }}! Here's what's happening today.</p>
            </div>
            <div>
                <a href="{{ route('projects.create') }}">
                    <x-primary-button>
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Project
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="{{ route('projects.index') }}" class="block group">
            <x-stat-card 
                title="Total Projects" 
                value="{{ $stats['total_projects'] }}"
                class="group-hover:border-primary/50 group-hover:-translate-y-1 transition-all duration-300"
            >
                <x-slot name="icon">
                    <svg class="w-6 h-6 group-hover:text-primary-hover transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </x-slot>
            </x-stat-card>
        </a>

        <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}" class="block group">
            <x-stat-card 
                title="Active Tasks" 
                value="{{ $stats['active_tasks'] }}"
                class="group-hover:border-primary/50 group-hover:-translate-y-1 transition-all duration-300"
            >
                <x-slot name="icon">
                    <svg class="w-6 h-6 group-hover:text-primary-hover transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </x-slot>
            </x-stat-card>
        </a>

        <a href="{{ route('tasks.index', ['status' => 'done']) }}" class="block group">
            <x-stat-card 
                title="Completed Tasks" 
                value="{{ $stats['completed_tasks'] }}"
                class="group-hover:border-teal/50 group-hover:-translate-y-1 transition-all duration-300"
            >
                <x-slot name="icon">
                    <svg class="w-6 h-6 group-hover:text-teal transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot>
            </x-stat-card>
        </a>

        <div title="Team members across your projects">
            <x-stat-card 
                title="Team Members" 
                value="{{ $stats['team_members'] }}"
            >
                <x-slot name="icon">
                    <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </x-slot>
            </x-stat-card>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Projects -->
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-primary-text">Your Projects</h3>
                        <a href="{{ route('projects.index') }}" class="text-sm font-medium text-primary hover:text-primary-hover transition-colors">View all &rarr;</a>
                    </div>
                </x-slot>

                @if($recentProjects->count())
                    <div class="space-y-3">
                        @foreach($recentProjects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-4 p-3 rounded-lg hover:bg-surface-hover transition-colors group">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white font-bold text-sm" style="background-color: {{ $project->color }}">
                                    {{ strtoupper(substr($project->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-primary-text font-medium group-hover:text-primary transition-colors truncate">
                                        {{ $project->name }}
                                    </p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-secondary-text">{{ $project->tasks_count }} tasks</span>
                                        @if($project->tasks_count > 0)
                                            <div class="flex-1 max-w-[120px] bg-surface-hover rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-gradient-to-r from-primary to-teal h-full rounded-full transition-all duration-500" style="width: {{ $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0 }}%"></div>
                                            </div>
                                            <span class="text-xs text-tertiary-text">{{ $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0 }}%</span>
                                        @endif
                                    </div>
                                </div>
                                <x-badge :color="$project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'gray')">
                                    {{ ucfirst($project->status) }}
                                </x-badge>
                            </a>
                        @endforeach
                    </div>
                @else
                    <x-empty-state 
                        title="No projects yet" 
                        description="Create your first project to get started with DevLoop."
                    >
                        <x-slot name="action">
                            <a href="{{ route('projects.create') }}">
                                <x-primary-button>Create Project</x-primary-button>
                            </a>
                        </x-slot>
                    </x-empty-state>
                @endif
            </x-card>
        </div>

        <!-- Task Distribution -->
        <div>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Task Distribution</h3>
                </x-slot>

                @php
                    $total = array_sum($taskDistribution);
                @endphp

                <div class="space-y-4">
                    @foreach([
                        'todo' => ['label' => 'To Do', 'color' => 'bg-secondary-text'],
                        'in_progress' => ['label' => 'In Progress', 'color' => 'bg-primary'],
                        'review' => ['label' => 'In Review', 'color' => 'bg-accent'],
                        'done' => ['label' => 'Done', 'color' => 'bg-teal'],
                    ] as $status => $meta)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm text-secondary-text">{{ $meta['label'] }}</span>
                                <span class="text-sm font-medium text-primary-text">{{ $taskDistribution[$status] }}</span>
                            </div>
                            <div class="w-full bg-surface-hover rounded-full h-2 overflow-hidden">
                                <div class="{{ $meta['color'] }} h-full rounded-full transition-all duration-700 ease-out" style="width: {{ $total > 0 ? round(($taskDistribution[$status] / $total) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>

    <!-- Recent Tasks -->
    <x-card>
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-primary-text">Recent Tasks</h3>
            </div>
        </x-slot>

        @if($recentTasks->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-secondary-text uppercase tracking-wider border-b border-border">
                        <tr>
                            <th class="px-4 py-3">Task</th>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Assignee</th>
                            <th class="px-4 py-3">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($recentTasks as $task)
                            <tr class="hover:bg-surface-hover transition-colors">
                                <td class="px-4 py-3">
                                    <span class="text-primary-text font-medium">{{ $task->title }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-secondary-text">{{ $task->project->name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'todo' => 'gray',
                                            'in_progress' => 'primary',
                                            'review' => 'accent',
                                            'done' => 'success',
                                        ];
                                        $statusLabels = [
                                            'todo' => 'To Do',
                                            'in_progress' => 'In Progress',
                                            'review' => 'Review',
                                            'done' => 'Done',
                                        ];
                                    @endphp
                                    <x-badge :color="$statusColors[$task->status] ?? 'gray'">
                                        {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $priorityColors = [
                                            'low' => 'gray',
                                            'medium' => 'info',
                                            'high' => 'warning',
                                            'urgent' => 'danger',
                                        ];
                                    @endphp
                                    <x-badge :color="$priorityColors[$task->priority] ?? 'gray'">
                                        {{ ucfirst($task->priority) }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-3">
                                    @if($task->assignee)
                                        <x-avatar :name="$task->assignee->name" size="xs" />
                                    @else
                                        <span class="text-tertiary-text text-xs">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($task->due_date)
                                        <span class="text-secondary-text text-xs {{ $task->due_date->isPast() ? 'text-red-500 font-medium' : '' }}">
                                            {{ $task->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-tertiary-text text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state 
                title="No tasks yet" 
                description="Tasks will appear here once you create a project and add tasks to it."
            />
        @endif
    </x-card>
</x-app-layout>

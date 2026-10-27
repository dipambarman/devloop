<x-app-layout>
    <x-slot name="title">Calendar</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">
                        {{ $selectedProject ? $selectedProject->name . ' — Calendar' : 'Task Calendar' }}
                    </h2>
                    <p class="text-sm text-secondary-text mt-0.5">
                        Track deadlines, deliverables, and team schedules across your projects.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                <a href="{{ route('tasks.create') }}">
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

    <div class="space-y-6">
        <!-- Controls & Navigation Bar -->
        <x-card class="!p-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Month Navigator -->
                <div class="flex items-center gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                       class="p-2 rounded-lg bg-surface border border-border text-secondary-text hover:text-primary-text hover:bg-surface-hover transition-colors"
                       title="Previous Month">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['month' => now()->month, 'year' => now()->year]) }}"
                       class="px-3 py-1.5 rounded-lg bg-surface border border-border text-xs font-semibold text-secondary-text hover:text-primary-text hover:bg-surface-hover transition-colors">
                        Today
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                       class="p-2 rounded-lg bg-surface border border-border text-secondary-text hover:text-primary-text hover:bg-surface-hover transition-colors"
                       title="Next Month">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <h3 class="text-lg font-bold text-primary-text ml-2">
                        {{ $currentDate->format('F Y') }}
                    </h3>
                </div>

                <!-- Filter Controls -->
                <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="month" value="{{ $currentDate->month }}">
                    <input type="hidden" name="year" value="{{ $currentDate->year }}">

                    @if(!$selectedProject)
                        <select name="project_id" onchange="this.form.submit()" class="bg-background border-border text-primary-text rounded-lg text-xs py-1.5 px-3 focus:ring-primary focus:border-primary">
                            <option value="">All Projects</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <select name="priority" onchange="this.form.submit()" class="bg-background border-border text-primary-text rounded-lg text-xs py-1.5 px-3 focus:ring-primary focus:border-primary">
                        <option value="">All Priorities</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->value }}" {{ request('priority') == $priority->value ? 'selected' : '' }}>
                                {{ ucfirst($priority->value) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" onchange="this.form.submit()" class="bg-background border-border text-primary-text rounded-lg text-xs py-1.5 px-3 focus:ring-primary focus:border-primary">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                            </option>
                        @endforeach
                    </select>

                    @if(request()->hasAny(['project_id', 'priority', 'status']))
                        <a href="{{ url()->current() . '?month=' . $currentDate->month . '&year=' . $currentDate->year }}"
                           class="text-xs text-primary hover:text-accent font-medium transition-colors ml-1">
                            Reset Filters
                        </a>
                    @endif
                </form>
            </div>
        </x-card>

        <!-- Calendar Month Grid -->
        <div class="rounded-2xl border border-border bg-surface/50 backdrop-blur-sm overflow-hidden shadow-xl">
            <!-- Day of Week Header -->
            <div class="grid grid-cols-7 border-b border-border bg-surface/80 text-center">
                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                    <div class="py-3 text-xs font-bold uppercase tracking-wider text-secondary-text">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            <!-- Days Grid -->
            <div class="grid grid-cols-7 divide-x divide-y divide-border bg-background/40">
                @foreach($calendarGrid as $day)
                    <div class="min-h-[130px] p-2 flex flex-col justify-between transition-colors relative group
                                {{ !$day['is_current_month'] ? 'opacity-35 bg-surface/20' : 'hover:bg-surface/30' }}
                                {{ $day['is_today'] ? 'bg-primary/5 ring-1 ring-inset ring-primary/40' : '' }}">
                        
                        <!-- Cell Date Header -->
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center justify-center text-xs font-semibold rounded-md
                                         {{ $day['is_today'] ? 'w-6 h-6 bg-primary text-white font-bold shadow-md shadow-primary/20' : 'text-primary-text' }}">
                                {{ $day['day_number'] }}
                            </span>

                            <a href="{{ route('tasks.create', ['due_date' => $day['date_string'], 'project_id' => $selectedProject?->id ?? request('project_id')]) }}"
                               class="opacity-0 group-hover:opacity-100 p-1 text-secondary-text hover:text-primary transition-opacity"
                               title="Add task for this day">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </a>
                        </div>

                        <!-- Tasks in this Day -->
                        <div class="mt-1.5 space-y-1.5 flex-1 overflow-y-auto max-h-[140px] pr-0.5">
                            @foreach($day['tasks'] as $task)
                                @php
                                    $projectColor = $task->project->color ?? '#6366f1';
                                    $priorityColor = match($task->priority?->value ?? $task->priority) {
                                        'urgent' => 'text-danger bg-danger/10 border-danger/30',
                                        'high' => 'text-warning bg-warning/10 border-warning/30',
                                        'medium' => 'text-accent bg-accent/10 border-accent/30',
                                        default => 'text-secondary-text bg-surface border-border',
                                    };
                                    $isCompleted = ($task->status?->value ?? $task->status) === 'done' || ($task->status?->value ?? $task->status) === 'completed';
                                @endphp

                                <a href="{{ route('tasks.show', $task) }}"
                                   class="block text-left p-1.5 rounded-lg border text-xs transition-all hover:scale-[1.02] hover:shadow-md {{ $priorityColor }} {{ $isCompleted ? 'line-through opacity-60' : '' }}">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $projectColor }};"></span>
                                        <span class="font-medium truncate text-primary-text">{{ $task->title }}</span>
                                    </div>
                                    @if($task->assignee)
                                        <div class="text-[10px] text-secondary-text truncate mt-0.5 pl-3.5">
                                            {{ $task->assignee->name }}
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

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
                        {{ $project->name }} <span class="text-secondary-text font-normal">/ Time Tracking Report</span>
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
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        Milestones ({{ $project->milestones()->count() }})
                    </x-primary-button>
                </a>
                <a href="{{ route('projects.time-report', $project) }}">
                    <x-primary-button class="!bg-primary text-white shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Time Report
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-surface border border-border shadow-sm">
                <div class="text-[11px] font-bold text-secondary-text uppercase tracking-wider">Total Time Logged</div>
                <div class="text-3xl font-black text-primary-text mt-1">
                    {{ round($totalMinutes / 60, 1) }} <span class="text-sm font-semibold text-secondary-text">hours</span>
                </div>
                <div class="text-xs text-secondary-text mt-1">{{ number_format($totalMinutes) }} total minutes recorded</div>
            </div>

            <div class="p-5 rounded-2xl bg-surface border border-border shadow-sm">
                <div class="text-[11px] font-bold text-secondary-text uppercase tracking-wider">Active Contributors</div>
                <div class="text-3xl font-black text-indigo-400 mt-1">{{ $userSummaries->count() }}</div>
                <div class="text-xs text-secondary-text mt-1">Team members logging work</div>
            </div>

            <div class="p-5 rounded-2xl bg-surface border border-border shadow-sm">
                <div class="text-[11px] font-bold text-secondary-text uppercase tracking-wider">Work Entries</div>
                <div class="text-3xl font-black text-emerald-400 mt-1">{{ $timeLogs->total() }}</div>
                <div class="text-xs text-secondary-text mt-1">Discrete work log entries</div>
            </div>
        </div>

        <!-- Breakdown by Contributor -->
        @if($userSummaries->isNotEmpty())
            <div class="p-6 rounded-2xl bg-surface border border-border shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-primary-text uppercase tracking-wider">Time Breakdown by Contributor</h3>

                <div class="space-y-3">
                    @foreach($userSummaries as $summary)
                        @php
                            $userPct = $totalMinutes > 0 ? round(($summary->total_minutes / $totalMinutes) * 100) : 0;
                            $userHours = round($summary->total_minutes / 60, 1);
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-primary-text flex items-center gap-2">
                                    <x-avatar :name="$summary->name" size="xs" />
                                    {{ $summary->name }}
                                </span>
                                <span class="text-secondary-text">{{ $userHours }} hrs ({{ $userPct }}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-background border border-border overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" style="width: {{ $userPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recent Logs Table -->
        <div class="p-6 rounded-2xl bg-surface border border-border shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-primary-text uppercase tracking-wider">Recent Work Log Entries</h3>

            <div class="divide-y divide-border overflow-hidden rounded-xl border border-border">
                @forelse($timeLogs as $log)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface hover:bg-background/40 transition">
                        <div class="flex items-start sm:items-center gap-3 min-w-0">
                            <x-avatar :name="$log->user->name" size="sm" />
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-primary-text">{{ $log->user->name }}</span>
                                    <span class="text-xs text-secondary-text">&bull; {{ $log->logged_at->format('M d, Y') }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">
                                        {{ $log->formattedDuration() }}
                                    </span>
                                </div>
                                <div class="mt-0.5">
                                    <a href="{{ route('tasks.show', $log->task) }}" class="text-xs font-medium text-secondary-text hover:text-primary transition truncate">
                                        Task: {{ $log->task->title }}
                                    </a>
                                </div>
                                @if($log->description)
                                    <p class="text-xs text-primary-text/80 mt-1 italic">{{ $log->description }}</p>
                                @endif
                            </div>
                        </div>

                        @if($log->user_id === auth()->id() || $project->owner_id === auth()->id())
                            <form action="{{ route('projects.tasks.timeLogs.destroy', [$project, $log->task, $log]) }}" method="POST" onsubmit="return confirm('Delete this time log entry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-secondary-text hover:text-red-400 transition" title="Delete Entry">
                                    &times; Delete
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-secondary-text">No time logged on this project yet.</div>
                @endforelse
            </div>

            <div class="pt-2">
                {{ $timeLogs->links() }}
            </div>
        </div>

    </div>
</x-app-layout>

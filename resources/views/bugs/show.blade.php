<x-app-layout>
    <x-slot name="header">
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('projects.bugs.index', $project) }}" class="text-secondary-text hover:text-primary-text transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <!-- Severity Badge -->
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider
                            @if($bug->severity === 'critical') bg-red-500/15 text-red-400 border border-red-500/30
                            @elseif($bug->severity === 'high') bg-orange-500/15 text-orange-400 border border-orange-500/30
                            @elseif($bug->severity === 'medium') bg-amber-500/15 text-amber-400 border border-amber-500/30
                            @else bg-slate-500/15 text-slate-300 border border-slate-500/30
                            @endif">
                            {{ $bug->severity }}
                        </span>

                        <!-- Status Badge -->
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                            @if($bug->status === 'open') bg-amber-500/15 text-amber-300 border border-amber-500/30
                            @elseif($bug->status === 'in_progress') bg-blue-500/15 text-blue-300 border border-blue-500/30
                            @elseif($bug->status === 'fixed') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                            @else bg-slate-500/15 text-slate-400 border border-slate-500/30
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $bug->status)) }}
                        </span>

                        <span class="text-xs font-mono text-secondary-text">{{ $bug->error_type }}</span>

                        <span class="text-xs font-mono text-secondary-text/80 bg-background px-2 py-0.5 rounded border border-border">
                            #{{ $bug->fingerprint }}
                        </span>

                        @if($bug->regressions > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-600/20 text-red-300 border border-red-500/30">
                                🔥 {{ $bug->regressions }} Regressions
                            </span>
                        @endif
                    </div>

                    <h2 class="text-2xl font-black text-primary-text tracking-tight truncate">
                        {{ $bug->title }}
                    </h2>
                </div>
            </div>

            <!-- Actions Row -->
            <div class="flex items-center justify-between gap-4 flex-wrap pt-2 border-t border-border">
                <!-- Status Transitions -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-secondary-text font-semibold uppercase mr-1">Status:</span>

                    @if($bug->status === 'open')
                        <form action="{{ route('projects.bugs.updateStatus', [$project, $bug]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <input type="hidden" name="version" value="{{ $bug->version }}">
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition">
                                Start Progress
                            </button>
                        </form>
                        <form action="{{ route('projects.bugs.updateStatus', [$project, $bug]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="fixed">
                            <input type="hidden" name="version" value="{{ $bug->version }}">
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition">
                                Mark Fixed
                            </button>
                        </form>
                        <form action="{{ route('projects.bugs.updateStatus', [$project, $bug]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="wontfix">
                            <input type="hidden" name="version" value="{{ $bug->version }}">
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-surface border border-border text-secondary-text hover:text-primary-text transition">
                                Won't Fix
                            </button>
                        </form>
                    @elseif($bug->status === 'in_progress')
                        <form action="{{ route('projects.bugs.updateStatus', [$project, $bug]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="fixed">
                            <input type="hidden" name="version" value="{{ $bug->version }}">
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition">
                                Mark Fixed
                            </button>
                        </form>
                        <form action="{{ route('projects.bugs.updateStatus', [$project, $bug]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="open">
                            <input type="hidden" name="version" value="{{ $bug->version }}">
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-surface border border-border text-secondary-text hover:text-primary-text transition">
                                Move back to Open
                            </button>
                        </form>
                    @elseif($bug->status === 'fixed' || $bug->status === 'wontfix')
                        <form action="{{ route('projects.bugs.updateStatus', [$project, $bug]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="open">
                            <input type="hidden" name="version" value="{{ $bug->version }}">
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-600 hover:bg-amber-500 text-white transition">
                                Reopen Bug
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Secondary Actions -->
                <div class="flex items-center gap-2">
                    <form action="{{ route('projects.bugs.convertToTask', [$project, $bug]) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3.5 py-1.5 text-xs font-bold rounded-lg bg-primary hover:bg-primary-hover text-white shadow-sm transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Convert to Kanban Task
                        </button>
                    </form>

                    @if($project->owner_id === auth()->id())
                        <form action="{{ route('projects.bugs.destroy', [$project, $bug]) }}" method="POST" onsubmit="return confirm('Delete this bug permanently?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-secondary-text hover:text-red-400 transition" title="Delete Bug">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-[11px] font-semibold text-secondary-text uppercase tracking-wider">Occurrences</div>
                <div class="text-2xl font-black text-primary-text mt-1">{{ number_format($bug->occurrence_count) }}</div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm {{ $bug->regressions > 0 ? 'border-red-500/40 bg-red-500/5' : '' }}">
                <div class="text-[11px] font-semibold {{ $bug->regressions > 0 ? 'text-red-400' : 'text-secondary-text' }} uppercase tracking-wider">
                    Regressions
                </div>
                <div class="text-2xl font-black {{ $bug->regressions > 0 ? 'text-red-400' : 'text-primary-text' }} mt-1">
                    {{ $bug->regressions }}
                </div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-[11px] font-semibold text-secondary-text uppercase tracking-wider">Last Seen</div>
                <div class="text-sm font-bold text-primary-text mt-2 truncate" title="{{ $bug->last_seen_at?->toDayDateTimeString() }}">
                    {{ $bug->last_seen_at ? $bug->last_seen_at->diffForHumans() : 'Unknown' }}
                </div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-[11px] font-semibold text-secondary-text uppercase tracking-wider">Fingerprint</div>
                <div class="text-xs font-mono font-bold text-primary-text mt-2 truncate flex items-center justify-between">
                    <span>{{ $bug->fingerprint }}</span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $bug->fingerprint }}'); alert('Fingerprint copied!');"
                            class="text-secondary-text hover:text-primary transition" title="Copy Fingerprint">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-[11px] font-semibold text-secondary-text uppercase tracking-wider">Error Type</div>
                <div class="text-xs font-mono font-bold text-primary-text mt-2 truncate">
                    {{ $bug->error_type }}
                </div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-[11px] font-semibold text-secondary-text uppercase tracking-wider">State Version</div>
                <div class="text-sm font-mono text-secondary-text mt-2">
                    v{{ $bug->version }}
                </div>
            </div>
        </div>

        <!-- Raw Error Message and Stack Trace Section -->
        <div class="card p-6 rounded-xl border border-border bg-surface shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-primary-text uppercase tracking-wider">Latest Error Payload</h3>
                @if($bug->raw_stack)
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('raw_stack_block').innerText); alert('Stack trace copied!');"
                            class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded bg-background border border-border text-secondary-text hover:text-primary-text transition">
                        Copy Stack Trace
                    </button>
                @endif
            </div>

            @if($bug->raw_message)
                <div>
                    <div class="text-xs font-semibold text-secondary-text mb-1">Message</div>
                    <div class="p-3 bg-background rounded-lg border border-border font-mono text-xs text-red-300 break-words">
                        {{ $bug->raw_message }}
                    </div>
                </div>
            @endif

            @if($bug->raw_stack)
                <div>
                    <div class="text-xs font-semibold text-secondary-text mb-1">Stack Trace</div>
                    <pre id="raw_stack_block" class="p-4 bg-black/70 rounded-lg border border-border font-mono text-xs text-slate-300 overflow-x-auto max-h-96"><code>{{ $bug->raw_stack }}</code></pre>
                </div>
            @endif
        </div>

        <!-- Immutable Event Timeline -->
        <div class="card p-6 rounded-xl border border-border bg-surface shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-primary-text uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Bug Timeline & Event Log
            </h3>
            <p class="text-xs text-secondary-text">Append-only audit trail of every crash recurrence, status transition, and regression.</p>

            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-border">
                @forelse($bug->events as $event)
                    <div class="relative flex items-start gap-4">
                        <!-- Timeline Icon Node -->
                        @if($event->type === 'status_changed')
                            @if($event->to_status === 'open' && $event->from_status === 'fixed')
                                <div class="w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-[10px] font-bold shadow-md ring-4 ring-surface absolute -left-6 top-0.5">
                                    !
                                </div>
                            @elseif($event->to_status === 'fixed')
                                <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold shadow-md ring-4 ring-surface absolute -left-6 top-0.5">
                                    ✓
                                </div>
                            @else
                                <div class="w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center text-[10px] font-bold shadow-md ring-4 ring-surface absolute -left-6 top-0.5">
                                    &bull;
                                </div>
                            @endif
                        @else
                            <div class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold shadow-md ring-4 ring-surface absolute -left-6 top-0.5">
                                #
                            </div>
                        @endif

                        <!-- Timeline Content Card -->
                        <div class="flex-1 bg-background p-3.5 rounded-lg border border-border">
                            <div class="flex items-center justify-between text-xs text-secondary-text mb-1">
                                <span class="font-semibold text-primary-text">
                                    @if($event->type === 'status_changed')
                                        @if($event->to_status === 'open' && $event->from_status === 'fixed')
                                            <span class="text-red-400 font-bold">Automatic Regression Detected</span>
                                        @else
                                            Status Changed to <span class="text-primary font-bold">{{ ucfirst(str_replace('_', ' ', $event->to_status)) }}</span>
                                        @endif
                                    @else
                                        Crash Occurrence #{{ $event->occurrence_count }}
                                    @endif
                                </span>
                                <span>{{ $event->created_at->format('M d, Y H:i:s') }} ({{ $event->created_at->diffForHumans() }})</span>
                            </div>

                            @if($event->type === 'status_changed')
                                <div class="text-xs text-secondary-text">
                                    Status moved from <strong class="text-primary-text">{{ $event->from_status }}</strong> to <strong class="text-primary-text">{{ $event->to_status }}</strong>
                                    @if($event->user)
                                        by <strong class="text-primary-text">{{ $event->user->name }}</strong>
                                    @else
                                        by <span class="text-red-400 font-medium">DevLoop Regression Engine</span>
                                    @endif
                                </div>
                            @else
                                <div class="text-xs text-secondary-text">
                                    Identical fingerprint matched. Occurrence counter incremented to {{ $event->occurrence_count }}.
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-secondary-text italic">No events recorded yet.</p>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>

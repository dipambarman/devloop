<!-- Slack Webhooks Modal -->
<div x-show="showSlackModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-black/70 backdrop-blur-sm" @click="showSlackModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-surface border border-border rounded-xl shadow-2xl relative z-10">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-border">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-primary-text">Slack Webhook Integrations</h3>
                        <p class="text-xs text-secondary-text">Broadcast real-time error notifications, fatal crashes, and regressions directly to Slack.</p>
                    </div>
                </div>
                <button @click="showSlackModal = false" class="text-secondary-text hover:text-primary-text text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-5 max-h-[70vh] overflow-y-auto pr-1">
                <!-- Existing Webhooks List -->
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-secondary-text mb-2.5">Active Integrations ({{ $project->slackWebhooks->count() }})</h4>
                    
                    @if($project->slackWebhooks->isEmpty())
                        <div class="p-4 rounded-xl bg-background border border-border text-center">
                            <p class="text-xs text-secondary-text">No Slack webhooks configured for this project yet. Add your Slack Incoming Webhook URL below to start receiving real-time crash alerts.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($project->slackWebhooks as $webhook)
                                <div class="p-3.5 rounded-xl bg-background border border-border hover:border-border/80 transition space-y-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-primary-text truncate">{{ $webhook->name }}</span>
                                                @if($webhook->channel)
                                                    <span class="text-xs font-mono text-secondary-text">{{ $webhook->channel }}</span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $webhook->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-secondary-text/10 text-secondary-text border border-border' }}">
                                                    {{ $webhook->is_active ? 'Active' : 'Disabled' }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] font-mono text-secondary-text/80 truncate mt-0.5">
                                                {{ substr($webhook->webhook_url, 0, 32) }}...{{ substr($webhook->webhook_url, -8) }}
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <!-- Test Ping Form -->
                                            <form action="{{ route('projects.slackWebhooks.test', [$project, $webhook]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-surface hover:bg-surface-hover border border-border text-primary-text transition shadow-sm">
                                                    Test Ping
                                                </button>
                                            </form>

                                            <!-- Delete Form -->
                                            <form action="{{ route('projects.slackWebhooks.destroy', [$project, $webhook]) }}" method="POST" onsubmit="return confirm('Delete this Slack webhook integration?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-secondary-text hover:text-danger transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Event tags & delivery status -->
                                    <div class="flex items-center justify-between gap-2 flex-wrap pt-1 text-[11px] border-t border-border/40">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @foreach($webhook->events as $evt)
                                                <span class="px-1.5 py-0.5 rounded bg-surface border border-border text-[10px] text-secondary-text font-mono">
                                                    {{ $evt }}
                                                </span>
                                            @endforeach
                                        </div>

                                        <div class="text-[11px] text-secondary-text">
                                            @if($webhook->last_triggered_at)
                                                <span>Last ping: {{ $webhook->last_triggered_at->diffForHumans() }}</span>
                                                <span class="font-mono {{ $webhook->last_status_code === 200 ? 'text-emerald-400' : 'text-danger' }}">
                                                    ({{ $webhook->last_status_code ?: 'Err' }})
                                                </span>
                                            @else
                                                <span class="italic">Never triggered</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Add Webhook Form -->
                <div class="p-4 rounded-xl bg-surface-hover border border-border">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-primary-text mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Slack Webhook
                    </h4>

                    <form action="{{ route('projects.slackWebhooks.store', $project) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-secondary-text mb-1">Webhook Name</label>
                                <x-text-input name="name" placeholder="e.g. #dev-alerts" class="w-full text-xs py-1.5" required />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary-text mb-1">Slack Channel (Optional)</label>
                                <x-text-input name="channel" placeholder="#alerts" class="w-full text-xs py-1.5" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-secondary-text mb-1">Incoming Webhook URL</label>
                            <x-text-input name="webhook_url" type="url" placeholder="https://hooks.slack.com/services/T00/B00/XXXX" class="w-full text-xs py-1.5 font-mono" required />
                            <p class="text-[10px] text-secondary-text mt-1">Generate via Slack App &gt; Incoming Webhooks &gt; Add New Webhook to Workspace.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-secondary-text mb-1.5">Trigger Events</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-background border border-border cursor-pointer hover:border-primary/50 transition">
                                    <input type="checkbox" name="events[]" value="bug.created" checked class="rounded border-border text-primary focus:ring-primary">
                                    <span class="text-primary-text">New Bug Discovered</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-background border border-border cursor-pointer hover:border-primary/50 transition">
                                    <input type="checkbox" name="events[]" value="bug.regression" checked class="rounded border-border text-primary focus:ring-primary">
                                    <span class="text-primary-text">Regression Detected</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-background border border-border cursor-pointer hover:border-primary/50 transition">
                                    <input type="checkbox" name="events[]" value="bug.critical" checked class="rounded border-border text-primary focus:ring-primary">
                                    <span class="text-primary-text">Critical Fatal Crashes</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-background border border-border cursor-pointer hover:border-primary/50 transition">
                                    <input type="checkbox" name="events[]" value="bug.status_changed" class="rounded border-border text-primary focus:ring-primary">
                                    <span class="text-primary-text">Bug Status Transitions</span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <x-primary-button class="!text-xs !py-1.5">
                                Save Webhook Integration
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="flex justify-end pt-4 mt-4 border-t border-border">
                <button type="button" @click="showSlackModal = false" class="px-4 py-2 text-xs font-medium rounded-lg bg-surface border border-border text-secondary-text hover:text-primary-text">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

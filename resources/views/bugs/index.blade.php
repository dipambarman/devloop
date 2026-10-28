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
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold tracking-tight text-primary-text truncate">{{ $project->name }} &mdash; Error Tracker</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                            DevLoop Mini Engine
                        </span>
                    </div>
                    <p class="text-xs text-secondary-text mt-0.5">Persistent bug identity, normalized fingerprinting & automatic regression detection.</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('projects.show', $project) }}">
                        <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                            Tasks
                        </x-primary-button>
                    </a>
                    <a href="{{ route('projects.board', $project) }}">
                        <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                            Board
                        </x-primary-button>
                    </a>
                    <a href="{{ route('projects.calendar', $project) }}">
                        <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                            Calendar
                        </x-primary-button>
                    </a>
                    <a href="{{ route('projects.discussions.index', $project) }}">
                        <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                            Discussions
                        </x-primary-button>
                    </a>
                    <x-primary-button class="!bg-primary/20 !border-primary/40 !text-primary">
                        Errors ({{ $stats['total'] }})
                    </x-primary-button>
                </div>

                <div class="flex items-center gap-2" x-data="{ showIngestModal: false, showSdkModal: false, showSlackModal: false }">
                    <button @click="showSlackModal = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-surface hover:bg-surface-hover border border-border text-secondary-text hover:text-primary-text transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>
                        </svg>
                        Slack Webhooks ({{ $project->slackWebhooks->count() }})
                    </button>

                    <button @click="showSdkModal = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-surface hover:bg-surface-hover border border-border text-secondary-text hover:text-primary-text transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        API Key & SDK
                    </button>

                    <button @click="showIngestModal = true" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-500 text-white transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ingest Error
                    </button>

                    <!-- Ingest Modal -->
                    <div x-show="showIngestModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity bg-black/70 backdrop-blur-sm" @click="showIngestModal = false"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                            <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-surface border border-border rounded-xl shadow-2xl relative z-10">
                                <div class="flex items-center justify-between pb-4 mb-4 border-b border-border">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center font-bold">!</div>
                                        <h3 class="text-lg font-bold text-primary-text">Ingest Runtime Error</h3>
                                    </div>
                                    <button @click="showIngestModal = false" class="text-secondary-text hover:text-primary-text">&times;</button>
                                </div>

                                <form action="{{ route('projects.bugs.store', $project) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-secondary-text mb-1">Error Message or Exception *</label>
                                        <input type="text" name="message" required placeholder="e.g. TypeError: Cannot read properties of undefined (reading 'map')"
                                               class="w-full text-sm bg-background border border-border rounded-lg px-3 py-2 text-primary-text focus:border-primary focus:ring-1 focus:ring-primary">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-secondary-text mb-1">Severity</label>
                                        <select name="severity" class="w-full text-sm bg-background border border-border rounded-lg px-3 py-2 text-primary-text focus:border-primary focus:ring-1 focus:ring-primary">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-secondary-text mb-1">Raw Stack Trace (Optional)</label>
                                        <textarea name="stack" rows="6" placeholder="Paste full stack trace here... (line numbers and memory pointers will be automatically stripped before fingerprinting)"
                                                  class="w-full font-mono text-xs bg-background border border-border rounded-lg px-3 py-2 text-primary-text focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                                    </div>

                                    <div class="flex justify-end gap-3 pt-3">
                                        <button type="button" @click="showIngestModal = false" class="px-4 py-2 text-xs font-medium rounded-lg bg-surface border border-border text-secondary-text hover:text-primary-text">Cancel</button>
                                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-500 text-white">Fingerprint & Ingest</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- SDK & API Key Modal -->
                    <div x-show="showSdkModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity bg-black/70 backdrop-blur-sm" @click="showSdkModal = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                            <div class="inline-block w-full max-w-3xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-surface border border-border rounded-xl shadow-2xl relative z-10"
                                 x-data="{ tab: 'js' }">
                                <div class="flex items-center justify-between pb-4 mb-4 border-b border-border">
                                    <div>
                                        <h3 class="text-lg font-bold text-primary-text">DevLoop Mini Reporter SDK</h3>
                                        <p class="text-xs text-secondary-text">Send unhandled exceptions straight into this project with automated fingerprinting & deduplication.</p>
                                    </div>
                                    <button @click="showSdkModal = false" class="text-secondary-text hover:text-primary-text">&times;</button>
                                </div>

                                <!-- Key Management Card -->
                                <div class="p-4 mb-5 rounded-lg bg-background border border-border">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div>
                                            <div class="text-xs font-semibold text-primary-text">Project Ingest Key</div>
                                            <div class="text-xs text-secondary-text">
                                                @if($project->ingest_key_hash)
                                                    <span class="text-emerald-400 font-mono">Active Key Configured (SHA-256: {{ substr($project->ingest_key_hash, 0, 12) }}...)</span>
                                                @else
                                                    <span class="text-amber-400">No active key configured. Generate one below to enable automated error reporting.</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            @if($project->owner_id === auth()->id())
                                                <form action="{{ route('projects.keys.store', $project) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-primary hover:bg-primary-hover text-white transition">
                                                        {{ $project->ingest_key_hash ? 'Rotate Key' : 'Generate Key' }}
                                                    </button>
                                                </form>

                                                @if($project->ingest_key_hash)
                                                    <form action="{{ route('projects.keys.destroy', $project) }}" method="POST" onsubmit="return confirm('Revoke this key? Apps using it will stop sending errors.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 transition">
                                                            Revoke
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    @if(session('plaintext_key'))
                                        <div class="mt-3 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-lg">
                                            <div class="text-xs font-bold text-emerald-400 mb-1">Save your Ingest API Key now! (Shown only once)</div>
                                            <div class="flex items-center gap-2">
                                                <input type="text" readonly value="{{ session('plaintext_key') }}" id="plaintext_key_input"
                                                       class="w-full font-mono text-xs bg-black/40 border border-emerald-500/30 rounded px-2.5 py-1 text-emerald-300">
                                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('plaintext_key_input').value); alert('Key copied to clipboard!');"
                                                        class="px-2.5 py-1 text-xs font-medium bg-emerald-600 hover:bg-emerald-500 text-white rounded">
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Code Snippet Tabs -->
                                <div class="flex gap-2 border-b border-border pb-2 mb-3 overflow-x-auto">
                                    <button @click="tab = 'browser'" :class="tab === 'browser' ? 'text-primary border-primary' : 'text-secondary-text border-transparent'" class="px-3 py-1 text-xs font-semibold border-b-2 transition whitespace-nowrap">🌐 Browser Script (Zero-Config)</button>
                                    <button @click="tab = 'react'" :class="tab === 'react' ? 'text-primary border-primary' : 'text-secondary-text border-transparent'" class="px-3 py-1 text-xs font-semibold border-b-2 transition whitespace-nowrap">⚡ React / Next.js / Vue</button>
                                    <button @click="tab = 'laravel'" :class="tab === 'laravel' ? 'text-primary border-primary' : 'text-secondary-text border-transparent'" class="px-3 py-1 text-xs font-semibold border-b-2 transition whitespace-nowrap">🐘 Laravel 11 / PHP</button>
                                    <button @click="tab = 'curl'" :class="tab === 'curl' ? 'text-primary border-primary' : 'text-secondary-text border-transparent'" class="px-3 py-1 text-xs font-semibold border-b-2 transition whitespace-nowrap">📡 cURL & Async API</button>
                                </div>

                                <!-- Browser Script Tab -->
                                <div x-show="tab === 'browser'" class="space-y-3">
                                    <p class="text-xs text-secondary-text">Paste this single tag before the closing <code>&lt;/head&gt;</code> or <code>&lt;/body&gt;</code> tag of any web application. Automatically captures runtime crashes, unhandled promises, and viewport telemetry with client-side deduplication.</p>
                                    <pre class="p-3.5 bg-black/60 rounded-lg text-xs font-mono text-slate-300 overflow-x-auto border border-border"><code>&lt;!-- DevLoop Client Error Listener --&gt;
&lt;script 
  src="{{ url('/js/devloop-tracker.js') }}" 
  data-key="{{ session('plaintext_key') ?? 'YOUR_INGEST_KEY_HERE' }}"
  data-endpoint="{{ url('/api/v1/ingest') }}"
  data-environment="production"&gt;
&lt;/script&gt;</code></pre>
                                </div>

                                <!-- React / Vue / JS Tab -->
                                <div x-show="tab === 'react'" style="display: none;" class="space-y-3">
                                    <p class="text-xs text-secondary-text">Initialize programmatically inside <code>main.jsx</code>, <code>index.js</code>, or your App entrypoint.</p>
                                    <pre class="p-3.5 bg-black/60 rounded-lg text-xs font-mono text-slate-300 overflow-x-auto border border-border"><code>// 1. Initialize Tracker
DevLoop.init({
  key: '{{ session('plaintext_key') ?? 'YOUR_INGEST_KEY_HERE' }}',
  endpoint: '{{ url('/api/v1/ingest') }}',
  environment: process.env.NODE_ENV || 'production',
  release: 'v1.0.0'
});

// 2. Set Current User Context (Optional)
DevLoop.setUser({ id: 42, email: 'user@example.com' });

// 3. Manual Error Capture (Try / Catch or Error Boundary)
try {
  runRiskyOperation();
} catch (error) {
  DevLoop.captureException(error, {
    severity: 'critical',
    context: { feature: 'checkout_modal' }
  });
}</code></pre>
                                </div>

                                <!-- Laravel / PHP Tab -->
                                <div x-show="tab === 'laravel'" style="display: none;" class="space-y-3">
                                    <p class="text-xs text-secondary-text">Use the built-in <code>App\Support\DevLoopReporter</code> helper in <code>bootstrap/app.php</code>:</p>
                                    <pre class="p-3.5 bg-black/60 rounded-lg text-xs font-mono text-slate-300 overflow-x-auto border border-border"><code>// In bootstrap/app.php (Laravel 11)
use App\Support\DevLoopReporter;

$exceptions->reportable(function (Throwable $e) {
    DevLoopReporter::report(
        $e,
        apiKey: '{{ session('plaintext_key') ?? 'YOUR_INGEST_KEY_HERE' }}',
        endpoint: '{{ url('/api/v1/ingest') }}'
    );
});</code></pre>
                                </div>

                                <!-- cURL / Async API Tab -->
                                <div x-show="tab === 'curl'" style="display: none;" class="space-y-3">
                                    <p class="text-xs text-secondary-text">Send single or batched error events. Add <code>X-DevLoop-Async: 1</code> header for high-throughput background queueing (202 Accepted):</p>
                                    <pre class="p-3.5 bg-black/60 rounded-lg text-xs font-mono text-slate-300 overflow-x-auto border border-border"><code>curl -X POST "{{ url('/api/v1/ingest') }}" \
  -H "Content-Type: application/json" \
  -H "X-DevLoop-Key: {{ session('plaintext_key') ?? 'YOUR_INGEST_KEY_HERE' }}" \
  -H "X-DevLoop-Async: 1" \
  -d '{
    "message": "DatabaseTimeout: Connection to replica timed out",
    "stack": "at DB.query (/var/www/src/db.js:84:12)",
    "severity": "critical",
    "environment": "production",
    "url": "https://api.myapp.com/v1/orders",
    "context": { "retry_count": 3, "service": "orders" }
  }'</code></pre>
                                </div>

                                <!-- Live Ingest Ping Test Utility -->
                                <div class="mt-5 p-4 rounded-lg bg-surface-hover border border-border space-y-3"
                                     x-data="{ pingKey: '{{ session('plaintext_key') ?? '' }}', pingStatus: null, pingLoading: false }">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs font-bold text-primary-text block">Live Ingest Ping Test</span>
                                            <span class="text-[11px] text-secondary-text">Verify that your ingest pipeline and error listener are working right now.</span>
                                        </div>
                                        <button type="button" 
                                                :disabled="pingLoading || !pingKey"
                                                @click="
                                                    pingLoading = true;
                                                    pingStatus = 'Sending test error ping...';
                                                    fetch('{{ url('/api/v1/ingest') }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-DevLoop-Key': pingKey,
                                                            'X-DevLoop-Async': '0'
                                                        },
                                                        body: JSON.stringify({
                                                            message: 'TestError: DevLoop Ingest Live Verification Ping',
                                                            stack: 'at DevLoopSdkModal.testPing (' + window.location.href + ':1:1)',
                                                            severity: 'medium',
                                                            environment: 'local',
                                                            url: window.location.href,
                                                            context: { test: true, initiated_at: new Date().toISOString() }
                                                        })
                                                    })
                                                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                                                    .then(result => {
                                                        pingLoading = false;
                                                        if (result.ok) {
                                                            pingStatus = '✓ Success! Ingested error #' + result.data.fingerprint + ' (' + result.data.title + '). Reloading...';
                                                            setTimeout(() => window.location.reload(), 1500);
                                                        } else {
                                                            pingStatus = '✗ Failed: ' + (result.data.error || 'Check your key');
                                                        }
                                                    })
                                                    .catch(err => {
                                                        pingLoading = false;
                                                        pingStatus = '✗ Network Error: ' + err.message;
                                                    });
                                                "
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white transition">
                                            <span x-show="!pingLoading">Send Test Ping</span>
                                            <span x-show="pingLoading" style="display: none;">Sending...</span>
                                        </button>
                                    </div>

                                    @if(!session('plaintext_key'))
                                        <div>
                                            <label class="block text-[11px] text-secondary-text mb-1">Enter your active Ingest Key to test:</label>
                                            <input type="text" x-model="pingKey" placeholder="dl_ingest_..." 
                                                   class="w-full text-xs font-mono bg-background border border-border rounded px-2.5 py-1 text-primary-text">
                                        </div>
                                    @endif

                                    <div x-show="pingStatus" class="text-xs font-medium" :class="pingStatus && pingStatus.startsWith('✓') ? 'text-emerald-400' : (pingStatus && pingStatus.startsWith('✗') ? 'text-red-400' : 'text-blue-400')" x-text="pingStatus"></div>
                                </div>

                                <div class="flex justify-end pt-4 mt-2">
                                    <button type="button" @click="showSdkModal = false" class="px-4 py-2 text-xs font-medium rounded-lg bg-surface border border-border text-secondary-text hover:text-primary-text">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slack Webhook Integration Modal -->
                    @include('projects.partials.slack-webhooks-modal')
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Error Summary Statistics Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-xs text-secondary-text uppercase font-semibold tracking-wider">Total Unique Bugs</div>
                <div class="text-2xl font-black text-primary-text mt-1">{{ number_format($stats['total']) }}</div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-xs text-secondary-text uppercase font-semibold tracking-wider">Open Errors</div>
                <div class="text-2xl font-black text-amber-400 mt-1">{{ number_format($stats['open']) }}</div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-xs text-secondary-text uppercase font-semibold tracking-wider">In Progress</div>
                <div class="text-2xl font-black text-blue-400 mt-1">{{ number_format($stats['in_progress']) }}</div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-xs text-secondary-text uppercase font-semibold tracking-wider">Fixed</div>
                <div class="text-2xl font-black text-emerald-400 mt-1">{{ number_format($stats['fixed']) }}</div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm {{ $stats['regressions'] > 0 ? 'border-red-500/40 bg-red-500/5' : '' }}">
                <div class="text-xs {{ $stats['regressions'] > 0 ? 'text-red-400 font-bold' : 'text-secondary-text' }} uppercase tracking-wider flex items-center gap-1">
                    @if($stats['regressions'] > 0)
                        <span>🔥 Regressions</span>
                    @else
                        <span>Regressions</span>
                    @endif
                </div>
                <div class="text-2xl font-black {{ $stats['regressions'] > 0 ? 'text-red-400' : 'text-primary-text' }} mt-1">
                    {{ number_format($stats['regressions']) }}
                </div>
            </div>

            <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm">
                <div class="text-xs text-secondary-text uppercase font-semibold tracking-wider">Total Occurrences</div>
                <div class="text-2xl font-black text-primary-text mt-1">{{ number_format($stats['total_occurrences']) }}</div>
            </div>
        </div>

        <!-- Filter and Search Bar -->
        <div class="card p-4 rounded-xl border border-border bg-surface shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <form method="GET" action="{{ route('projects.bugs.index', $project) }}" class="flex flex-wrap items-center gap-3 w-full">
                <!-- Status Filter -->
                <div class="flex items-center gap-1 bg-background p-1 rounded-lg border border-border text-xs">
                    <a href="{{ route('projects.bugs.index', array_merge(['project' => $project], request()->except('status'))) }}"
                       class="px-2.5 py-1 rounded {{ !request('status') ? 'bg-primary text-white font-semibold' : 'text-secondary-text hover:text-primary-text' }}">
                        All Status
                    </a>
                    <a href="{{ route('projects.bugs.index', array_merge(['project' => $project], request()->except('status'), ['status' => 'open'])) }}"
                       class="px-2.5 py-1 rounded {{ request('status') === 'open' ? 'bg-amber-500 text-white font-semibold' : 'text-secondary-text hover:text-primary-text' }}">
                        Open
                    </a>
                    <a href="{{ route('projects.bugs.index', array_merge(['project' => $project], request()->except('status'), ['status' => 'in_progress'])) }}"
                       class="px-2.5 py-1 rounded {{ request('status') === 'in_progress' ? 'bg-blue-500 text-white font-semibold' : 'text-secondary-text hover:text-primary-text' }}">
                        In Progress
                    </a>
                    <a href="{{ route('projects.bugs.index', array_merge(['project' => $project], request()->except('status'), ['status' => 'fixed'])) }}"
                       class="px-2.5 py-1 rounded {{ request('status') === 'fixed' ? 'bg-emerald-500 text-white font-semibold' : 'text-secondary-text hover:text-primary-text' }}">
                        Fixed
                    </a>
                </div>

                <!-- Severity Filter -->
                <select name="severity" onchange="this.form.submit()" class="text-xs bg-background border border-border rounded-lg px-2.5 py-1.5 text-primary-text focus:border-primary">
                    <option value="">All Severities</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                </select>

                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by error message, type, or fingerprint..."
                           class="w-full text-xs bg-background border border-border rounded-lg pl-8 pr-3 py-1.5 text-primary-text focus:border-primary">
                    <svg class="w-4 h-4 text-secondary-text absolute left-2.5 top-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                @if(request()->anyFilled(['status', 'severity', 'search']))
                    <a href="{{ route('projects.bugs.index', $project) }}" class="text-xs text-secondary-text hover:text-primary-text underline">
                        Clear Filters
                    </a>
                @endif
            </form>
        </div>

        <!-- Bug Card Listing -->
        <div class="space-y-3">
            @forelse($bugs as $bug)
                <div class="card p-4 rounded-xl border border-border bg-surface hover:border-primary/50 transition shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Severity Badge -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider
                                @if($bug->severity === 'critical') bg-red-500/15 text-red-400 border border-red-500/30
                                @elseif($bug->severity === 'high') bg-orange-500/15 text-orange-400 border border-orange-500/30
                                @elseif($bug->severity === 'medium') bg-amber-500/15 text-amber-400 border border-amber-500/30
                                @else bg-slate-500/15 text-slate-300 border border-slate-500/30
                                @endif">
                                {{ $bug->severity }}
                            </span>

                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                                @if($bug->status === 'open') bg-amber-500/15 text-amber-300 border border-amber-500/30
                                @elseif($bug->status === 'in_progress') bg-blue-500/15 text-blue-300 border border-blue-500/30
                                @elseif($bug->status === 'fixed') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                                @else bg-slate-500/15 text-slate-400 border border-slate-500/30
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $bug->status)) }}
                            </span>

                            <!-- Error Type -->
                            <span class="text-xs font-mono font-medium text-secondary-text">
                                {{ $bug->error_type }}
                            </span>

                            <!-- Fingerprint -->
                            <span class="text-[11px] font-mono text-secondary-text/80 bg-background px-1.5 py-0.5 rounded border border-border">
                                #{{ $bug->fingerprint }}
                            </span>

                            @if($bug->regressions > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-600/20 text-red-300 border border-red-500/30 animate-pulse">
                                    🔥 {{ $bug->regressions }} {{ Str::plural('Regression', $bug->regressions) }}
                                </span>
                            @endif
                        </div>

                        <!-- Bug Title -->
                        <h4 class="text-base font-bold text-primary-text hover:text-primary transition truncate">
                            <a href="{{ route('projects.bugs.show', [$project, $bug]) }}">
                                {{ $bug->title }}
                            </a>
                        </h4>

                        <!-- Last Seen & Occurrences -->
                        <div class="flex items-center gap-4 text-xs text-secondary-text">
                            <span>Seen <strong>{{ $bug->occurrence_count }}</strong> {{ Str::plural('time', $bug->occurrence_count) }}</span>
                            <span>&bull;</span>
                            <span>Last seen {{ $bug->last_seen_at ? $bug->last_seen_at->diffForHumans() : 'never' }}</span>
                            @if($bug->assignee)
                                <span>&bull;</span>
                                <span class="text-primary-text">Assigned to {{ $bug->assignee->name }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Right Quick Actions -->
                    <div class="flex items-center gap-2 shrink-0">
                        <form action="{{ route('projects.bugs.convertToTask', [$project, $bug]) }}" method="POST">
                            @csrf
                            <button type="submit" title="Convert this error into a Kanban task"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Convert to Task
                            </button>
                        </form>

                        <a href="{{ route('projects.bugs.show', [$project, $bug]) }}"
                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-surface-hover hover:bg-border text-primary-text border border-border transition">
                            View Details &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="card p-12 text-center rounded-xl border border-dashed border-border bg-surface/50">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-primary-text">No Errors Logged</h3>
                    <p class="text-sm text-secondary-text max-w-md mx-auto mt-1 mb-4">
                        Your project is running cleanly! When runtime errors occur or AI hallucinations repeat bugs, ingest them manually or connect your app via the Ingest API Key.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($bugs->hasPages())
            <div class="mt-6">
                {{ $bugs->links() }}
            </div>
        @endif

    </div>
</x-app-layout>

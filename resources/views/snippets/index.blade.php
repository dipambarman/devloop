<x-app-layout>
    <x-slot name="title">Snippets</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">Snippets</h2>
                    <p class="text-sm text-secondary-text mt-1">Reusable code blocks and solutions.</p>
                </div>
            </div>
            <a href="{{ route('snippets.create') }}">
                <x-primary-button>
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Snippet
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    <!-- Filters -->
    <x-card class="mb-6 !p-4">
        <form action="{{ route('snippets.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <x-text-input type="text" name="search" :value="request('search')" placeholder="Search snippets..." class="w-full !pl-10 !py-2" />
            </div>

            <select name="language" class="w-full sm:w-48 border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm">
                <option value="">All Languages</option>
                <option value="php" {{ request('language') === 'php' ? 'selected' : '' }}>PHP</option>
                <option value="javascript" {{ request('language') === 'javascript' ? 'selected' : '' }}>JavaScript</option>
                <option value="html" {{ request('language') === 'html' ? 'selected' : '' }}>HTML</option>
                <option value="css" {{ request('language') === 'css' ? 'selected' : '' }}>CSS</option>
                <option value="bash" {{ request('language') === 'bash' ? 'selected' : '' }}>Bash</option>
                <option value="sql" {{ request('language') === 'sql' ? 'selected' : '' }}>SQL</option>
                <option value="plaintext" {{ request('language') === 'plaintext' ? 'selected' : '' }}>Plain Text</option>
            </select>

            <select name="project_id" class="w-full sm:w-48 border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>

            <x-primary-button type="submit" class="!py-2">Search</x-primary-button>
            @if(request()->hasAny(['search', 'language', 'project_id']))
                <a href="{{ route('snippets.index') }}" class="text-sm text-secondary-text hover:text-primary-text transition-colors flex items-center">Clear</a>
            @endif
        </form>
    </x-card>

    @if($snippets->count())
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach($snippets as $snippet)
                <x-card class="flex flex-col group">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-bold text-primary-text">{{ $snippet->title }}</h3>
                            <x-badge color="primary" class="!text-[10px] uppercase tracking-wider">
                                {{ $snippet->language }}
                            </x-badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="copyToClipboard(`{{ base64_encode($snippet->code) }}`)" class="text-secondary-text hover:text-primary transition-colors p-1" title="Copy to clipboard">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <a href="{{ route('snippets.edit', $snippet) }}" class="text-secondary-text hover:text-primary transition-colors p-1" title="Edit snippet">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <div class="bg-[#1e1e1e] rounded-xl p-4 overflow-x-auto text-sm font-mono text-gray-300 relative group-hover:ring-1 ring-border transition-all">
                        <pre><code class="language-{{ $snippet->language }}">{{ $snippet->code }}</code></pre>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-border flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if($snippet->project)
                                <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $snippet->project->color }}"></div>
                                <span class="text-xs text-secondary-text">{{ $snippet->project->name }}</span>
                            @else
                                <svg class="w-3 h-3 text-secondary-text shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs text-secondary-text">Global</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-secondary-text">Updated {{ $snippet->updated_at->diffForHumans() }}</span>
                    </div>
                </x-card>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $snippets->withQueryString()->links() }}
        </div>
    @else
        <x-empty-state
            title="No snippets found"
            description="Store reusable code blocks to speed up your workflow."
        >
            <x-slot name="action">
                @if(request()->hasAny(['search', 'language', 'project_id']))
                    <a href="{{ route('snippets.index') }}">
                        <x-primary-button class="!bg-surface !border-border !text-primary-text">Clear Filters</x-primary-button>
                    </a>
                @else
                    <a href="{{ route('snippets.create') }}">
                        <x-primary-button>Create Snippet</x-primary-button>
                    </a>
                @endif
            </x-slot>
        </x-empty-state>
    @endif

    <x-slot name="scripts">
        <script>
            function copyToClipboard(base64Code) {
                const code = atob(base64Code);
                navigator.clipboard.writeText(code).then(() => {
                    alert('Copied to clipboard!');
                });
            }
        </script>
        <!-- Include highlight.js for syntax highlighting -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/vs2015.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script>hljs.highlightAll();</script>
    </x-slot>
</x-app-layout>

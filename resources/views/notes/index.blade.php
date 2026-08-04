<x-app-layout>
    <x-slot name="title">Notes</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">Notes</h2>
                    <p class="text-sm text-secondary-text mt-1">Capture ideas, documentation, and thoughts.</p>
                </div>
            </div>
            <a href="{{ route('notes.create') }}">
                <x-primary-button>
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Note
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    <!-- Filters -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <form action="{{ route('notes.index') }}" method="GET" class="flex-1 max-w-sm">
            <select name="project_id" onchange="this.form.submit()" class="w-full border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm">
                <option value="">All Projects & Global Notes</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($notes->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($notes as $note)
                <x-card class="flex flex-col h-full hover:shadow-lg transition-shadow group relative">
                    <div class="absolute top-4 right-4 z-10 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <form action="{{ route('notes.togglePin', $note) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="p-1.5 rounded-lg {{ $note->is_pinned ? 'bg-primary/20 text-primary' : 'bg-surface border border-border text-secondary-text hover:text-primary-text' }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 2a1 1 0 011-1h8a1 1 0 011 1v1.5a2 2 0 01-2 2h-1v4l2.5 3.5A1 1 0 0114 15h-3v4a1 1 0 01-2 0v-4H6a1 1 0 01-.81-1.5L7.7 10V5.5H6.7a2 2 0 01-2-2V2z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <a href="{{ route('notes.edit', $note) }}" class="flex-1 flex flex-col pt-2">
                        <div class="flex items-center gap-2 mb-3 pr-12">
                            @if($note->is_pinned)
                                <svg class="w-4 h-4 text-primary shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 2a1 1 0 011-1h8a1 1 0 011 1v1.5a2 2 0 01-2 2h-1v4l2.5 3.5A1 1 0 0114 15h-3v4a1 1 0 01-2 0v-4H6a1 1 0 01-.81-1.5L7.7 10V5.5H6.7a2 2 0 01-2-2V2z" />
                                </svg>
                            @endif
                            <h3 class="text-lg font-bold text-primary-text line-clamp-1 group-hover:text-primary transition-colors">{{ $note->title }}</h3>
                        </div>
                        <div class="text-sm text-secondary-text line-clamp-4 flex-1 whitespace-pre-wrap">{{ $note->content }}</div>
                        
                        <div class="mt-4 pt-4 border-t border-border flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($note->project)
                                    <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $note->project->color }}"></div>
                                    <span class="text-xs text-secondary-text">{{ $note->project->name }}</span>
                                @else
                                    <svg class="w-3 h-3 text-secondary-text shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs text-secondary-text">Global</span>
                                @endif
                            </div>
                            <span class="text-[10px] text-secondary-text">{{ $note->updated_at->diffForHumans() }}</span>
                        </div>
                    </a>
                </x-card>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $notes->withQueryString()->links() }}
        </div>
    @else
        <x-empty-state
            title="No notes found"
            description="{{ request('project_id') ? 'This project has no notes yet.' : 'Create your first note to start organizing your thoughts.' }}"
        >
            <x-slot name="action">
                <a href="{{ route('notes.create') }}">
                    <x-primary-button>Create Note</x-primary-button>
                </a>
            </x-slot>
        </x-empty-state>
    @endif
</x-app-layout>

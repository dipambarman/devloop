<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.discussions.index', $project) }}" class="text-secondary-text hover:text-primary-text transition-colors" title="Back to discussions">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold tracking-tight text-primary-text truncate" title="{{ $discussion->title }}">
                    {{ $discussion->title }}
                </h2>
                <p class="text-sm text-secondary-text mt-1 flex items-center gap-2">
                    <a href="{{ route('projects.show', $project) }}" class="hover:text-primary transition-colors">
                        {{ $project->name }}
                    </a>
                    <span>/</span>
                    <span>Discussions</span>
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                @can('update', $project)
                    <form action="{{ route('projects.discussions.togglePin', [$project, $discussion]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover" title="{{ $discussion->is_pinned ? 'Unpin Discussion' : 'Pin Discussion' }}">
                            @if($discussion->is_pinned)
                                <svg class="w-4 h-4 mr-2 text-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" /></svg>
                                Unpin
                            @else
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                                Pin
                            @endif
                        </x-primary-button>
                    </form>
                @endcan

                @if($discussion->user_id === auth()->id() || $project->owner_id === auth()->id())
                    <form action="{{ route('projects.discussions.destroy', [$project, $discussion]) }}" method="POST" onsubmit="return confirm('Delete this discussion thread completely?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500/10 border border-transparent rounded-xl font-semibold text-xs text-red-500 uppercase tracking-widest hover:bg-red-500/20 transition">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Original Post -->
        <div class="bg-surface rounded-2xl p-6 border border-border shadow-sm relative">
            @if($discussion->is_pinned)
                <div class="absolute -top-3 -right-3 w-8 h-8 bg-accent rounded-full flex items-center justify-center text-white shadow-lg shadow-accent/20" title="Pinned">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" /></svg>
                </div>
            @endif
            
            <div class="flex items-start gap-4">
                <x-avatar :name="$discussion->user->name" size="md" class="shrink-0" />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <h4 class="font-semibold text-primary-text">{{ $discussion->user->name }}</h4>
                        <span class="text-xs text-secondary-text">{{ $discussion->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                    
                    <div class="prose prose-invert max-w-none text-sm text-primary-text leading-relaxed whitespace-pre-wrap">{{ $discussion->content }}</div>
                </div>
            </div>
        </div>

        <div class="pl-6 border-l-2 border-border/50 ml-4 space-y-6">
            <!-- Replies -->
            @foreach($discussion->replies as $reply)
                <div class="bg-background rounded-xl p-5 border border-border/50 relative group">
                    <div class="flex items-start gap-3">
                        <x-avatar :name="$reply->user->name" size="sm" class="shrink-0" />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold text-sm text-primary-text">{{ $reply->user->name }}</h4>
                                    <span class="text-xs text-secondary-text">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                
                                @if($reply->user_id === auth()->id() || $project->owner_id === auth()->id())
                                    <form action="{{ route('projects.discussions.replies.destroy', [$project, $discussion, $reply]) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Delete this reply?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-secondary-text hover:text-red-500 p-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            
                            <div class="prose prose-invert max-w-none text-sm text-primary-text/90 leading-relaxed whitespace-pre-wrap">{{ $reply->content }}</div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Reply Form -->
            <div class="bg-surface rounded-xl p-5 border border-border shadow-sm mt-8">
                <form action="{{ route('projects.discussions.replies.store', [$project, $discussion]) }}" method="POST">
                    @csrf
                    <div class="flex items-start gap-3">
                        <x-avatar :name="auth()->user()->name" size="sm" class="shrink-0 mt-1" />
                        <div class="flex-1">
                            <textarea name="content" rows="3" placeholder="Write a reply..." class="block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm placeholder-secondary-text/50 text-sm mb-3" required></textarea>
                            <div class="flex justify-end">
                                <x-primary-button>Reply</x-primary-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

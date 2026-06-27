<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $project) }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white font-bold text-sm shadow-lg" style="background-color: {{ $project->color }}">
                {{ strtoupper(substr($project->name, 0, 2)) }}
            </div>

            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">{{ $project->name }} <span class="text-secondary-text font-normal">/ Discussions</span></h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('projects.board', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Board
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-primary-text">Discussions</h3>
                        <div class="flex items-center gap-2">
                            <x-text-input type="text" placeholder="Search discussions..." class="text-sm py-1.5 w-48" />
                        </div>
                    </div>
                </x-slot>

                @if($discussions->count())
                    <div class="space-y-1">
                        @foreach($discussions as $discussion)
                            <div class="flex items-start justify-between p-4 rounded-xl hover:bg-surface-hover transition-colors group border border-transparent hover:border-border cursor-pointer" onclick="window.location='{{ route('projects.discussions.show', [$project, $discussion]) }}'">
                                <div class="flex items-start gap-4 flex-1">
                                    <div class="shrink-0 mt-1 text-primary">
                                        @if($discussion->is_pinned)
                                            <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" /></svg>
                                        @else
                                            <svg class="w-5 h-5 text-secondary-text" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-medium text-primary-text mb-1 truncate group-hover:text-primary transition-colors">
                                            {{ $discussion->title }}
                                        </p>
                                        <div class="flex items-center gap-4 text-xs text-secondary-text">
                                            <div class="flex items-center gap-1.5">
                                                <x-avatar :name="$discussion->user->name" size="xs" />
                                                <span>{{ $discussion->user->name }}</span>
                                            </div>
                                            <span>•</span>
                                            <span>{{ $discussion->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3 shrink-0 ml-4">
                                    <div class="flex items-center gap-1 text-secondary-text text-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        <span class="font-medium">{{ $discussion->replies_count }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-border">
                        {{ $discussions->links() }}
                    </div>
                @else
                    <x-empty-state 
                        title="No discussions yet" 
                        description="Start a new topic to get the team talking."
                    >
                    </x-empty-state>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Start a Discussion</h3>
                </x-slot>

                <form action="{{ route('projects.discussions.store', $project) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="title" value="Topic" class="!text-xs uppercase tracking-wider text-secondary-text mb-1" />
                        <x-text-input id="title" name="title" type="text" class="block w-full text-sm py-2" required placeholder="What's on your mind?" />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="content" value="Message" class="!text-xs uppercase tracking-wider text-secondary-text mb-1" />
                        <textarea id="content" name="content" rows="4" class="block w-full border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm placeholder-secondary-text/50 text-sm" required placeholder="Add more details..."></textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-1" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button>Post Discussion</x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>

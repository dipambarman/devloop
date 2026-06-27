<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">Projects</h2>
                <p class="text-sm text-secondary-text mt-1">Manage all your projects in one place.</p>
            </div>
            <a href="{{ route('projects.create') }}">
                <x-primary-button>
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Project
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    @if($projects->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="group">
                    <x-card class="h-full hover:border-primary/30 glass-hover">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-white font-bold text-sm shadow-lg" style="background-color: {{ $project->color }}">
                                {{ strtoupper(substr($project->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-primary-text group-hover:text-primary transition-colors truncate">{{ $project->name }}</h3>
                                <p class="text-sm text-secondary-text mt-1 line-clamp-2">{{ $project->description ?? 'No description' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            <!-- Progress bar -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs text-secondary-text">Progress</span>
                                    <span class="text-xs font-medium text-primary-text">{{ $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0 }}%</span>
                                </div>
                                <div class="w-full bg-surface-hover rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-primary to-teal h-full rounded-full transition-all duration-500" style="width: {{ $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div class="flex items-center justify-between text-xs text-secondary-text">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    {{ $project->tasks_count }} tasks
                                </div>
                                <x-badge :color="$project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'gray')" class="text-[10px]">
                                    {{ ucfirst($project->status) }}
                                </x-badge>
                            </div>
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $projects->links() }}
        </div>
    @else
        <x-empty-state 
            title="No projects yet" 
            description="Get started by creating your first project. Projects help you organize tasks and collaborate with your team."
        >
            <x-slot name="action">
                <a href="{{ route('projects.create') }}">
                    <x-primary-button>Create Your First Project</x-primary-button>
                </a>
            </x-slot>
        </x-empty-state>
    @endif
</x-app-layout>

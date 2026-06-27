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
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">{{ $project->name }} <span class="text-secondary-text font-normal">/ Board</span></h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('projects.discussions.index', $project) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Discussions
                    </x-primary-button>
                </a>
                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}">
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

    <!-- Kanban Board Container -->
    <div class="flex gap-6 h-[calc(100vh-12rem)] overflow-x-auto pb-4" 
         x-data="kanbanBoard()"
         x-init="initBoard()">
        
        @php
            $columns = [
                'todo' => ['title' => 'To Do', 'color' => 'gray'],
                'in_progress' => ['title' => 'In Progress', 'color' => 'primary'],
                'review' => ['title' => 'Review', 'color' => 'warning'],
                'done' => ['title' => 'Done', 'color' => 'success'],
            ];
            
            $priorityColors = [
                'low' => 'gray',
                'medium' => 'info',
                'high' => 'warning',
                'urgent' => 'danger',
            ];
        @endphp

        @foreach($columns as $status => $column)
            <!-- Column -->
            <div class="flex flex-col flex-shrink-0 w-80">
                <!-- Column Header -->
                <div class="flex items-center justify-between mb-4 px-1">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-{{ $column['color'] }}"></div>
                        <h3 class="font-semibold text-primary-text">{{ $column['title'] }}</h3>
                        <span class="text-xs font-medium bg-surface text-secondary-text px-2 py-0.5 rounded-full">{{ $tasksByStatus[$status]->count() }}</span>
                    </div>
                    <button class="text-secondary-text hover:text-primary-text transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                    </button>
                </div>

                <!-- Column Dropzone -->
                <div class="flex-1 min-h-[200px] bg-surface/50 rounded-2xl p-2 sortable-column flex flex-col gap-3 border border-transparent transition-colors"
                     data-status="{{ $status }}">
                    
                    @foreach($tasksByStatus[$status] as $task)
                        <!-- Task Card -->
                        <div class="bg-background border border-border p-4 rounded-xl shadow-sm cursor-grab active:cursor-grabbing hover:border-primary/50 transition-colors group"
                             data-id="{{ $task->id }}">
                            
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h4 class="text-sm font-medium text-primary-text leading-snug group-hover:text-primary transition-colors">{{ $task->title }}</h4>
                            </div>

                            @if($task->tags->count() > 0)
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($task->tags as $tag)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center gap-2">
                                    <x-badge :color="$priorityColors[$task->priority] ?? 'gray'" class="!text-[10px] !py-0 !px-1.5">
                                        {{ ucfirst($task->priority) }}
                                    </x-badge>

                                    @if($task->comments_count > 0)
                                        <div class="flex items-center text-xs text-secondary-text gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            {{ $task->comments_count }}
                                        </div>
                                    @endif
                                </div>
                                
                                @if($task->assignee)
                                    <div title="{{ $task->assignee->name }}">
                                        <x-avatar :name="$task->assignee->name" size="xs" />
                                    </div>
                                @else
                                    <div class="w-6 h-6 rounded-full border border-dashed border-border flex items-center justify-center text-secondary-text" title="Unassigned">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Kanban Logic -->
    <script>
        function kanbanBoard() {
            return {
                initBoard() {
                    const columns = document.querySelectorAll('.sortable-column');
                    
                    columns.forEach(column => {
                        window.Sortable.create(column, {
                            group: 'shared', // set both lists to same group
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: (evt) => {
                                const taskId = evt.item.dataset.id;
                                const newStatus = evt.to.dataset.status;
                                
                                // Calculate new order based on DOM position
                                const newOrder = Array.from(evt.to.children).map(child => child.dataset.id);

                                // Send AJAX request to update task status and order
                                fetch(`/tasks/${taskId}/status-board`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        status: newStatus,
                                        order: newOrder
                                    })
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        console.error('Failed to update task status');
                                        // Ideally, we'd revert the DOM change here if it failed
                                    }
                                });
                            },
                        });
                    });
                }
            }
        }
    </script>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $task->project) }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>

            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tight text-primary-text">{{ $task->title }}</h2>
                    @php
                        $statusColors = ['todo' => 'gray', 'in_progress' => 'primary', 'review' => 'warning', 'done' => 'success'];
                        $statusLabels = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'];
                        $priorityColors = ['low' => 'gray', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                    @endphp
                    <form action="{{ route('tasks.updateStatusInline', $task) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="text-xs font-medium px-2.5 py-1 rounded-full border-0 focus:ring-2 focus:ring-primary cursor-pointer 
                            {{ $task->status === 'todo' ? 'bg-gray-500/20 text-gray-400' : '' }}
                            {{ $task->status === 'in_progress' ? 'bg-primary/20 text-primary' : '' }}
                            {{ $task->status === 'review' ? 'bg-warning/20 text-warning' : '' }}
                            {{ $task->status === 'done' ? 'bg-success/20 text-success' : '' }}
                        ">
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" {{ $task->status === $value ? 'selected' : '' }} class="bg-background text-primary-text">{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                    
                    <form action="{{ route('tasks.updatePriorityInline', $task) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <select name="priority" onchange="this.form.submit()" class="text-xs font-medium px-2.5 py-1 rounded-full border-0 focus:ring-2 focus:ring-primary cursor-pointer
                            {{ $task->priority === 'low' ? 'bg-gray-500/20 text-gray-400' : '' }}
                            {{ $task->priority === 'medium' ? 'bg-info/20 text-info' : '' }}
                            {{ $task->priority === 'high' ? 'bg-warning/20 text-warning' : '' }}
                            {{ $task->priority === 'urgent' ? 'bg-danger/20 text-danger' : '' }}
                        ">
                            <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }} class="bg-background text-primary-text">Low</option>
                            <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }} class="bg-background text-primary-text">Medium</option>
                            <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }} class="bg-background text-primary-text">High</option>
                            <option value="urgent" {{ $task->priority === 'urgent' ? 'selected' : '' }} class="bg-background text-primary-text">Urgent</option>
                        </select>
                    </form>
                </div>
                <p class="text-sm text-secondary-text mt-1">
                    in <a href="{{ route('projects.show', $task->project) }}" class="text-primary hover:underline">{{ $task->project->name }}</a>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('tasks.edit', $task) }}">
                    <x-primary-button class="!bg-surface !border-border !text-primary-text hover:!bg-surface-hover">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit
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
            <!-- Description -->
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Description</h3>
                </x-slot>

                @if($task->description)
                    <div class="prose prose-invert max-w-none text-sm text-primary-text leading-relaxed whitespace-pre-wrap">{{ $task->description }}</div>
                @else
                    <p class="text-sm text-secondary-text italic">No description provided.</p>
                @endif
            </x-card>

            <!-- Comments -->
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Comments ({{ $task->comments->count() }})</h3>
                </x-slot>

                @if($task->comments->count())
                    <div class="space-y-4">
                        @foreach($task->comments as $comment)
                            <div class="flex gap-3">
                                <x-avatar :name="$comment->user->name" size="sm" />
                                <div class="flex-1 bg-surface rounded-xl p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-primary-text">{{ $comment->user->name }}</span>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-secondary-text">{{ $comment->created_at->diffForHumans() }}</span>
                                            @if($comment->user_id === auth()->id() || $task->project->owner_id === auth()->id())
                                                <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this comment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-secondary-text hover:text-red-500 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-primary-text/80 whitespace-pre-wrap">{{ $comment->content }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-secondary-text italic">No comments yet.</p>
                @endif

                <!-- Add Comment Form -->
                <form action="{{ route('comments.store', $task) }}" method="POST" class="mt-4 pt-4 border-t border-border">
                    @csrf
                    <div class="flex gap-3">
                        <x-avatar :name="auth()->user()->name" size="sm" />
                        <div class="flex-1">
                            <textarea name="content" rows="2" placeholder="Write a comment..." class="block w-full border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm placeholder-secondary-text/50 text-sm" required></textarea>
                            <div class="flex justify-end mt-2">
                                <x-primary-button class="!text-xs !px-3 !py-1.5">
                                    Comment
                                </x-primary-button>
                            </div>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-primary-text">Details</h3>
                </x-slot>

                <div class="space-y-4">
                    <!-- Assignee -->
                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Assignee</h4>
                        <form action="{{ route('tasks.updateAssigneeInline', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="flex items-center gap-2">
                                @if($task->assignee)
                                    <x-avatar :name="$task->assignee->name" size="sm" />
                                @else
                                    <div class="w-8 h-8 rounded-full border border-dashed border-border flex items-center justify-center text-secondary-text bg-surface">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                @endif
                                <select name="assignee_id" onchange="this.form.submit()" class="text-sm bg-transparent border-0 border-b border-transparent hover:border-border focus:border-primary focus:ring-0 py-1 pl-1 pr-6 cursor-pointer text-primary-text">
                                    <option value="" class="bg-background text-primary-text">Unassigned</option>
                                    @foreach($projectMembers as $member)
                                        <option value="{{ $member->id }}" {{ $task->assignee_id == $member->id ? 'selected' : '' }} class="bg-background text-primary-text">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Due Date</h4>
                        @if($task->due_date)
                            <span class="text-sm text-primary-text {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-500 font-medium' : '' }}">
                                {{ $task->due_date->format('M d, Y') }}
                                @if($task->due_date->isPast() && $task->status !== 'done')
                                    (Overdue)
                                @endif
                            </span>
                        @else
                            <p class="text-sm text-secondary-text italic">No due date</p>
                        @endif
                    </div>

                    <!-- Tags -->
                    <div>
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Tags</h4>
                        @if($task->tags->count())
                            <div class="flex flex-wrap gap-1">
                                @foreach($task->tags as $tag)
                                    <span class="text-xs font-medium px-2 py-1 rounded-lg text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-secondary-text italic">No tags</p>
                        @endif
                    </div>

                    <!-- Creator -->
                    <div class="pt-4 border-t border-border">
                        <h4 class="text-xs font-semibold text-secondary-text uppercase tracking-wider mb-2">Created By</h4>
                        @if($task->creator)
                            <div class="flex items-center gap-2">
                                <x-avatar :name="$task->creator->name" size="xs" />
                                <span class="text-sm text-primary-text">{{ $task->creator->name }}</span>
                            </div>
                        @endif
                        <p class="text-xs text-secondary-text mt-1">{{ $task->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Danger Zone -->
            <x-card class="border-red-500/20">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-red-500">Delete this task</h4>
                        <p class="text-xs text-secondary-text mt-1">This action cannot be undone.</p>
                    </div>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-500/10 border border-transparent rounded-xl font-semibold text-xs text-red-500 uppercase tracking-widest hover:bg-red-500/20 transition">
                            Delete
                        </button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>

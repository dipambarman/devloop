<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tasks.show', $task) }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">Edit Task</h2>
                <p class="text-sm text-secondary-text mt-1">Update details for "{{ $task->title }}"</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div>
                    <x-input-label for="title" value="Task Title" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $task->title)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm placeholder-secondary-text/50">{{ old('description', $task->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Status -->
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm">
                            @foreach(\App\Enums\TaskStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('status', $task->status->value) === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <!-- Priority -->
                    <div>
                        <x-input-label for="priority" value="Priority" />
                        <select id="priority" name="priority" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm">
                            @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" {{ old('priority', $task->priority->value) === $priority->value ? 'selected' : '' }}>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('priority')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Due Date -->
                    <div>
                        <x-input-label for="due_date" value="Due Date" />
                        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', $task->due_date?->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
                    </div>

                    <!-- Assignee -->
                    <div>
                        <x-input-label for="assignee_id" value="Assign To" />
                        <select id="assignee_id" name="assignee_id" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assignee_id', $task->assignee_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('assignee_id')" />
                    </div>
                </div>

                <!-- Tags -->
                @if($tags->count())
                    <div>
                        <x-input-label value="Tags" />
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($tags as $tag)
                                <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-1.5 rounded-lg border border-border hover:border-primary/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="rounded border-border text-primary focus:ring-primary bg-background" {{ in_array($tag->id, old('tags', $task->tags->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $tag->color }}"></span>
                                    <span class="text-sm text-primary-text">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end pt-4 border-t border-border">
                    <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-secondary-text hover:text-primary-text mr-6">
                        Cancel
                    </a>
                    <x-primary-button>
                        Save Changes
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>

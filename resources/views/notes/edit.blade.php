<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('notes.index') }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="flex-1">
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">Edit Note</h2>
                <p class="text-sm text-secondary-text mt-1">Last updated {{ $note->updated_at->diffForHumans() }}</p>
            </div>
            
            <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Delete this note?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-secondary-text hover:text-red-500 transition-colors font-medium px-4 py-2">
                    Delete Note
                </button>
            </form>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <x-card>
            <form action="{{ route('notes.update', $note) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Title and Pin -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <x-text-input id="title" name="title" type="text" class="w-full text-lg font-bold" :value="old('title', $note->title)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>
                    <label class="flex items-center gap-2 px-4 py-2 border border-border rounded-xl cursor-pointer hover:bg-surface-hover transition-colors {{ $note->is_pinned ? 'bg-primary/10 border-primary/30' : '' }}">
                        <input type="checkbox" name="is_pinned" class="rounded border-border text-primary focus:ring-primary bg-background" value="1" {{ old('is_pinned', $note->is_pinned) ? 'checked' : '' }}>
                        <svg class="w-4 h-4 {{ $note->is_pinned ? 'text-primary' : 'text-secondary-text' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 2a1 1 0 011-1h8a1 1 0 011 1v1.5a2 2 0 01-2 2h-1v4l2.5 3.5A1 1 0 0114 15h-3v4a1 1 0 01-2 0v-4H6a1 1 0 01-.81-1.5L7.7 10V5.5H6.7a2 2 0 01-2-2V2z" />
                        </svg>
                        <span class="text-sm font-medium {{ $note->is_pinned ? 'text-primary' : 'text-primary-text' }}">Pin</span>
                    </label>
                </div>

                <!-- Project Context -->
                <div>
                    <select id="project_id" name="project_id" class="mt-1 block w-48 border-transparent bg-surface text-secondary-text hover:text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm transition-colors cursor-pointer">
                        <option value="">Global Note</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $note->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
                </div>

                <!-- Content -->
                <div>
                    <textarea id="content" name="content" rows="15" class="block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm font-mono text-sm placeholder-secondary-text/50">{{ old('content', $note->content) }}</textarea>
                    <p class="text-xs text-secondary-text mt-2">Markdown formatting is supported.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('content')" />
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-border">
                    <a href="{{ route('notes.index') }}" class="text-sm font-medium text-secondary-text hover:text-primary-text mr-6">
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

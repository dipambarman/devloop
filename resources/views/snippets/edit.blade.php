<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('snippets.index') }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="flex-1">
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">Edit Snippet</h2>
                <p class="text-sm text-secondary-text mt-1">Last updated {{ $snippet->updated_at->diffForHumans() }}</p>
            </div>
            
            <form action="{{ route('snippets.destroy', $snippet) }}" method="POST" onsubmit="return confirm('Delete this snippet?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-secondary-text hover:text-red-500 transition-colors font-medium px-4 py-2">
                    Delete Snippet
                </button>
            </form>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <x-card>
            <form action="{{ route('snippets.update', $snippet) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Title and Language -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <x-input-label for="title" value="Snippet Title" />
                        <x-text-input id="title" name="title" type="text" class="w-full mt-1" :value="old('title', $snippet->title)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>
                    
                    <div class="w-full sm:w-48">
                        <x-input-label for="language" value="Language" />
                        <select id="language" name="language" required class="mt-1 block w-full border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm">
                            <option value="php" {{ old('language', $snippet->language) === 'php' ? 'selected' : '' }}>PHP</option>
                            <option value="javascript" {{ old('language', $snippet->language) === 'javascript' ? 'selected' : '' }}>JavaScript</option>
                            <option value="html" {{ old('language', $snippet->language) === 'html' ? 'selected' : '' }}>HTML</option>
                            <option value="css" {{ old('language', $snippet->language) === 'css' ? 'selected' : '' }}>CSS</option>
                            <option value="bash" {{ old('language', $snippet->language) === 'bash' ? 'selected' : '' }}>Bash</option>
                            <option value="sql" {{ old('language', $snippet->language) === 'sql' ? 'selected' : '' }}>SQL</option>
                            <option value="plaintext" {{ old('language', $snippet->language) === 'plaintext' ? 'selected' : '' }}>Plain Text</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('language')" />
                    </div>
                </div>

                <!-- Project Context -->
                <div>
                    <x-input-label for="project_id" value="Project (Optional)" />
                    <select id="project_id" name="project_id" class="mt-1 block w-full sm:w-64 border-border bg-surface text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm cursor-pointer">
                        <option value="">Global Snippet</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $snippet->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
                </div>

                <!-- Code Content -->
                <div>
                    <x-input-label for="code" value="Code" />
                    <textarea id="code" name="code" rows="15" required class="mt-1 block w-full border-border bg-[#1e1e1e] text-gray-300 focus:border-primary focus:ring-primary rounded-xl shadow-sm font-mono text-sm placeholder-secondary-text/50">{{ old('code', $snippet->code) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('code')" />
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-border">
                    <a href="{{ route('snippets.index') }}" class="text-sm font-medium text-secondary-text hover:text-primary-text mr-6">
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

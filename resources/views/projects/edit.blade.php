<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $project) }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">Edit Project</h2>
                <p class="text-sm text-secondary-text mt-1">Update details for {{ $project->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <x-card>
            <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <x-input-label for="name" value="Project Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm placeholder-secondary-text/50">{{ old('description', $project->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Status -->
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm">
                            <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="archived" {{ old('status', $project->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <!-- Color -->
                    <div>
                        <x-input-label for="color" value="Project Theme Color" />
                        <div class="flex items-center gap-3 mt-1">
                            <input type="color" id="color" name="color" value="{{ old('color', $project->color) }}" class="h-10 w-14 rounded cursor-pointer bg-background border border-border p-1">
                            <span class="text-sm text-secondary-text font-mono">Select a color</span>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('color')" />
                    </div>
                </div>

                <!-- GitHub Repo -->
                <div>
                    <x-input-label for="github_repo" value="GitHub Repository (Optional)" />
                    <x-text-input id="github_repo" name="github_repo" type="text" class="mt-1 block w-full" :value="old('github_repo', $project->github_repo)" placeholder="e.g., owner/repo" />
                    <x-input-error class="mt-2" :messages="$errors->get('github_repo')" />
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-border">
                    <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-secondary-text hover:text-primary-text mr-6">
                        Cancel
                    </a>
                    <x-primary-button>
                        Save Changes
                    </x-primary-button>
                </div>
            </form>
        </x-card>

        <!-- Danger Zone -->
        <x-card class="border-red-500/20">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-red-500">Danger Zone</h3>
            </x-slot>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-medium text-primary-text">Delete this project</h4>
                    <p class="text-xs text-secondary-text mt-1">Once you delete a project, there is no going back. Please be certain.</p>
                </div>
                
                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this project? All tasks associated with it will also be deleted.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-red-500/10 border border-transparent rounded-xl font-semibold text-xs text-red-500 uppercase tracking-widest hover:bg-red-500/20 active:bg-red-500/30 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-background transition ease-in-out duration-150">
                        Delete Project
                    </button>
                </form>
            </div>
        </x-card>
    </div>
</x-app-layout>

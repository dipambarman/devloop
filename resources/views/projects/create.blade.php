<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.index') }}" class="text-secondary-text hover:text-primary-text transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">Create Project</h2>
                <p class="text-sm text-secondary-text mt-1">Start a new project and organize your work.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <form action="{{ route('projects.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" value="Project Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="e.g., Nexus Protocol" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-border bg-background text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm placeholder-secondary-text/50" placeholder="What is this project about?">{{ old('description') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Color -->
                    <div>
                        <x-input-label for="color" value="Project Theme Color" />
                        <div class="flex items-center gap-3 mt-1">
                            <input type="color" id="color" name="color" value="{{ old('color', '#6366F1') }}" class="h-10 w-14 rounded cursor-pointer bg-background border border-border p-1">
                            <span class="text-sm text-secondary-text font-mono">Select a color to identify this project</span>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('color')" />
                    </div>

                    <!-- GitHub Repo -->
                    <div>
                        <x-input-label for="github_repo" value="GitHub Repository (Optional)" />
                        <x-text-input id="github_repo" name="github_repo" type="text" class="mt-1 block w-full" :value="old('github_repo')" placeholder="e.g., owner/repo" />
                        <x-input-error class="mt-2" :messages="$errors->get('github_repo')" />
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-border">
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-secondary-text hover:text-primary-text mr-6">
                        Cancel
                    </a>
                    <x-primary-button>
                        Create Project
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>

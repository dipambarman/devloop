<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-primary-text">
                    {{ __('Developer Profile') }}
                </h2>
                <p class="text-sm text-secondary-text mt-0.5">
                    {{ __('Manage your developer identity, linked GitHub authentication, and notification preferences.') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if($user->github_username)
                    <a href="https://github.com/{{ $user->github_username }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-surface border border-border text-primary-text hover:bg-surface-hover hover:border-primary/40 shadow-sm transition-all group">
                        <svg class="w-4 h-4 fill-current text-primary-text" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                        <span>GitHub Profile</span>
                        <svg class="w-3.5 h-3.5 text-secondary-text group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Developer Identity Banner -->
    <div class="mb-6 bg-surface border border-border rounded-2xl p-6 sm:p-8 relative overflow-hidden shadow-lg backdrop-blur-md">
        <!-- Subtle Glow & Gradient Background -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-accent/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-5">
                <!-- Avatar with glowing gradient border -->
                <div class="relative shrink-0">
                    <div class="p-1 rounded-2xl bg-gradient-to-tr from-primary via-indigo-500 to-accent shadow-md">
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-background flex items-center justify-center font-bold text-3xl text-primary font-mono">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <!-- Verified status dot -->
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-surface flex items-center justify-center text-white" title="Active & Authenticated">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </div>

                <!-- Info -->
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-bold text-primary-text tracking-tight">
                            {{ $user->name }}
                        </h1>
                        @if($user->github_username)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-primary/15 text-primary border border-primary/25">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                                </svg>
                                {{ '@' . $user->github_username }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            OAuth Verified
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-y-1 gap-x-4 mt-2 text-xs text-secondary-text">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-secondary-text/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $user->email }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-secondary-text/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Joined {{ $user->created_at ? $user->created_at->format('M Y') : 'Recently' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Developer Stats Shelf -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-background/60 p-3 rounded-xl border border-border shrink-0">
                <div class="px-3 py-2 text-center">
                    <p class="text-xs text-secondary-text font-medium">Projects</p>
                    <p class="text-lg font-bold text-primary-text font-mono mt-0.5">{{ $user->allProjects()->count() }}</p>
                </div>
                <div class="px-3 py-2 text-center border-l border-border">
                    <p class="text-xs text-secondary-text font-medium">Tasks</p>
                    <p class="text-lg font-bold text-primary-text font-mono mt-0.5">{{ $user->assignedTasks()->count() }}</p>
                </div>
                <div class="px-3 py-2 text-center border-l border-border">
                    <p class="text-xs text-secondary-text font-medium">Snippets</p>
                    <p class="text-lg font-bold text-primary-text font-mono mt-0.5">{{ $user->snippets()->count() }}</p>
                </div>
                <div class="px-3 py-2 text-center border-l border-border">
                    <p class="text-xs text-secondary-text font-medium">Notes</p>
                    <p class="text-lg font-bold text-primary-text font-mono mt-0.5">{{ $user->notes()->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-7xl">
        <div class="lg:col-span-2 space-y-6">
            <x-card class="relative overflow-hidden">
                @include('profile.partials.update-profile-information-form')
            </x-card>

            <x-card>
                @include('profile.partials.update-notification-preferences-form')
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card class="border-border">
                @include('profile.partials.github-account-info')
            </x-card>

            <x-card class="border-border">
                @include('profile.partials.logout-user-form')
            </x-card>

            <x-card class="border-danger/20">
                @include('profile.partials.delete-user-form')
            </x-card>
        </div>
    </div>
</x-app-layout>

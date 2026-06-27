<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-surface border-r border-border flex flex-col h-full transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
    <div class="h-16 flex items-center px-6 border-b border-border">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center shadow-lg group-hover:shadow-primary/50 transition-all duration-300">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-white">
                DevLoop
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 scrollbar-hide">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors w-full justify-start {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary' : 'text-secondary-text hover:text-primary-text hover:bg-surface-hover' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            {{ __('Dashboard') }}
        </x-nav-link>

        <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors w-full justify-start {{ request()->routeIs('projects.*') ? 'bg-primary/10 text-primary' : 'text-secondary-text hover:text-primary-text hover:bg-surface-hover' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
            Projects
        </x-nav-link>
        
        <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors w-full justify-start {{ request()->routeIs('tasks.*') ? 'bg-primary/10 text-primary' : 'text-secondary-text hover:text-primary-text hover:bg-surface-hover' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Tasks
        </x-nav-link>

        <x-nav-link :href="route('notes.index')" :active="request()->routeIs('notes.*')" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors w-full justify-start {{ request()->routeIs('notes.*') ? 'bg-primary/10 text-primary' : 'text-secondary-text hover:text-primary-text hover:bg-surface-hover' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Notes
        </x-nav-link>

        <x-nav-link :href="route('snippets.index')" :active="request()->routeIs('snippets.*')" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors w-full justify-start {{ request()->routeIs('snippets.*') ? 'bg-primary/10 text-primary' : 'text-secondary-text hover:text-primary-text hover:bg-surface-hover' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
            Snippets
        </x-nav-link>
    </nav>

    <!-- User Profile Snippet in Sidebar -->
    <div class="p-4 border-t border-border">
        <div class="flex items-center gap-3 px-2">
            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-accent flex items-center justify-center text-white font-semibold text-xs">
                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-primary-text truncate">
                    {{ Auth::user()->name ?? 'User' }}
                </p>
                <p class="text-xs text-secondary-text truncate">
                    {{ Auth::user()->email ?? 'user@example.com' }}
                </p>
            </div>
        </div>
    </div>
</aside>

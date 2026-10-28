<header class="h-16 bg-surface/80 backdrop-blur-md border-b border-border flex items-center justify-between px-4 sm:px-6 z-20">
    <div class="flex items-center flex-1 gap-4">
        <!-- Mobile Sidebar Toggle -->
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 text-secondary-text hover:text-primary-text transition-colors rounded-lg hover:bg-surface-hover focus:outline-none">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Search -->
        <div class="flex-1 max-w-xl">
            <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-secondary-text group-focus-within:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-transparent rounded-lg leading-5 bg-background text-primary-text placeholder-secondary-text focus:outline-none focus:bg-surface focus:border-primary focus:ring-1 focus:ring-primary sm:text-sm transition-all" placeholder="Search projects, tasks, notes...">
        </div>
    </div>
    </div>

    <!-- Right side (Notifications & Profile Dropdown) -->
    <div class="ml-4 flex items-center gap-4">
        <!-- Notifications -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
            <button @click="open = ! open" class="relative p-2 text-secondary-text hover:text-primary-text transition-colors rounded-full hover:bg-surface-hover focus:outline-none">
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="absolute top-1 right-1.5 w-2 h-2 bg-accent rounded-full animate-pulse"></span>
                @endif
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>

            <!-- Notifications Dropdown -->
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-[calc(100vw-2rem)] sm:w-80 max-w-sm rounded-xl shadow-xl glass z-50 py-1 border border-border"
                 style="display: none;">
                
                <div class="px-4 py-3 border-b border-border flex justify-between items-center bg-surface">
                    <h3 class="text-sm font-bold text-primary-text">Notifications</h3>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.readAll') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-primary hover:text-accent transition-colors font-medium">Mark all as read</button>
                        </form>
                    @endif
                </div>

                <div class="max-h-[300px] overflow-y-auto">
                    @forelse(auth()->user()->unreadNotifications as $notification)
                        <div class="px-4 py-3 border-b border-border hover:bg-surface-hover transition-colors group">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1">
                                    @if(!empty($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}" class="text-sm font-medium text-primary-text hover:text-primary transition-colors block">
                                            {{ $notification->data['message'] ?? 'New notification' }}
                                        </a>
                                    @else
                                        <p class="text-sm text-primary-text">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                    @endif
                                    <p class="text-xs text-secondary-text mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1 rounded text-secondary-text hover:text-primary hover:bg-surface transition-colors" title="Mark as read">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-sm text-secondary-text">
                            No new notifications
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Account Profile Link (Opens Profile Directly) -->
        <a href="{{ route('profile.edit') }}" 
           class="flex items-center gap-2.5 px-2 py-1.5 sm:px-3 sm:py-1.5 rounded-xl border {{ request()->routeIs('profile.*') ? 'border-primary/50 bg-primary/10 text-primary' : 'border-border bg-surface/60 hover:bg-surface-hover hover:border-primary/30 text-secondary-text hover:text-primary-text' }} transition-all group focus:outline-none focus:ring-2 focus:ring-primary/40 shadow-sm"
           title="{{ __('My Account & Profile') }}"
        >
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-lg object-cover ring-1 ring-border group-hover:ring-primary/40 transition-all">
            @else
                <div class="w-7 h-7 rounded-lg bg-gradient-to-r from-primary to-accent flex items-center justify-center text-white font-semibold text-xs shadow-sm font-mono">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
            @endif
            <div class="hidden sm:flex flex-col text-left">
                <span class="text-xs font-semibold leading-tight text-primary-text group-hover:text-primary transition-colors truncate max-w-[120px]">
                    {{ Auth::user()->name ?? 'User' }}
                </span>
                <span class="text-[10px] text-secondary-text leading-tight font-mono">
                    {{ Auth::user()->github_username ? '@' . Auth::user()->github_username : 'Developer' }}
                </span>
            </div>
        </a>
    </div>
</header>

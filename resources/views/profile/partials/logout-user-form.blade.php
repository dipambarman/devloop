<section class="space-y-4">
    <header class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-surface border border-border flex items-center justify-center text-secondary-text">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-primary-text">
                    {{ __('Session & Sign Out') }}
                </h3>
                <p class="text-[11px] text-secondary-text">
                    {{ __('End your active authenticated session on this browser.') }}
                </p>
            </div>
        </div>
    </header>

    <div class="p-3.5 rounded-xl bg-background border border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-semibold text-primary-text truncate">
                {{ Auth::user()->name }}
            </p>
            <p class="text-[11px] text-secondary-text truncate font-mono mt-0.5">
                {{ Auth::user()->email }}
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
            @csrf
            <button 
                type="submit" 
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-danger bg-danger/10 hover:bg-danger hover:text-white border border-danger/25 shadow-sm transition-all cursor-pointer"
                title="Log Out of DevLoop"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>{{ __('Log Out') }}</span>
            </button>
        </form>
    </div>
</section>

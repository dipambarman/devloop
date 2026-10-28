<section class="space-y-4">
    <header class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-surface border border-border flex items-center justify-center text-primary-text">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-primary-text">
                    {{ __('GitHub OAuth') }}
                </h3>
                <p class="text-[11px] text-secondary-text">
                    {{ __('Primary authentication provider') }}
                </p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Connected
        </span>
    </header>

    <div class="p-3.5 rounded-xl bg-background border border-border space-y-3">
        <div class="flex items-center gap-3">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-11 h-11 rounded-full object-cover ring-2 ring-primary/40">
            @else
                <div class="w-11 h-11 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-base font-mono">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-primary-text truncate">{{ $user->name }}</span>
                    @if($user->github_username)
                        <a href="https://github.com/{{ $user->github_username }}" target="_blank" rel="noopener noreferrer" class="text-[11px] text-primary hover:underline font-mono">
                            @ {{ $user->github_username }} &nearr;
                        </a>
                    @endif
                </div>
                <p class="text-[11px] text-secondary-text truncate mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <div class="pt-2 border-t border-border flex items-center justify-between text-[11px] text-secondary-text">
            <span>GitHub UID</span>
            <span class="font-mono text-primary-text font-semibold">#{{ $user->github_id ?? 'Unknown' }}</span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-secondary-text">
            <span>Auth Scheme</span>
            <span class="font-mono text-emerald-400">OAuth 2.0 PKCE</span>
        </div>
    </div>

    <div class="p-3 rounded-xl bg-background/40 border border-border/60 text-[11px] text-secondary-text leading-relaxed">
        <div class="flex items-start gap-2">
            <svg class="w-4 h-4 text-primary shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Passwordless authentication active. Your session is cryptographically bound to your GitHub identity.</span>
        </div>
    </div>
</section>

<section class="space-y-4">
    <header class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-primary-text">
                {{ __('GitHub Authentication') }}
            </h2>
            <p class="mt-1 text-sm text-secondary-text">
                {{ __('Your account is secured via GitHub OAuth. No passwords required.') }}
            </p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            Connected
        </span>
    </header>

    <div class="flex items-center gap-4 p-4 rounded-xl bg-background border border-border">
        @if($user->avatar)
            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-primary/40">
        @else
            <div class="w-12 h-12 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-lg">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="text-base font-semibold text-primary-text truncate">{{ $user->name }}</span>
                @if($user->github_username)
                    <a href="https://github.com/{{ $user->github_username }}" target="_blank" rel="noopener noreferrer" class="text-xs text-primary hover:underline font-mono">
                        @ {{ $user->github_username }} &nearr;
                    </a>
                @endif
            </div>
            <p class="text-xs text-secondary-text mt-0.5 truncate">{{ $user->email }}</p>
            @if($user->github_id)
                <p class="text-[11px] text-secondary-text/80 font-mono mt-1">GitHub UID: #{{ $user->github_id }}</p>
            @endif
        </div>
    </div>
</section>

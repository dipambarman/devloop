<section 
    x-data="{
        isEditing: {{ $errors->any() ? 'true' : 'false' }},
        skills: {{ json_encode(is_array($user->skills) ? array_values(array_filter($user->skills)) : []) }},
        newSkill: '',
        addSkill() {
            const val = this.newSkill.trim().replace(/^,+|,+$/g, '');
            if (val && !this.skills.includes(val)) {
                this.skills.push(val);
            }
            this.newSkill = '';
        },
        removeSkill(index) {
            this.skills.splice(index, 1);
        },
        handleKeydown(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                this.addSkill();
            }
        }
    }"
    class="space-y-6"
>
    <!-- View / Overview Mode -->
    <div x-show="!isEditing" class="space-y-6">
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-border">
            <div>
                <h3 class="text-lg font-bold text-primary-text flex items-center gap-2">
                    <span>Developer Dossier</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">Public Profile</span>
                </h3>
                <p class="mt-1 text-xs text-secondary-text">
                    Your developer profile, technical skills, and public URLs shared with your team.
                </p>
            </div>
            <button 
                type="button" 
                @click="isEditing = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-hover shadow-md hover:shadow-primary/25 transition-all self-start sm:self-auto cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                <span>Edit Profile</span>
            </button>
        </header>

        @if (session('status') === 'profile-updated')
            <div 
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 3500)"
                class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium flex items-center gap-2"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Profile updated successfully!</span>
            </div>
        @endif

        <!-- Bio Section -->
        <div class="space-y-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-secondary-text flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                About & Bio
            </span>
            @if($user->bio)
                <div class="p-4 rounded-xl bg-background/50 border border-border">
                    <p class="text-sm text-primary-text leading-relaxed whitespace-pre-line font-normal">
                        {{ $user->bio }}
                    </p>
                </div>
            @else
                <div class="p-4 rounded-xl bg-background/30 border border-dashed border-border text-center">
                    <p class="text-xs text-secondary-text">No bio provided yet. Click &ldquo;Edit Profile&rdquo; to introduce yourself.</p>
                </div>
            @endif
        </div>

        <!-- Tech Stack & Skills -->
        <div class="space-y-2.5">
            <span class="text-xs font-semibold uppercase tracking-wider text-secondary-text flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                Tech Stack & Skills
            </span>
            @if(!empty($user->skills) && count($user->skills) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($user->skills as $skill)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-mono font-medium bg-primary/10 text-primary border border-primary/25 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            {{ $skill }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-secondary-text italic">No skills listed yet.</p>
            @endif
        </div>

        <!-- External Links & Socials -->
        <div class="space-y-2.5 pt-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-secondary-text flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-secondary-text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                Developer Links
            </span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- GitHub -->
                @if($user->github_url || $user->github_username)
                    <a href="{{ $user->github_url ?: 'https://github.com/' . $user->github_username }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3.5 rounded-xl bg-background border border-border hover:border-primary/40 hover:bg-surface-hover transition-all group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 fill-current text-primary-text shrink-0" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                            <div class="truncate">
                                <p class="text-xs font-semibold text-primary-text">GitHub</p>
                                <p class="text-[11px] text-secondary-text truncate font-mono">{{ $user->github_username ? '@' . $user->github_username : 'View Profile' }}</p>
                            </div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-secondary-text group-hover:text-primary transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                @else
                    <div class="flex items-center gap-2.5 p-3.5 rounded-xl bg-background/40 border border-border text-secondary-text opacity-60">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                        <span class="text-xs">GitHub not linked</span>
                    </div>
                @endif

                <!-- Portfolio -->
                @if($user->portfolio_url)
                    <a href="{{ $user->portfolio_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3.5 rounded-xl bg-background border border-border hover:border-primary/40 hover:bg-surface-hover transition-all group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                            <div class="truncate">
                                <p class="text-xs font-semibold text-primary-text">Portfolio</p>
                                <p class="text-[11px] text-secondary-text truncate font-mono">{{ parse_url($user->portfolio_url, PHP_URL_HOST) ?: $user->portfolio_url }}</p>
                            </div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-secondary-text group-hover:text-primary transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                @else
                    <div class="flex items-center gap-2.5 p-3.5 rounded-xl bg-background/40 border border-border text-secondary-text opacity-60">
                        <svg class="w-4 h-4 text-secondary-text shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                        <span class="text-xs">No portfolio linked</span>
                    </div>
                @endif

                <!-- LinkedIn -->
                @if($user->linkedin_url)
                    <a href="{{ $user->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3.5 rounded-xl bg-background border border-border hover:border-primary/40 hover:bg-surface-hover transition-all group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 fill-current text-blue-400 shrink-0" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            <div class="truncate">
                                <p class="text-xs font-semibold text-primary-text">LinkedIn</p>
                                <p class="text-[11px] text-secondary-text truncate font-mono">View Profile</p>
                            </div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-secondary-text group-hover:text-primary transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                @else
                    <div class="flex items-center gap-2.5 p-3.5 rounded-xl bg-background/40 border border-border text-secondary-text opacity-60">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        <span class="text-xs">No LinkedIn</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Edit Mode Form -->
    <div x-show="isEditing" x-cloak class="space-y-6">
        <header class="flex items-center justify-between pb-4 border-b border-border">
            <div>
                <h3 class="text-lg font-bold text-primary-text flex items-center gap-2">
                    <span>Edit Profile Details</span>
                </h3>
                <p class="mt-1 text-xs text-secondary-text">
                    Update your personal profile, bio, interactive skills tags, and public social URLs.
                </p>
            </div>
            <button 
                type="button" 
                @click="isEditing = false"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium text-secondary-text hover:text-primary-text bg-background border border-border hover:bg-surface-hover transition-all cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Cancel</span>
            </button>
        </header>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Display Name')" class="!text-secondary-text font-medium text-xs" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary-text">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <x-text-input id="name" name="name" type="text" class="pl-9 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary text-sm rounded-xl" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('Email Address')" class="!text-secondary-text font-medium text-xs" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary-text">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <x-text-input id="email" name="email" type="email" class="pl-9 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary text-sm rounded-xl" :value="old('email', $user->email)" required autocomplete="username" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2 text-xs text-warning flex items-center justify-between">
                            <span>Your email is unverified.</span>
                            <button form="send-verification" class="underline hover:text-primary-text transition-colors">
                                Resend verification email
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bio -->
            <div>
                <x-input-label for="bio" :value="__('About / Bio')" class="!text-secondary-text font-medium text-xs" />
                <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full bg-background border-border text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm" placeholder="Tell the team about what you're building, your engineering interests, or your background...">{{ old('bio', $user->bio) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
            </div>

            <!-- Interactive Skills Tag Manager -->
            <div>
                <x-input-label :value="__('Tech Stack & Skills')" class="!text-secondary-text font-medium text-xs" />
                
                <!-- Hidden input submitting comma-separated string for backend compatibility -->
                <input type="hidden" name="skills" :value="skills.join(', ')">

                <div class="mt-1.5 p-3 rounded-xl bg-background border border-border focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                    <!-- Chip List -->
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="(skill, index) in skills" :key="index">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-medium bg-primary/15 text-primary border border-primary/30 animate-fadeIn">
                                <span x-text="skill"></span>
                                <button 
                                    type="button" 
                                    @click="removeSkill(index)" 
                                    class="text-primary hover:text-danger hover:bg-danger/10 rounded p-0.5 transition-colors focus:outline-none"
                                    title="Remove tag"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        </template>

                        <!-- Add input inside container -->
                        <div class="flex-1 min-w-[140px] flex items-center gap-2">
                            <input 
                                type="text" 
                                x-model="newSkill" 
                                @keydown="handleKeydown($event)" 
                                @blur="addSkill()"
                                placeholder="Add skill (press Enter)..." 
                                class="w-full text-xs font-mono bg-transparent border-0 focus:ring-0 text-primary-text placeholder-secondary-text/50 p-1"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-1.5 text-[11px] text-secondary-text">
                    <span>Press <kbd class="px-1.5 py-0.5 rounded bg-surface border border-border text-primary-text font-mono">Enter</kbd> or <kbd class="px-1.5 py-0.5 rounded bg-surface border border-border text-primary-text font-mono">,</kbd> to add tags</span>
                    <span x-text="skills.length + ' added'"></span>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('skills')" />
            </div>

            <!-- Developer Links -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <!-- GitHub -->
                <div>
                    <x-input-label for="github_url" :value="__('GitHub Profile URL')" class="!text-secondary-text font-medium text-xs" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary-text">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                        </div>
                        <x-text-input id="github_url" name="github_url" type="url" class="pl-9 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary text-xs rounded-xl font-mono" :value="old('github_url', $user->github_url)" placeholder="https://github.com/username" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
                </div>

                <!-- Portfolio -->
                <div>
                    <x-input-label for="portfolio_url" :value="__('Portfolio / Website')" class="!text-secondary-text font-medium text-xs" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary-text">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                        </div>
                        <x-text-input id="portfolio_url" name="portfolio_url" type="url" class="pl-9 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary text-xs rounded-xl font-mono" :value="old('portfolio_url', $user->portfolio_url)" placeholder="https://yourportfolio.dev" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('portfolio_url')" />
                </div>

                <!-- LinkedIn -->
                <div>
                    <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" class="!text-secondary-text font-medium text-xs" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary-text">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </div>
                        <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="pl-9 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary text-xs rounded-xl font-mono" :value="old('linkedin_url', $user->linkedin_url)" placeholder="https://linkedin.com/in/username" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-5 border-t border-border">
                <button 
                    type="button" 
                    @click="isEditing = false"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-secondary-text hover:text-primary-text bg-background border border-border hover:bg-surface-hover transition-all cursor-pointer"
                >
                    Cancel
                </button>

                <div class="flex items-center gap-3">
                    <x-primary-button class="cursor-pointer">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Save Changes') }}
                    </x-primary-button>
                </div>
            </div>
        </form>
    </div>
</section>

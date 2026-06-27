<section>
    <header>
        <h2 class="text-lg font-semibold text-primary-text">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm text-secondary-text">
            {{ __("Update your account's profile information, bio, skills, and social links.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" class="!text-secondary-text" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email')" class="!text-secondary-text" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-warning">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-secondary-text hover:text-primary-text rounded-md focus:outline-none transition-colors">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-success">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Bio -->
        <div>
            <x-input-label for="bio" :value="__('Bio')" class="!text-secondary-text" />
            <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full bg-background border-border text-primary-text focus:border-primary focus:ring-primary rounded-xl shadow-sm text-sm" placeholder="Tell us a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <!-- Skills -->
        <div>
            <x-input-label for="skills" :value="__('Skills (comma separated)')" class="!text-secondary-text" />
            <x-text-input id="skills" name="skills" type="text" class="mt-1 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary" :value="old('skills', is_array($user->skills) ? implode(', ', $user->skills) : '')" placeholder="e.g. PHP, Laravel, Vue.js, TailwindCSS" />
            <x-input-error class="mt-2" :messages="$errors->get('skills')" />
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <!-- GitHub -->
            <div>
                <x-input-label for="github_url" :value="__('GitHub URL')" class="!text-secondary-text" />
                <x-text-input id="github_url" name="github_url" type="url" class="mt-1 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary" :value="old('github_url', $user->github_url)" placeholder="https://github.com/username" />
                <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
            </div>

            <!-- Portfolio -->
            <div>
                <x-input-label for="portfolio_url" :value="__('Portfolio URL')" class="!text-secondary-text" />
                <x-text-input id="portfolio_url" name="portfolio_url" type="url" class="mt-1 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary" :value="old('portfolio_url', $user->portfolio_url)" placeholder="https://yourwebsite.com" />
                <x-input-error class="mt-2" :messages="$errors->get('portfolio_url')" />
            </div>

            <!-- LinkedIn -->
            <div>
                <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" class="!text-secondary-text" />
                <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full !bg-background !border-border !text-primary-text focus:!border-primary focus:!ring-primary" :value="old('linkedin_url', $user->linkedin_url)" placeholder="https://linkedin.com/in/username" />
                <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-border">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success font-medium flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>

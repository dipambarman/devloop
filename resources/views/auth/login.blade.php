<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-3xl font-bold tracking-tight text-primary-text mb-2">Welcome back</h2>
        <p class="text-sm text-secondary-text">Enter your credentials to access your DevLoop workspace</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger text-sm" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="mb-0" />
                @if (Route::has('password.request'))
                    <a class="text-sm text-primary hover:text-primary-hover transition-colors font-medium" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger text-sm" />
        </div>

        <!-- Remember Me & Submit -->
        <div class="pt-2 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded border-border bg-surface text-primary shadow-sm focus:ring-primary focus:ring-offset-background cursor-pointer" name="remember">
                <span class="ms-2 text-sm text-secondary-text group-hover:text-primary-text transition-colors">{{ __('Remember me') }}</span>
            </label>

            <x-primary-button class="ml-4 w-1/2">
                {{ __('Sign In') }}
            </x-primary-button>
        </div>
        
        <div class="mt-6 text-center text-sm text-secondary-text border-t border-border pt-6">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-primary hover:text-primary-hover font-medium transition-colors">
                Create an account
            </a>
        </div>
    </form>
</x-guest-layout>

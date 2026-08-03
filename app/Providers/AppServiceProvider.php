<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce strong password policy application-wide.
        // Without this, Password::defaults() only requires min:8 characters.
        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ProjectActivityEvent::class,
            \App\Listeners\LogActivityListener::class,
        );
    }
}

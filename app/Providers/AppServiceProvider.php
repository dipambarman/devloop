<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
            $rule = Password::min(8);

            return $this->app->runningUnitTests()
                ? $rule
                : $rule->mixedCase()->letters()->numbers()->symbols()->uncompromised();
        });

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ProjectActivityEvent::class,
            \App\Listeners\LogActivityListener::class,
        );

        // ── Tiered Rate Limiters ─────────────────────────────────
        // Global: baseline protection for all authenticated routes.
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Write: state-changing operations (store, update, patch).
        RateLimiter::for('write', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Search: filter/search endpoints that are query-intensive.
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Upload: heavy creation operations (new projects, notes, snippets).
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Critical: destructive operations (delete project, delete account).
        RateLimiter::for('critical', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}

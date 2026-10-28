<?php

namespace App\Providers;

use App\Events\CommentAdded;
use App\Events\DiscussionReplyAdded;
use App\Events\MemberAdded;
use App\Events\ProjectActivityEvent;
use App\Events\TaskAssigneeChanged;
use App\Listeners\LogActivityListener;
use App\Listeners\SendCommentNotification;
use App\Listeners\SendDiscussionReplyNotification;
use App\Listeners\SendMemberAddedNotification;
use App\Listeners\SendTaskAssignedNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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

        Event::listen(
            ProjectActivityEvent::class,
            LogActivityListener::class,
        );

        Event::listen(
            TaskAssigneeChanged::class,
            SendTaskAssignedNotification::class,
        );

        Event::listen(
            CommentAdded::class,
            SendCommentNotification::class,
        );

        Event::listen(
            DiscussionReplyAdded::class,
            SendDiscussionReplyNotification::class,
        );

        Event::listen(
            MemberAdded::class,
            SendMemberAddedNotification::class,
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
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Upload: heavy creation operations (new projects, notes, snippets).
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Critical: destructive operations (delete project, delete account).
        RateLimiter::for('critical', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Ingest: headless API for error reporting (300 req/min per key/IP).
        RateLimiter::for('ingest', function (Request $request) {
            $key = $request->header('X-DevLoop-Key') ?: $request->header('x-api-key') ?: $request->ip();

            return Limit::perMinute(300)->by($key);
        });
    }
}

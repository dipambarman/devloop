<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GithubAuthController extends Controller
{
    /**
     * Display the developer login screen with GitHub OAuth.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Display the developer registration screen with GitHub OAuth.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['read:user', 'user:email'])
            ->redirect();
    }

    /**
     * Obtain the user information from GitHub and authenticate.
     */
    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Throwable $e) {
            Log::warning('GitHub OAuth authentication cancelled or failed: '.$e->getMessage());

            return redirect()->route('login')->withErrors([
                'oauth' => 'GitHub authentication was cancelled or failed. Please try again.',
            ]);
        }

        $githubId = (string) $githubUser->getId();
        $email = $githubUser->getEmail();
        $nickname = $githubUser->getNickname();
        $name = $githubUser->getName() ?: $nickname ?: 'GitHub Developer';
        $avatar = $githubUser->getAvatar();
        $token = $githubUser->token;
        $refreshToken = $githubUser->refreshToken ?? null;

        // Fallback email if user has private email on GitHub
        $effectiveEmail = $email ?: "{$nickname}@users.noreply.github.com";

        // Query by github_id first, then by email
        $user = User::where('github_id', $githubId)->first();

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            // Register brand-new developer via GitHub
            $user = User::create([
                'name' => $name,
                'email' => $effectiveEmail,
                'github_id' => $githubId,
                'github_username' => $nickname,
                'avatar' => $avatar,
                'github_token' => $token,
                'github_refresh_token' => $refreshToken,
                'github_url' => $nickname ? "https://github.com/{$nickname}" : null,
                'email_verified_at' => now(),
            ]);

            // Fire registered event to trigger initial developer onboarding (starter project, tasks, snippets)
            event(new Registered($user));
        } else {
            // Update existing user with fresh GitHub credentials and profile image
            $user->update([
                'github_id' => $githubId,
                'github_username' => $nickname ?: $user->github_username,
                'avatar' => $avatar ?: $user->avatar,
                'github_token' => $token,
                'github_refresh_token' => $refreshToken ?: $user->github_refresh_token,
                'github_url' => $nickname ? "https://github.com/{$nickname}" : $user->github_url,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

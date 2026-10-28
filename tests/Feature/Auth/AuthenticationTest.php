<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

test('login screen can be rendered with github oauth button', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Continue with GitHub');
    $response->assertSee('github-login-btn');
});

test('registration screen can be rendered with github oauth button', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertSee('Sign Up with GitHub');
    $response->assertSee('github-register-btn');
});

test('redirects to github oauth provider', function () {
    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('scopes')->with(['read:user', 'user:email'])->andReturnSelf();
    $provider->shouldReceive('redirect')->andReturn(redirect('https://github.com/login/oauth/authorize?mock=1'));

    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get('/auth/github');

    $response->assertRedirect('https://github.com/login/oauth/authorize?mock=1');
});

test('new user can authenticate via github oauth callback and gets onboarded', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('98765432');
    $socialiteUser->shouldReceive('getEmail')->andReturn('dev@github.com');
    $socialiteUser->shouldReceive('getNickname')->andReturn('octo-dev');
    $socialiteUser->shouldReceive('getName')->andReturn('Octo Developer');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatars.githubusercontent.com/u/98765432');
    $socialiteUser->token = 'gho_mock_access_token_abc';
    $socialiteUser->refreshToken = null;

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get('/auth/github/callback');

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('github_id', '98765432')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Octo Developer');
    expect($user->github_username)->toBe('octo-dev');
    expect($user->email)->toBe('dev@github.com');
    expect($user->avatar)->toBe('https://avatars.githubusercontent.com/u/98765432');

    // Verify automated onboarding project was created
    expect($user->projects()->count())->toBe(1);
    expect($user->projects()->first()->tasks()->count())->toBe(5);
});

test('existing user logs in via github oauth callback and refreshes identity', function () {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'github_id' => '11223344',
        'github_username' => 'old-handle',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('11223344');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getNickname')->andReturn('new-handle');
    $socialiteUser->shouldReceive('getName')->andReturn('Updated Name');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatars.githubusercontent.com/u/11223344?v=4');
    $socialiteUser->token = 'gho_updated_token';
    $socialiteUser->refreshToken = 'ghr_refresh_token';

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get('/auth/github/callback');

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));

    $user->refresh();
    expect($user->github_username)->toBe('new-handle');
    expect($user->avatar)->toBe('https://avatars.githubusercontent.com/u/11223344?v=4');
});

test('handles failed github oauth callback gracefully', function () {
    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->andThrow(new \Exception('The user cancelled authorization'));

    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get('/auth/github/callback');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['oauth']);
});

test('authenticated users are redirected away from login screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('authenticated users are redirected away from register screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

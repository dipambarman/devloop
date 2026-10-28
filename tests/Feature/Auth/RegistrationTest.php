<?php

use App\Models\User;

test('registration screen can be rendered with github signup', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertSee('Join DevLoop');
    $response->assertSee('Sign Up with GitHub');
    $response->assertSee('github-register-btn');
});

test('authenticated users are redirected away from registration screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('legacy password registration post endpoint is discontinued', function () {
    $response = $this->post('/register', [
        'name' => 'Legacy User',
        'email' => 'legacy@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Password registration route has been deleted in favor of pure GitHub OAuth
    $response->assertStatus(405);
});

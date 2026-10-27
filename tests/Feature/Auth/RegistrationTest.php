<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->projects()->count())->toBe(1);
    expect($user->notes()->count())->toBe(1);
    expect($user->snippets()->count())->toBe(1);
    expect($user->projects()->first()->tasks()->count())->toBe(5);
});

test('registration fails when email is already taken', function () {
    \App\Models\User::factory()->create(['email' => 'duplicate@example.com']);

    $response = $this->post('/register', [
        'name' => 'Another User',
        'email' => 'duplicate@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('registration fails when password confirmation does not match', function () {
    $response = $this->post('/register', [
        'name' => 'Mismatch User',
        'email' => 'mismatch@example.com',
        'password' => 'password123',
        'password_confirmation' => 'differentpassword',
    ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertGuest();
});

test('registration fails when required fields are missing', function () {
    $response = $this->post('/register', [
        'name' => '',
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
    $this->assertGuest();
});

test('registration fails when password is shorter than 8 characters', function () {
    $response = $this->post('/register', [
        'name' => 'Short Pass User',
        'email' => 'shortpass@example.com',
        'password' => '12345',
        'password_confirmation' => '12345',
    ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertGuest();
});

test('authenticated users are redirected away from registration screen', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    $response->assertRedirect(route('dashboard', absolute: false));
});



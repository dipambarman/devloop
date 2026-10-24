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


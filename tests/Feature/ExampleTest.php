<?php

it('returns a successful response for the homepage', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('returns a successful response for the changelog page', function () {
    $response = $this->get('/changelog');

    $response->assertStatus(200);
    $response->assertSee('v1.4.0');
});

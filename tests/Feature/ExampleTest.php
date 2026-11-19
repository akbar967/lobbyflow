<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated visitors are redirected to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('authenticated user can view the lobby application', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200);
});

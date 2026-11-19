<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login page is accessible to unauthenticated users', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('LobbyFlow Enterprise');
    $response->assertSee('Admin');
    $response->assertSee('Resepsionis');
    $response->assertSee('Masuk sbg Admin');
    $response->assertSee('Masuk sbg Resepsionis');
});

test('admin can log in with valid credentials and redirect to dashboard', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@lobbyflow.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('login.post'), [
        'email' => 'admin@lobbyflow.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('visitors.index'));
    $this->assertAuthenticatedAs($admin);
});

test('quick login allows instant sign-in for examiner evaluation', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@lobbyflow.com',
    ]);

    $response = $this->get(route('login.quick', 'admin'));

    $response->assertRedirect(route('visitors.index'));
    $this->assertAuthenticatedAs($admin);

    $receptionist = User::factory()->receptionist()->create([
        'email' => 'resepsionis@lobbyflow.com',
    ]);

    $responseRec = $this->get(route('login.quick', 'receptionist'));

    $responseRec->assertRedirect(route('visitors.index'));
    $this->assertAuthenticatedAs($receptionist);
});

test('logout terminates user session and redirects to login page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

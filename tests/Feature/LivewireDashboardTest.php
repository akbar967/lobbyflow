<?php

use App\Livewire\LobbyDashboard;
use App\Models\Employee;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('renders lobby dashboard livewire component successfully', function () {
    Livewire::test(LobbyDashboard::class)
        ->assertStatus(200)
        ->assertSee('Check-In Tamu Baru')
        ->assertSee('Daftar Aktivitas Tamu')
        ->assertSee('Belum ada data tamu hari ini.');
});

test('livewire creates a new visitor reactively without page reload', function () {
    $employee = Employee::create([
        'name' => 'Budi Santoso',
        'department' => 'HRD',
        'email' => 'budi@company.com',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->set('name', 'Bambang Sudirman')
        ->set('instansi', 'PT Graha Pratama')
        ->set('employee_id', $employee->id)
        ->set('purpose', 'Konsultasi Kontrak')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Tamu berhasil check-in!')
        ->assertSee('Bambang Sudirman')
        ->assertSee('PT Graha Pratama')
        ->assertSee('Di Dalam Gedung');

    $this->assertDatabaseHas('visitors', [
        'name' => 'Bambang Sudirman',
        'instansi' => 'PT Graha Pratama',
        'status' => 'INSIDE',
    ]);
});

test('livewire filters visitors reactively as user types in search', function () {
    $employee = Employee::create([
        'name' => 'Siti Aminah',
        'department' => 'Finance',
        'email' => 'siti@company.com',
    ]);

    Visitor::create([
        'name' => 'Nadia Amanda',
        'nama_tamu' => 'Nadia Amanda',
        'instansi' => 'PT Bintang Lima',
        'employee_id' => $employee->id,
        'purpose' => 'Meeting',
        'status' => 'INSIDE',
    ]);

    Visitor::create([
        'name' => 'Surya Saputra',
        'nama_tamu' => 'Surya Saputra',
        'instansi' => 'CV Mega Cipta',
        'employee_id' => $employee->id,
        'purpose' => 'Kunjungan',
        'status' => 'INSIDE',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->assertSee('Nadia Amanda')
        ->assertSee('Surya Saputra')
        ->set('search', 'Nadia')
        ->assertSee('Nadia Amanda')
        ->assertDontSee('Surya Saputra')
        ->call('resetSearch')
        ->assertSee('Nadia Amanda')
        ->assertSee('Surya Saputra');
});

test('livewire executes 1-click check-out reactively', function () {
    $employee = Employee::create([
        'name' => 'Andi Pratama',
        'department' => 'IT Support',
        'email' => 'andi@company.com',
    ]);

    $visitor = Visitor::create([
        'name' => 'Rizky Ramadhan',
        'nama_tamu' => 'Rizky Ramadhan',
        'instansi' => 'PT Cyber Data',
        'employee_id' => $employee->id,
        'purpose' => 'Audit Server',
        'status' => 'INSIDE',
        'check_in_at' => now()->subMinutes(30),
        'waktu_checkin' => now()->subMinutes(30),
    ]);

    Livewire::test(LobbyDashboard::class)
        ->assertSee('Rizky Ramadhan')
        ->assertSee('Di Dalam Gedung')
        ->call('checkout', $visitor->id)
        ->assertSee('Selesai / Check-Out')
        ->assertSee('30 menit');

    expect($visitor->fresh()->status)->toBe('CHECKED_OUT')
        ->and($visitor->fresh()->durasi)->toContain('30 menit');
});

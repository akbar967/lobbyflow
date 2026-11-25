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

test('livewire filters visitors by status filter tabs', function () {
    $employee = Employee::create([
        'name' => 'Budi Santoso',
        'department' => 'HRD',
        'email' => 'budi@company.com',
    ]);

    $visitorInside = Visitor::create([
        'name' => 'Tamu Sedang Di Dalam',
        'nama_tamu' => 'Tamu Sedang Di Dalam',
        'instansi' => 'PT Pertama',
        'employee_id' => $employee->id,
        'purpose' => 'Meeting',
        'status' => 'INSIDE',
    ]);

    $visitorOut = Visitor::create([
        'name' => 'Tamu Sudah Pulang',
        'nama_tamu' => 'Tamu Sudah Pulang',
        'instansi' => 'PT Kedua',
        'employee_id' => $employee->id,
        'purpose' => 'Kunjungan',
        'status' => 'CHECKED_OUT',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->assertSee('Tamu Sedang Di Dalam')
        ->assertSee('Tamu Sudah Pulang')
        ->call('setStatusFilter', 'INSIDE')
        ->assertSee('Tamu Sedang Di Dalam')
        ->assertDontSee('Tamu Sudah Pulang')
        ->call('setStatusFilter', 'CHECKED_OUT')
        ->assertSee('Tamu Sudah Pulang')
        ->assertDontSee('Tamu Sedang Di Dalam')
        ->call('setStatusFilter', 'ALL')
        ->assertSee('Tamu Sedang Di Dalam')
        ->assertSee('Tamu Sudah Pulang');
});

test('livewire executes fast checkout via quick pass barcode scanner', function () {
    $employee = Employee::create([
        'name' => 'Dewi Lestari',
        'department' => 'Marketing',
        'email' => 'dewi@company.com',
    ]);

    $visitor = Visitor::create([
        'name' => 'Tamu Cepat',
        'nama_tamu' => 'Tamu Cepat',
        'instansi' => 'PT Kilat',
        'employee_id' => $employee->id,
        'purpose' => 'Drop Barang',
        'qr_code' => 'PASS-QUICK123',
        'status' => 'INSIDE',
        'check_in_at' => now()->subMinutes(15),
        'waktu_checkin' => now()->subMinutes(15),
    ]);

    Livewire::test(LobbyDashboard::class)
        ->set('quickPassCode', 'PASS-QUICK123')
        ->call('quickCheckout')
        ->assertSee('berhasil check-out')
        ->assertSee('15 menit');

    expect($visitor->fresh()->status)->toBe('CHECKED_OUT');
});

test('livewire manages employee master data including deletion', function () {
    $employee = Employee::create([
        'name' => 'Pegawai Uji Hapus',
        'department' => 'Testing',
        'email' => 'uji@company.com',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->assertSee('Pegawai Uji Hapus')
        ->call('deleteEmployee', $employee->id)
        ->assertSee('berhasil dihapus');

    expect(Employee::find($employee->id))->toBeNull();
});

test('livewire supports visitor pre-registration and arrival confirmation', function () {
    $employee = Employee::create([
        'name' => 'Ferry Gunawan',
        'department' => 'Legal',
        'email' => 'ferry@company.com',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->set('name', 'Tamu Terjadwal')
        ->set('instansi', 'PT Jadwal Sukses')
        ->set('employee_id', $employee->id)
        ->set('purpose', 'Tanda Tangan MoU')
        ->set('is_preregistered', true)
        ->call('save')
        ->assertSee('Pra-Registrasi Tamu berhasil dijadwalkan')
        ->assertSee('Dijadwalkan');

    $visitor = Visitor::where('name', 'Tamu Terjadwal')->first();
    expect($visitor->status)->toBe('SCHEDULED');

    // Resepsionis mengonfirmasi kedatangan saat tamu tiba di gedung
    Livewire::test(LobbyDashboard::class)
        ->call('confirmArrival', $visitor->id)
        ->assertSee('telah dikonfirmasi tiba di lobi');

    expect($visitor->fresh()->status)->toBe('INSIDE');
});

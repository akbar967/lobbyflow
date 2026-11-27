<?php

use App\Livewire\LobbyDashboard;
use App\Models\Employee;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

test('livewire generates demo visitors to make dashboard lively', function () {
    Livewire::test(LobbyDashboard::class)
        ->call('generateDemoVisitors')
        ->assertSee('Google Indonesia')
        ->assertSee('PT Bank Central Asia Tbk')
        ->assertSee('PT Telkomsel Regional');

    expect(Visitor::count())->toBeGreaterThanOrEqual(5);
});

test('livewire generates whatsapp notification preview for host', function () {
    $employee = Employee::create([
        'name' => 'Maya Anggraini',
        'department' => 'Corporate Secretary',
        'email' => 'maya@company.com',
    ]);

    $visitor = Visitor::create([
        'name' => 'Doni Siregar',
        'nama_tamu' => 'Doni Siregar',
        'instansi' => 'PT Mitra Utama',
        'employee_id' => $employee->id,
        'purpose' => 'Audiensi Direksi',
        'status' => 'INSIDE',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->call('openWhatsAppModal', $visitor->id)
        ->assertSee('Kirim Notifikasi WhatsApp ke Host')
        ->assertSee('Doni Siregar')
        ->assertSee('PT Mitra Utama')
        ->assertSee('Maya Anggraini');
});

test('livewire edits and saves announcement ticker', function () {
    Livewire::test(LobbyDashboard::class)
        ->call('editAnnouncement')
        ->set('newAnnouncement', '📢 Hari ini ada audit ISO 27001 di lantai 3.')
        ->call('saveAnnouncement')
        ->assertSee('Pengumuman lobi berhasil diperbarui!')
        ->assertSee('audit ISO 27001');
});

test('livewire exports today visitors to csv', function () {
    $employee = Employee::create([
        'name' => 'Budi Santoso',
        'department' => 'HRD',
        'email' => 'budi@company.com',
    ]);

    Visitor::create([
        'name' => 'Ferry Salim',
        'nama_tamu' => 'Ferry Salim',
        'instansi' => 'PT Surya Harapan',
        'employee_id' => $employee->id,
        'purpose' => 'Kerjasama Bisnis',
        'status' => 'INSIDE',
    ]);

    $component = Livewire::test(LobbyDashboard::class);
    $response = $component->instance()->exportTodayCsv();

    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->getStatusCode())->toBe(200);

    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('Ferry Salim');
    expect($csvContent)->toContain('PT Surya Harapan');
    expect($csvContent)->toContain('Kode Pass');
});

test('livewire opens and closes evacuation roster modal', function () {
    $employee = Employee::create([
        'name' => 'Rahmat Hidayat',
        'department' => 'Operations',
        'email' => 'rahmat@company.com',
    ]);

    Visitor::create([
        'name' => 'Rangga Wijaya',
        'nama_tamu' => 'Rangga Wijaya',
        'instansi' => 'PT Megah Prima',
        'employee_id' => $employee->id,
        'purpose' => 'Inspeksi Gedung',
        'status' => 'INSIDE',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->call('openEvacuationModal')
        ->assertSee('Daftar Evakuasi Darurat (K3 / HSE Muster Roll)')
        ->assertSee('Rangga Wijaya')
        ->assertSee('PT Megah Prima')
        ->call('closeEvacuationModal')
        ->assertDontSee('Daftar Evakuasi Darurat (K3 / HSE Muster Roll)');
});

test('livewire dispatches play-chime event on visitor save and checkout', function () {
    $employee = Employee::create([
        'name' => 'Siti Aminah',
        'department' => 'Finance',
        'email' => 'siti@company.com',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->set('name', 'Nanda Pratama')
        ->set('instansi', 'PT Cahaya Abadi')
        ->set('employee_id', $employee->id)
        ->set('purpose', 'Konsultasi Finansial')
        ->call('save')
        ->assertDispatched('play-chime');
});

test('livewire opens and closes features directory modal', function () {
    Livewire::test(LobbyDashboard::class)
        ->call('openFeaturesModal')
        ->assertSee('Direktori 12 Fitur Unggulan Sistem')
        ->assertSee('Smart Scan AI (Vision OCR)')
        ->assertSee('Quick Pass Barcode Scanner')
        ->call('closeFeaturesModal')
        ->assertDontSee('Katalog modul dan kapabilitas sistem LobbyFlow');
});

test('livewire supports VIP visitor creation and toggle', function () {
    $employee = Employee::create([
        'name' => 'Dr. Gunawan',
        'department' => 'Direksi',
        'email' => 'gunawan@company.com',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->set('name', 'Tamu VIP Menteri')
        ->set('instansi', 'Kementerian Keuangan')
        ->set('employee_id', $employee->id)
        ->set('purpose', 'Kunjungan Kerja')
        ->set('is_vip', true)
        ->call('save')
        ->assertSee('Tamu berhasil check-in!');

    $visitor = Visitor::where('name', 'Tamu VIP Menteri')->first();
    expect($visitor->is_vip)->toBeTrue();

    Livewire::test(LobbyDashboard::class)
        ->call('toggleVip', $visitor->id);

    expect($visitor->fresh()->is_vip)->toBeFalse();
});

test('livewire executes batch check-out for multiple selected visitors', function () {
    $employee = Employee::create([
        'name' => 'Hendra',
        'department' => 'GA',
        'email' => 'hendra@company.com',
    ]);

    $v1 = Visitor::create([
        'name' => 'Delegasi 1',
        'instansi' => 'PT Rombongan',
        'employee_id' => $employee->id,
        'purpose' => 'Studi Banding',
        'status' => 'INSIDE',
    ]);

    $v2 = Visitor::create([
        'name' => 'Delegasi 2',
        'instansi' => 'PT Rombongan',
        'employee_id' => $employee->id,
        'purpose' => 'Studi Banding',
        'status' => 'INSIDE',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->set('selectedVisitors', [(string) $v1->id, (string) $v2->id])
        ->call('batchCheckout')
        ->assertSee('Berhasil melakukan check-out massal untuk 2 tamu!');

    expect($v1->fresh()->status)->toBe('CHECKED_OUT');
    expect($v2->fresh()->status)->toBe('CHECKED_OUT');
});

test('livewire opens and closes wifi and camera modals and kiosk mode', function () {
    Livewire::test(LobbyDashboard::class)
        ->call('openWifiModal')
        ->assertSee('Akses Wi-Fi Tamu Gedung')
        ->assertSee('LobbyFlow-Guest')
        ->call('closeWifiModal')
        ->assertDontSee('Akses Wi-Fi Tamu Gedung')
        ->call('openCameraModal')
        ->assertSee('Kamera Scanner Pass Tamu')
        ->call('closeCameraModal')
        ->assertDontSee('Kamera Scanner Pass Tamu')
        ->call('toggleKioskMode')
        ->assertSee('LobbyFlow Self-Service')
        ->call('toggleKioskMode')
        ->assertDontSee('LobbyFlow Self-Service');
});

test('admin sees executive command center and can delete visitor records', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test@lobbyflow.com',
        'role' => 'admin',
    ]);

    $employee = Employee::create([
        'name' => 'Host Test',
        'department' => 'Management',
        'email' => 'host@company.com',
    ]);

    $visitor = Visitor::create([
        'name' => 'Tamu Akan Dihapus',
        'instansi' => 'PT Test',
        'employee_id' => $employee->id,
        'purpose' => 'Audit',
        'status' => 'INSIDE',
    ]);

    $this->actingAs($admin);

    Livewire::test(LobbyDashboard::class)
        ->assertSee('Admin Command Center')
        ->assertSee('Pusat Supervisi dan Tata Kelola Admin')
        ->assertSee('Kepatuhan Check-Out')
        ->assertSee('Batalkan / Hapus Kunjungan (Admin)')
        ->call('deleteVisitor', $visitor->id)
        ->assertSee('berhasil dibatalkan/dihapus oleh Administrator');

    expect(Visitor::find($visitor->id))->toBeNull();
});

test('receptionist sees front desk hub and cannot delete visitor records', function () {
    $receptionist = User::factory()->create([
        'email' => 'receptionist_test@lobbyflow.com',
        'role' => 'receptionist',
    ]);

    $employee = Employee::create([
        'name' => 'Host Resepsionis',
        'department' => 'Lobby',
        'email' => 'lobbyhost@company.com',
    ]);

    $visitor = Visitor::create([
        'name' => 'Tamu Tetap Aman',
        'instansi' => 'PT Aman',
        'employee_id' => $employee->id,
        'purpose' => 'Meeting',
        'status' => 'INSIDE',
    ]);

    $this->actingAs($receptionist);

    Livewire::test(LobbyDashboard::class)
        ->assertSee('Meja Resepsionis Lobi')
        ->assertSee('Pusat Layanan Meja Resepsionis')
        ->assertDontSee('Batalkan / Hapus Kunjungan (Admin)')
        ->call('deleteVisitor', $visitor->id)
        ->assertSee('Akses Terbatas: Hanya Administrator');

    // Visitor should NOT be deleted
    expect(Visitor::find($visitor->id))->not->toBeNull();
});

test('can schedule visitor with specific appointment date and time and confirm arrival', function () {
    $employee = Employee::create([
        'name' => 'Dr. Hendro',
        'department' => 'R&D',
        'email' => 'hendro@company.com',
    ]);

    $scheduleDate = now()->addDays(2)->setHour(14)->setMinute(30);

    Livewire::test(LobbyDashboard::class)
        ->set('name', 'Tamu Terjadwal Khusus')
        ->set('instansi', 'PT Inovasi Mandiri')
        ->set('employee_id', $employee->id)
        ->set('purpose', 'Diskusi MoU Riset')
        ->set('is_preregistered', true)
        ->set('scheduled_at', $scheduleDate->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Pra-Registrasi Tamu berhasil dijadwalkan!')
        ->assertSee('Dijadwalkan')
        ->assertSee('Jadwal:');

    $visitor = Visitor::where('name', 'Tamu Terjadwal Khusus')->first();
    expect($visitor)->not->toBeNull()
        ->and($visitor->status)->toBe('SCHEDULED')
        ->and($visitor->check_in_at)->toBeNull()
        ->and($visitor->scheduled_at)->not->toBeNull();

    Livewire::test(LobbyDashboard::class)
        ->call('confirmArrival', $visitor->id)
        ->assertSee('telah dikonfirmasi tiba di lobi');

    expect($visitor->fresh()->status)->toBe('INSIDE')
        ->and($visitor->fresh()->check_in_at)->not->toBeNull();
});

test('handles scheduled_at gracefully when pra-registration is enabled', function () {
    $employee = Employee::create([
        'name' => 'Ibu Maya',
        'department' => 'Legal',
        'email' => 'maya@company.com',
    ]);

    Livewire::test(LobbyDashboard::class)
        ->set('name', 'Tamu Tanpa Jam Khusus')
        ->set('instansi', 'PT Mitra')
        ->set('employee_id', $employee->id)
        ->set('purpose', 'Meeting')
        ->set('is_preregistered', true)
        ->set('scheduled_at', null)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Pra-Registrasi Tamu berhasil dijadwalkan');

    $visitor = Visitor::where('name', 'Tamu Tanpa Jam Khusus')->first();
    expect($visitor)->not->toBeNull()
        ->and($visitor->status)->toBe('SCHEDULED')
        ->and($visitor->scheduled_at)->not->toBeNull();
});

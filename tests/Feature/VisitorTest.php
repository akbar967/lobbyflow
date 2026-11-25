<?php

use App\Mail\VisitorArrivedMail;
use App\Models\Employee;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('displays the lobby page with empty state when no visitors exist', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Belum ada data tamu hari ini.');
});

test('stores a new visitor, generates qr code pass, and dispatches email notification to host', function () {
    Mail::fake();

    $employee = Employee::create([
        'name' => 'Budi Santoso',
        'department' => 'HRD',
        'email' => 'budi@company.com',
    ]);

    $payload = [
        'name' => 'Ahmad Fauzi',
        'instansi' => 'PT Sumber Makmur',
        'employee_id' => $employee->id,
        'purpose' => 'Konsultasi Kerjasama',
    ];

    $response = $this->post(route('visitors.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('visitors', [
        'name' => 'Ahmad Fauzi',
        'nama_tamu' => 'Ahmad Fauzi',
        'instansi' => 'PT Sumber Makmur',
        'employee_id' => $employee->id,
        'purpose' => 'Konsultasi Kerjasama',
        'status' => 'INSIDE',
    ]);

    $visitor = Visitor::where('name', 'Ahmad Fauzi')->first();
    expect($visitor->qr_code)->not->toBeNull()
        ->and($visitor->qr_code)->toContain('PASS-');

    Mail::assertQueued(VisitorArrivedMail::class, function ($mail) use ($employee, $visitor) {
        return $mail->hasTo('budi@company.com')
            && $mail->visitor->id === $visitor->id
            && $mail->employee->id === $employee->id;
    });
});

test('displays stored visitors in the table with status tag and cetak badge button', function () {
    $employee = Employee::create([
        'name' => 'Siti Aminah',
        'department' => 'Finance',
        'email' => 'siti@company.com',
    ]);

    $visitor = Visitor::create([
        'name' => 'Rina Kartika',
        'nama_tamu' => 'Rina Kartika',
        'instansi' => 'CV Digital Kreatif',
        'employee_id' => $employee->id,
        'host_tujuan' => $employee->name,
        'purpose' => 'Pengantaran Invoice',
        'keperluan' => 'Pengantaran Invoice',
        'status' => 'INSIDE',
        'check_in_at' => now(),
        'waktu_checkin' => now(),
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertDontSee('Belum ada data tamu hari ini.');
    $response->assertSee('Rina Kartika');
    $response->assertSee('CV Digital Kreatif');
    $response->assertSee('Siti Aminah');
    $response->assertSee('Di Dalam Gedung');
    $response->assertSee(route('visitors.badge', $visitor->id));
    $response->assertSee('Cetak Badge');
});

test('renders the badge page with visitor details, qr code, and print button', function () {
    $employee = Employee::create([
        'name' => 'Budi Santoso',
        'department' => 'HRD',
        'email' => 'budi@company.com',
    ]);

    $visitor = Visitor::create([
        'name' => 'Hendro Wijaya',
        'nama_tamu' => 'Hendro Wijaya',
        'instansi' => 'PT Teknologi Nusantara',
        'employee_id' => $employee->id,
        'host_tujuan' => $employee->name,
        'purpose' => 'Presentasi Software',
        'keperluan' => 'Presentasi Software',
        'qr_code' => 'PASS-HENDRO99',
        'status' => 'INSIDE',
        'check_in_at' => now(),
        'waktu_checkin' => now(),
    ]);

    $response = $this->get(route('visitors.badge', $visitor->id));

    $response->assertStatus(200);
    $response->assertSee('Hendro Wijaya');
    $response->assertSee('PT Teknologi Nusantara');
    $response->assertSee('Budi Santoso');
    $response->assertSee('Presentasi Software');
    $response->assertSee('PASS-HENDRO99');
    $response->assertSee('VISITOR PASS');
    $response->assertSee('window.print()', false);
    $response->assertSee('<svg', false);
});

test('allows visitor 1-click check-out and calculates duration', function () {
    $employee = Employee::create([
        'name' => 'Andi Pratama',
        'department' => 'IT Support',
        'email' => 'andi@company.com',
    ]);

    // Visitor checked in 45 minutes ago
    $checkInTime = now()->subMinutes(45);

    $visitor = Visitor::create([
        'name' => 'Dedi Gunawan',
        'nama_tamu' => 'Dedi Gunawan',
        'instansi' => 'PT Jaya Abadi',
        'employee_id' => $employee->id,
        'host_tujuan' => $employee->name,
        'purpose' => 'Maintenance Jaringan',
        'status' => 'INSIDE',
        'check_in_at' => $checkInTime,
        'waktu_checkin' => $checkInTime,
    ]);

    $response = $this->patch(route('visitors.checkout', $visitor->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updatedVisitor = $visitor->fresh();
    expect($updatedVisitor->status)->toBe('CHECKED_OUT')
        ->and($updatedVisitor->waktu_checkout)->not->toBeNull()
        ->and($updatedVisitor->durasi)->toContain('45 menit');

    // Verify UI displays "Selesai / Check-Out" and duration
    $lobbyResponse = $this->get('/');
    $lobbyResponse->assertSee('Selesai / Check-Out');
    $lobbyResponse->assertSee('45 menit');
});

test('searches visitors by name or instansi', function () {
    $employee = Employee::create([
        'name' => 'Budi Santoso',
        'department' => 'HRD',
        'email' => 'budi@company.com',
    ]);

    Visitor::create([
        'name' => 'Fajar Pratama',
        'nama_tamu' => 'Fajar Pratama',
        'instansi' => 'PT Solusi Bangsa',
        'employee_id' => $employee->id,
        'purpose' => 'Wawancara Kerja',
        'status' => 'INSIDE',
    ]);

    Visitor::create([
        'name' => 'Dewi Lestari',
        'nama_tamu' => 'Dewi Lestari',
        'instansi' => 'CV Media Prima',
        'employee_id' => $employee->id,
        'purpose' => 'Kerjasama Liputan',
        'status' => 'INSIDE',
    ]);

    // Search by name
    $searchName = $this->get('/?search=Fajar');
    $searchName->assertStatus(200);
    $searchName->assertSee('Fajar Pratama');
    $searchName->assertDontSee('Dewi Lestari');

    // Search by instansi
    $searchInstansi = $this->get('/?search=Media');
    $searchInstansi->assertStatus(200);
    $searchInstansi->assertSee('Dewi Lestari');
    $searchInstansi->assertDontSee('Fajar Pratama');

    // Search with no results
    $searchNotFound = $this->get('/?search=TidakAdaNamaIni');
    $searchNotFound->assertStatus(200);
    $searchNotFound->assertSee('Tidak ada data tamu yang cocok dengan pencarian');
});

test('validates required fields when storing a visitor', function () {
    $response = $this->post(route('visitors.store'), []);

    $response->assertSessionHasErrors(['name', 'instansi', 'employee_id', 'purpose']);
});

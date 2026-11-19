<?php

use App\Models\Employee;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view reports page with statistics, charts, and AI Executive Summary', function () {
    $admin = User::factory()->admin()->create();

    $employee = Employee::create([
        'name' => 'Budi Santoso',
        'department' => 'HRD',
        'email' => 'budi@company.com',
    ]);

    Visitor::create([
        'name' => 'Michael Salim',
        'nama_tamu' => 'Michael Salim',
        'instansi' => 'PT Mega Solusi',
        'employee_id' => $employee->id,
        'purpose' => 'Audit Keuangan',
        'status' => 'INSIDE',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('reports.index', ['month' => now()->format('Y-m')]));

    $response->assertStatus(200);
    $response->assertSee('Laporan & Analisis Kunjungan Tamu', false);
    $response->assertSee('AI Executive Summary');
    $response->assertSee('Michael Salim');
    $response->assertSee('PT Mega Solusi');
    $response->assertSee('Ekspor Excel');
    $response->assertSee('Cetak / Simpan PDF');
    $response->assertSee('dailyTrendChart');
    $response->assertSee('statusChart');
    $response->assertSee('instansiChart');
});

test('receptionist is forbidden from accessing reports page and redirected to dashboard with error', function () {
    $receptionist = User::factory()->receptionist()->create();

    $response = $this->actingAs($receptionist)->get(route('reports.index'));

    $response->assertRedirect(route('visitors.index'));
    $response->assertSessionHas('error');
});

test('unauthenticated user is redirected to login when accessing reports', function () {
    $response = $this->get(route('reports.index'));

    $response->assertRedirect('/login');
});

test('admin can export monthly visitor data to Excel CSV format', function () {
    $admin = User::factory()->admin()->create();

    $employee = Employee::create([
        'name' => 'Siti Aminah',
        'department' => 'Finance',
        'email' => 'siti@company.com',
    ]);

    Visitor::create([
        'name' => 'Dewi Permata',
        'nama_tamu' => 'Dewi Permata',
        'instansi' => 'CV Karya Utama',
        'employee_id' => $employee->id,
        'purpose' => 'Pengiriman Kontrak',
        'status' => 'CHECKED_OUT',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export.excel', ['month' => now()->format('Y-m')]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    // Assert CSV contains column headers and visitor row
    $content = $response->streamedContent();
    expect($content)->toContain('Kode Pass ID')
        ->and($content)->toContain('Dewi Permata')
        ->and($content)->toContain('CV Karya Utama');
});

test('receptionist is blocked from exporting visitor data', function () {
    $receptionist = User::factory()->receptionist()->create();

    $response = $this->actingAs($receptionist)->get(route('reports.export.excel'));

    $response->assertRedirect(route('visitors.index'));
    $response->assertSessionHas('error');
});

test('admin can render printable PDF layout with window print trigger', function () {
    $admin = User::factory()->admin()->create();

    $employee = Employee::create([
        'name' => 'Andi Pratama',
        'department' => 'IT Support',
        'email' => 'andi@company.com',
    ]);

    Visitor::create([
        'name' => 'Fajar Nugraha',
        'nama_tamu' => 'Fajar Nugraha',
        'instansi' => 'PT Solusi Pratama',
        'employee_id' => $employee->id,
        'purpose' => 'Maintenance Server',
        'status' => 'INSIDE',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export.pdf', ['month' => now()->format('Y-m')]));

    $response->assertStatus(200);
    $response->assertSee('LAPORAN AKTIVITAS TAMU');
    $response->assertSee('RINGKASAN EKSEKUTIF BERBASIS AI');
    $response->assertSee('Fajar Nugraha');
    $response->assertSee('window.print()', false);
});

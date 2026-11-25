<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\LobbyDashboard;
use App\Livewire\Login;
use App\Livewire\ReportDashboard;
use App\Livewire\VisitorBadge;
use Illuminate\Support\Facades\Route;

// Autentikasi (Livewire Login & Quick Access)
Route::get('/login', Login::class)->name('login');
Route::post('/login', [Login::class, 'handleLoginPost'])->name('login.post');
if (app()->environment('local', 'testing')) {
    Route::match(['get', 'post'], '/login/quick/{role}', [Login::class, 'handleQuickLogin'])->name('login.quick');
} else {
    Route::post('/login/quick/{role}', [Login::class, 'handleQuickLogin'])->name('login.quick');
}
Route::post('/logout', [Login::class, 'handleLogout'])->name('logout');

// Halaman Resepsionis & Operasional Tamu (Livewire Full-Page)
Route::middleware(['auth'])->group(function () {
    Route::get('/', LobbyDashboard::class)->name('visitors.index');
    Route::post('/visitors', [LobbyDashboard::class, 'handleStore'])->name('visitors.store');
    Route::patch('/visitors/{id}/checkout', [LobbyDashboard::class, 'handleCheckout'])->name('visitors.checkout');
    Route::get('/visitors/{id}/badge', VisitorBadge::class)->name('visitors.badge');

    // Halaman Khusus Laporan & Analitik AI (HANYA UNTUK ADMIN - Livewire Full-Page)
    Route::middleware([EnsureUserIsAdmin::class])->group(function () {
        Route::get('/laporan', ReportDashboard::class)->name('reports.index');
        Route::get('/laporan/export-excel', [ReportDashboard::class, 'handleExportExcel'])->name('reports.export.excel');
        Route::get('/laporan/export-pdf', [ReportDashboard::class, 'handleExportPdf'])->name('reports.export.pdf');

        // Alias URL
        Route::redirect('/reports', '/laporan');
    });
});

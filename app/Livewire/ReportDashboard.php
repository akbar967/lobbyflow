<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
#[Title('Laporan Bulanan Tamu - LobbyFlow')]
class ReportDashboard extends Component
{
    use WithPagination;

    public string $selectedMonth = '';

    public array $stats = [
        'total' => 0,
        'inside' => 0,
        'checked_out' => 0,
        'top_instansi' => '-',
        'top_host' => '-',
    ];

    public array $aiSummary = [];

    public array $dailyDates = [];

    public array $dailyCounts = [];

    public array $instansiLabels = [];

    public array $instansiCounts = [];

    public function mount(): void
    {
        $this->selectedMonth = (string) request()->query('month', now()->format('Y-m'));
        $this->loadReportData();
    }

    public function loadReportData(): void
    {
        $startOfMonth = Carbon::parse($this->selectedMonth.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($this->selectedMonth.'-01')->endOfMonth();

        $visitors = Visitor::with('employee')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->latest()
            ->get();

        $totalVisitors = $visitors->count();
        $totalInside = $visitors->where('status', 'INSIDE')->count();
        $totalCheckedOut = $visitors->where('status', 'CHECKED_OUT')->count();

        $topInstansi = $visitors->groupBy('instansi')
            ->sortByDesc(fn ($items) => $items->count())
            ->keys()
            ->first() ?? '-';

        $topHost = $visitors->groupBy(fn ($v) => $v->employee?->name ?? $v->host_tujuan ?? '-')
            ->sortByDesc(fn ($items) => $items->count())
            ->keys()
            ->first() ?? '-';

        $this->stats = [
            'total' => $totalVisitors,
            'inside' => $totalInside,
            'checked_out' => $totalCheckedOut,
            'top_instansi' => $topInstansi,
            'top_host' => $topHost,
        ];

        // AI Executive Summary
        $geminiService = app(GeminiService::class);
        $this->aiSummary = $geminiService->generateExecutiveSummary($this->stats, $this->selectedMonth);

        // Daily Trend Chart Data
        $daysInMonth = $endOfMonth->day;
        $dailyDates = [];
        $dailyCounts = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = Carbon::createFromDate($startOfMonth->year, $startOfMonth->month, $day)->format('Y-m-d');
            $dailyDates[] = (string) $day;
            $dailyCounts[] = $visitors->filter(function ($v) use ($dateString) {
                return Carbon::parse($v->created_at)->format('Y-m-d') === $dateString;
            })->count();
        }

        $this->dailyDates = $dailyDates;
        $this->dailyCounts = $dailyCounts;

        // Top 5 Instansi Chart Data
        $top5Instansi = $visitors->groupBy('instansi')
            ->map(fn ($items) => $items->count())
            ->sortDesc()
            ->take(5);

        $this->instansiLabels = $top5Instansi->keys()->toArray();
        $this->instansiCounts = $top5Instansi->values()->toArray();

        // Dispatch browser event agar Chart.js langsung mengupdate grafik reaktif tanpa reload
        $this->dispatch('charts-updated', [
            'dates' => $this->dailyDates,
            'counts' => $this->dailyCounts,
            'inside' => $this->stats['inside'],
            'checked_out' => $this->stats['checked_out'],
            'instansiLabels' => $this->instansiLabels,
            'instansiCounts' => $this->instansiCounts,
        ]);
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
        $this->loadReportData();
    }

    public function exportExcel(): StreamedResponse
    {
        return self::streamCsv($this->selectedMonth);
    }

    /**
     * HTTP Streamed Response for Excel / CSV Export (menggantikan ReportController::exportExcel)
     */
    public static function handleExportExcel(Request $request): StreamedResponse
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        return self::streamCsv($selectedMonth);
    }

    public static function streamCsv(string $selectedMonth): StreamedResponse
    {
        $startOfMonth = Carbon::parse($selectedMonth.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($selectedMonth.'-01')->endOfMonth();

        $visitors = Visitor::with('employee')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->oldest()
            ->get();

        $fileName = 'Laporan-Tamu-LobbyFlow-'.$selectedMonth.'.csv';

        return response()->streamDownload(function () use ($visitors) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No',
                'Kode Pass ID',
                'Nama Tamu',
                'Instansi / Perusahaan',
                'Host (Pegawai Dituju)',
                'Departemen Host',
                'Keperluan Kunjungan',
                'Waktu Check-In',
                'Waktu Check-Out',
                'Durasi Kunjungan',
                'Status',
            ]);

            foreach ($visitors as $index => $v) {
                fputcsv($handle, [
                    $index + 1,
                    $v->qr_code ?: ('PASS-'.$v->id),
                    $v->name ?: $v->nama_tamu,
                    $v->instansi,
                    $v->employee?->name ?: $v->host_tujuan ?: '-',
                    $v->employee?->department ?: '-',
                    $v->purpose ?: $v->keperluan ?: '-',
                    $v->waktu_checkin ?: $v->check_in_at ?: $v->created_at,
                    $v->waktu_checkout ?: $v->check_out_at ?: '-',
                    $v->durasi ?: '-',
                    $v->status === 'INSIDE' ? 'Di Dalam Gedung' : 'Selesai / Check-Out',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * HTTP Handler for PDF Print Layout (menggantikan ReportController::exportPdf)
     */
    public static function handleExportPdf(Request $request, GeminiService $geminiService)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($selectedMonth.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($selectedMonth.'-01')->endOfMonth();

        $visitors = Visitor::with('employee')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->oldest()
            ->get();

        $stats = [
            'total' => $visitors->count(),
            'inside' => $visitors->where('status', 'INSIDE')->count(),
            'checked_out' => $visitors->where('status', 'CHECKED_OUT')->count(),
            'top_instansi' => $visitors->groupBy('instansi')->sortByDesc(fn ($i) => $i->count())->keys()->first() ?? '-',
            'top_host' => $visitors->groupBy(fn ($v) => $v->employee?->name ?? $v->host_tujuan ?? '-')->sortByDesc(fn ($i) => $i->count())->keys()->first() ?? '-',
        ];

        $aiSummary = $geminiService->generateExecutiveSummary($stats, $selectedMonth);

        return view('reports.print_pdf', compact('visitors', 'selectedMonth', 'stats', 'aiSummary'));
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirect(route('login'));
    }

    public function render()
    {
        $startOfMonth = Carbon::parse($this->selectedMonth.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($this->selectedMonth.'-01')->endOfMonth();

        $visitors = Visitor::with('employee')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->latest()
            ->paginate(10);

        return view('livewire.report-dashboard', [
            'visitors' => $visitors,
        ]);
    }
}

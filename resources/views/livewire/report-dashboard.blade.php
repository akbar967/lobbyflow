<div class="max-w-7xl mx-auto p-6">
    <!-- Top Navigation / Header -->
    <header class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('visitors.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">📊 Laporan & Analisis Kunjungan Tamu</h1>
            <p class="text-xs text-gray-500">Laporan bulanan, ekspor data eksekutif, dan ringkasan analitik berbasis AI &bull; Livewire Report Engine</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- User Profile & Logout -->
            @auth
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm text-xs">
                    <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-[10px]">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                        <span class="ml-1 bg-purple-100 text-purple-700 text-[10px] font-bold px-1.5 py-0.2 rounded-full uppercase">Admin</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline ml-2 pl-2 border-l border-gray-200">
                        @csrf
                        <button type="submit" wire:click="logout" class="text-rose-600 hover:text-rose-800 font-medium transition" title="Keluar dari sistem">
                            Keluar
                        </button>
                    </form>
                </div>
            @endauth

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export.excel', ['month' => $selectedMonth]) }}" wire:click.prevent="exportExcel" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ekspor Excel (.csv)</span>
                </a>
                <a href="{{ route('reports.export.pdf', ['month' => $selectedMonth]) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak / Simpan PDF</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Filter Periode Bulan -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-gray-700">Pilih Periode Bulan:</label>
            <input type="month" wire:model.live="selectedMonth" value="{{ $selectedMonth }}" class="p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="text-xs text-gray-500">
            Menampilkan data untuk periode: <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</span>
        </div>
    </div>

    <!-- KPI Statistic Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Kunjungan</span>
                <span class="text-xl">👥</span>
            </div>
            <p class="text-2xl font-extrabold text-gray-900 mt-2">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Tamu terdaftar bulan ini</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Di Dalam Gedung</span>
                <span class="text-xl">🏢</span>
            </div>
            <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $stats['inside'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Status aktif saat ini</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Selesai Check-Out</span>
                <span class="text-xl">✅</span>
            </div>
            <p class="text-2xl font-extrabold text-indigo-600 mt-2">{{ $stats['checked_out'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Telah menyelesaikan kunjungan</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Top Mitra Korporasi</span>
                <span class="text-xl">⭐</span>
            </div>
            <p class="text-lg font-bold text-gray-900 mt-2 truncate">{{ $stats['top_instansi'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Instansi paling sering berkunjung</p>
        </div>
    </div>

    <!-- 📈 Visual Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Chart 1: Tren Harian (2 cols on lg) -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">📈 Tren Kunjungan Harian (Bulan Ini)</h2>
                    <p class="text-xs text-gray-500">Volume tamu yang datang setiap hari di bulan {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</p>
                </div>
                <span class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full font-medium">Line Chart</span>
            </div>
            <div class="h-64" wire:ignore>
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Distribusi Status (1 col on lg) -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">🎯 Distribusi Status Tamu</h2>
                        <p class="text-xs text-gray-500">Perbandingan tamu aktif vs check-out</p>
                    </div>
                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full font-medium">Doughnut</span>
                </div>
                <div class="h-48 relative flex items-center justify-center" wire:ignore>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-3 border-t text-center text-xs mt-2">
                <div class="p-2 bg-emerald-50 rounded-lg">
                    <span class="text-gray-500 block text-[11px]">Di Dalam</span>
                    <span class="font-bold text-emerald-700 text-sm">{{ $stats['inside'] }}</span>
                </div>
                <div class="p-2 bg-indigo-50 rounded-lg">
                    <span class="text-gray-500 block text-[11px]">Check-Out</span>
                    <span class="font-bold text-indigo-700 text-sm">{{ $stats['checked_out'] }}</span>
                </div>
            </div>
        </div>

        <!-- Chart 3: Top 5 Mitra Korporasi (3 cols on lg) -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 lg:col-span-3">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">🏢 Top 5 Mitra Korporasi Teraktif</h2>
                    <p class="text-xs text-gray-500">Instansi / perusahaan dengan frekuensi kunjungan tertinggi</p>
                </div>
                <span class="text-xs bg-purple-50 text-purple-700 px-2.5 py-1 rounded-full font-medium">Bar Chart</span>
            </div>
            <div class="h-56" wire:ignore>
                <canvas id="instansiChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ✨ Executive Summary AI Section -->
    <div class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 rounded-2xl shadow-md p-6 text-white mb-8 border border-indigo-700/50">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-indigo-500/30 rounded-lg border border-indigo-400/40">
                    <span class="text-xl">✨</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold tracking-tight">AI Executive Summary</h2>
                    <p class="text-xs text-indigo-200">Analisis cerdas pola kunjungan dan rekomendasi keamanan operasional</p>
                </div>
            </div>
            <span class="text-xs bg-indigo-500/30 text-indigo-100 border border-indigo-400/30 px-3 py-1 rounded-full font-medium">
                Smart Insights Engine
            </span>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/10 mb-4">
            <h3 class="font-semibold text-base text-yellow-300 mb-1">{{ $aiSummary['headline'] ?? 'Analisis Pola Kunjungan' }}</h3>
            <p class="text-sm text-indigo-50 leading-relaxed">{{ $aiSummary['traffic_analysis'] ?? 'Memuat ringkasan eksekutif...' }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="bg-indigo-950/40 p-3.5 rounded-xl border border-indigo-600/30">
                <p class="font-bold text-indigo-300 mb-1 flex items-center gap-1.5">
                    <span>🏢</span> Pola Kemitraan
                </p>
                <p class="text-indigo-100 leading-relaxed">{{ $aiSummary['corporate_pattern'] ?? '-' }}</p>
            </div>
            <div class="bg-indigo-950/40 p-3.5 rounded-xl border border-indigo-600/30">
                <p class="font-bold text-indigo-300 mb-1 flex items-center gap-1.5">
                    <span>🛡️</span> Kepatuhan Keamanan
                </p>
                <p class="text-indigo-100 leading-relaxed">{{ $aiSummary['security_compliance'] ?? '-' }}</p>
            </div>
            <div class="bg-indigo-950/40 p-3.5 rounded-xl border border-indigo-600/30">
                <p class="font-bold text-indigo-300 mb-1 flex items-center gap-1.5">
                    <span>💡</span> Rekomendasi AI
                </p>
                <p class="text-indigo-100 leading-relaxed">{{ $aiSummary['recommendation'] ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Tabel Lengkap Laporan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Rincian Data Kunjungan Tamu</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <th class="pb-3">No</th>
                        <th class="pb-3">Kode ID</th>
                        <th class="pb-3">Tamu & Instansi</th>
                        <th class="pb-3">Host Tujuan</th>
                        <th class="pb-3">Keperluan</th>
                        <th class="pb-3">Waktu Masuk</th>
                        <th class="pb-3">Waktu Keluar</th>
                        <th class="pb-3">Durasi</th>
                        <th class="pb-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    @forelse($visitors as $index => $v)
                        <tr class="hover:bg-gray-50/75 transition">
                            <td class="py-3 text-gray-500 text-xs">{{ $index + 1 }}</td>
                            <td class="py-3 font-mono text-xs text-indigo-600 font-semibold">{{ $v->qr_code ?? ('PASS-' . $v->id) }}</td>
                            <td class="py-3">
                                <p class="font-semibold text-gray-800">{{ $v->name ?? $v->nama_tamu }}</p>
                                <p class="text-xs text-gray-500">{{ $v->instansi }}</p>
                            </td>
                            <td class="py-3 text-gray-700">
                                <p class="font-medium">{{ $v->employee?->name ?? $v->host_tujuan ?? '-' }}</p>
                                @if($v->employee?->department)
                                    <p class="text-xs text-gray-400">{{ $v->employee->department }}</p>
                                @endif
                            </td>
                            <td class="py-3 text-gray-600">{{ $v->purpose ?? $v->keperluan ?? '-' }}</td>
                            <td class="py-3 text-xs text-gray-600">
                                @php $cIn = $v->waktu_checkin ?? $v->check_in_at; @endphp
                                {{ $cIn ? \Carbon\Carbon::parse($cIn)->format('d M, H:i') : '-' }}
                            </td>
                            <td class="py-3 text-xs text-gray-600">
                                @php $cOut = $v->waktu_checkout ?? $v->check_out_at; @endphp
                                {{ $cOut ? \Carbon\Carbon::parse($cOut)->format('d M, H:i') : '-' }}
                            </td>
                            <td class="py-3 text-xs text-gray-500 font-medium">{{ $v->durasi_kunjungan ?? $v->durasi ?? '-' }}</td>
                            <td class="py-3 text-center">
                                @if($v->status === 'INSIDE')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Di Dalam
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-400">
                                Tidak ada data kunjungan untuk periode bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js Initialization -->
    <script>
        function renderCharts() {
            // 1. Daily Trend Chart (Line)
            const dailyCtx = document.getElementById('dailyTrendChart');
            if (dailyCtx) {
                const dailyDates = {!! json_encode($dailyDates) !!};
                const dailyCounts = {!! json_encode($dailyCounts) !!};

                if (window.myDailyChart) window.myDailyChart.destroy();
                window.myDailyChart = new Chart(dailyCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dailyDates.map(d => 'Tgl ' + d),
                        datasets: [{
                            label: 'Jumlah Kunjungan',
                            data: dailyCounts,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.12)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#4f46e5',
                            pointRadius: 3,
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, font: { size: 11 } },
                                grid: { color: '#f3f4f6' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // 2. Status Doughnut Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const insideCount = {{ (int) $stats['inside'] }};
                const checkedOutCount = {{ (int) $stats['checked_out'] }};

                if (window.myStatusChart) window.myStatusChart.destroy();
                window.myStatusChart = new Chart(statusCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Di Dalam Gedung', 'Selesai Check-Out'],
                        datasets: [{
                            data: [insideCount, checkedOutCount],
                            backgroundColor: ['#10b981', '#6366f1'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { size: 11 } }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }

            // 3. Top 5 Instansi Chart (Bar)
            const instansiCtx = document.getElementById('instansiChart');
            if (instansiCtx) {
                const instansiLabels = {!! json_encode($instansiLabels) !!};
                const instansiCounts = {!! json_encode($instansiCounts) !!};

                if (window.myInstansiChart) window.myInstansiChart.destroy();
                window.myInstansiChart = new Chart(instansiCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: instansiLabels.length > 0 ? instansiLabels : ['Belum ada data'],
                        datasets: [{
                            label: 'Frekuensi Kunjungan',
                            data: instansiCounts.length > 0 ? instansiCounts : [0],
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.85)',
                                'rgba(168, 85, 247, 0.85)',
                                'rgba(236, 72, 153, 0.85)',
                                'rgba(14, 165, 233, 0.85)',
                                'rgba(16, 185, 129, 0.85)'
                            ],
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, font: { size: 11 } },
                                grid: { color: '#f3f4f6' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', renderCharts);
        document.addEventListener('livewire:navigated', renderCharts);
    </script>
</div>

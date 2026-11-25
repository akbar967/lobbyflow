<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan Tamu - {{ $selectedMonth }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                font-size: 11pt;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
    </style>
</head>
<body class="bg-gray-100 p-6 min-h-screen">
    <!-- Floating Action Bar (Disembunyikan saat dicetak) -->
    <div class="no-print max-w-5xl mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-md border border-gray-200">
        <div class="flex items-center gap-2">
            <span class="text-lg">🖨️</span>
            <div>
                <p class="font-bold text-gray-800 text-sm">Mode Pratinjau Dokumen PDF / Cetak</p>
                <p class="text-xs text-gray-500">Pilih "Save as PDF" di dialog cetak browser Anda untuk menyimpan sebagai file PDF.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                Tutup
            </button>
        </div>
    </div>

    <!-- Container Dokumen Cetak -->
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-xl shadow border border-gray-200">
        <!-- Dokumen Header / Kop Surat -->
        <div class="flex justify-between items-center border-b-2 border-gray-800 pb-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-wide uppercase">LOBBYFLOW &bull; LAPORAN AKTIVITAS TAMU</h1>
                <p class="text-xs text-gray-600 mt-0.5">Sistem Manajemen & Pemantauan Resepsionis Terpadu</p>
            </div>
            <div class="text-right text-xs text-gray-600">
                <p><span class="font-bold">Periode Laporan:</span> {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</p>
                <p><span class="font-bold">Tanggal Cetak:</span> {{ now()->format('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <!-- Executive Summary Mini Box -->
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6 text-xs leading-relaxed">
            <h2 class="font-bold text-indigo-900 uppercase tracking-wider text-[11px] mb-1.5 flex items-center gap-1.5">
                <span>✨</span> RINGKASAN EKSEKUTIF BERBASIS AI
            </h2>
            <p class="text-slate-700 mb-2 font-medium">{{ $aiSummary['headline'] }}</p>
            <p class="text-slate-600">{{ $aiSummary['traffic_analysis'] }} {{ $aiSummary['corporate_pattern'] }}</p>
        </div>

        <!-- Ringkasan Angka / KPI -->
        <div class="grid grid-cols-4 gap-4 mb-6 text-center text-xs">
            <div class="border rounded-lg p-3 bg-gray-50">
                <span class="text-gray-500 block font-medium">Total Kunjungan</span>
                <span class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</span>
            </div>
            <div class="border rounded-lg p-3 bg-gray-50">
                <span class="text-gray-500 block font-medium">Selesai Check-Out</span>
                <span class="text-xl font-bold text-emerald-700">{{ $stats['checked_out'] }}</span>
            </div>
            <div class="border rounded-lg p-3 bg-gray-50">
                <span class="text-gray-500 block font-medium">Di Dalam Gedung</span>
                <span class="text-xl font-bold text-indigo-700">{{ $stats['inside'] }}</span>
            </div>
            <div class="border rounded-lg p-3 bg-gray-50">
                <span class="text-gray-500 block font-medium truncate">Top Mitra</span>
                <span class="text-sm font-bold text-gray-900 truncate block mt-1">{{ $stats['top_instansi'] }}</span>
            </div>
        </div>

        <!-- Tabel Data Tamu -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs mb-8">
            <thead>
                <tr class="bg-gray-100 border-y border-gray-300 font-bold text-gray-700">
                    <th class="py-2.5 px-2">No</th>
                    <th class="py-2.5 px-2">Kode Pass</th>
                    <th class="py-2.5 px-2">Nama Tamu</th>
                    <th class="py-2.5 px-2">Instansi</th>
                    <th class="py-2.5 px-2">Host Tujuan</th>
                    <th class="py-2.5 px-2">Keperluan</th>
                    <th class="py-2.5 px-2">Check-In</th>
                    <th class="py-2.5 px-2">Check-Out</th>
                    <th class="py-2.5 px-2">Durasi</th>
                    <th class="py-2.5 px-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($visitors as $idx => $v)
                    <tr>
                        <td class="py-2 px-2 text-gray-500">{{ $idx + 1 }}</td>
                        <td class="py-2 px-2 font-mono text-[11px]">{{ $v->qr_code ?? ('PASS-' . $v->id) }}</td>
                        <td class="py-2 px-2 font-semibold text-gray-900">{{ $v->name ?? $v->nama_tamu }}</td>
                        <td class="py-2 px-2 text-gray-700">{{ $v->instansi }}</td>
                        <td class="py-2 px-2 text-gray-700">{{ $v->employee?->name ?? $v->host_tujuan ?? '-' }}</td>
                        <td class="py-2 px-2 text-gray-600">{{ $v->purpose ?? $v->keperluan ?? '-' }}</td>
                        <td class="py-2 px-2 text-gray-600">
                            @php $in = $v->waktu_checkin ?? $v->check_in_at; @endphp
                            {{ $in ? \Carbon\Carbon::parse($in)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="py-2 px-2 text-gray-600">
                            @php $out = $v->waktu_checkout ?? $v->check_out_at; @endphp
                            {{ $out ? \Carbon\Carbon::parse($out)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="py-2 px-2 text-gray-700">{{ $v->durasi_kunjungan ?? $v->durasi ?? '-' }}</td>
                        <td class="py-2 px-2 text-center font-medium">
                            {{ $v->status === 'INSIDE' ? 'Di Dalam' : 'Selesai' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-6 text-gray-400">
                            Tidak ada catatan kunjungan tamu untuk periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <!-- Tanda Tangan Pengesahan Laporan -->
        <div class="flex justify-between items-end pt-8 border-t border-gray-200 text-xs text-gray-700">
            <div>
                <p class="font-bold">Dicetak Melalui:</p>
                <p>LobbyFlow Enterprise Visitor System</p>
            </div>
            <div class="text-center pr-8">
                <p class="mb-14">Mengetahui, <br><span class="font-bold">Kepala Bagian Operasional / Keamanan</span></p>
                <p class="border-t border-gray-400 pt-1 font-bold">( .................................................... )</p>
            </div>
        </div>
    </div>
</body>
</html>

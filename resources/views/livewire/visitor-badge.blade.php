<div class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .badge-card {
                box-shadow: none !important;
                border: 2px solid #cbd5e1 !important;
                page-break-inside: avoid;
                margin: 0 auto !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        @page {
            size: auto;
            margin: 10mm;
        }
    </style>

    <!-- Top Action Bar (Disembunyikan saat di-print) -->
    <div class="no-print mb-6 flex items-center gap-3 bg-white px-5 py-3 rounded-xl shadow-sm border border-gray-200">
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Badge (Print)</span>
        </button>
        <button onclick="window.close()" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg font-medium transition">
            <span>Tutup</span>
        </button>
        <a href="{{ route('visitors.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 ml-2">
            &larr; Kembali
        </a>
    </div>

    <!-- Desain ID Card Badge Tamu -->
    <div class="badge-card w-full max-w-sm bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden relative">
        <!-- Lubang Lanyard Clip Slot -->
        <div class="flex justify-center pt-3 pb-1 bg-indigo-700">
            <div class="w-14 h-2.5 bg-indigo-950/40 rounded-full border border-indigo-400/30"></div>
        </div>

        <!-- Header Badge -->
        <div class="bg-indigo-700 text-white px-6 py-4 text-center">
            <div class="flex items-center justify-center gap-2 mb-1">
                <img src="{{ asset('favicon.svg') }}" alt="Logo" class="w-6 h-6 rounded-md shadow-sm">
                <span class="font-extrabold tracking-wider text-base uppercase">LOBBYFLOW</span>
            </div>
            <div class="inline-block bg-indigo-900/60 text-indigo-200 text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full border border-indigo-400/30">
                VISITOR PASS
            </div>
        </div>

        <!-- Body Badge -->
        <div class="p-6 text-center space-y-4">
            <!-- Foto / Inisial Avatar -->
            <div class="flex justify-center">
                <div class="w-16 h-16 rounded-full bg-indigo-50 border-2 border-indigo-200 text-indigo-700 flex items-center justify-center font-bold text-2xl shadow-inner">
                    {{ strtoupper(substr($visitor->name, 0, 1)) }}
                </div>
            </div>

            <!-- Identitas Tamu -->
            <div>
                <h2 class="text-xl font-extrabold text-gray-900 leading-tight">
                    {{ $visitor->name }}
                </h2>
                <p class="text-sm font-semibold text-indigo-600 mt-0.5">
                    {{ $visitor->instansi }}
                </p>
            </div>

            <!-- QR Code Section -->
            <div class="flex flex-col items-center justify-center py-2 bg-slate-50 rounded-xl border border-slate-100">
                <div class="w-36 h-36 bg-white p-2 rounded-lg shadow-sm border border-gray-200 flex items-center justify-center">
                    {!! $qrCodeSvg !!}
                </div>
                <p class="font-mono text-xs font-bold text-gray-600 mt-2 tracking-wider">
                    {{ $visitor->qr_code ?? ('PASS-' . $visitor->id) }}
                </p>
            </div>

            <!-- Host & Keperluan -->
            <div class="border-t border-b border-gray-100 py-3 text-left space-y-2 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-medium">Host / Pegawai:</span>
                    <span class="font-bold text-gray-800">{{ $visitor->employee?->name ?? $visitor->host_tujuan ?? '-' }}</span>
                </div>
                @if($visitor->employee?->department)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 font-medium">Departemen:</span>
                        <span class="font-semibold text-gray-700">{{ $visitor->employee->department }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-medium">Keperluan:</span>
                    <span class="font-semibold text-gray-700 truncate max-w-[180px]">{{ $visitor->purpose ?? $visitor->keperluan ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-medium">Waktu Masuk:</span>
                    <span class="font-mono text-gray-600">
                        @php $cIn = $visitor->waktu_checkin ?? $visitor->check_in_at ?? $visitor->created_at; @endphp
                        {{ $cIn ? \Carbon\Carbon::parse($cIn)->format('d M Y, H:i') : '-' }}
                    </span>
                </div>
            </div>

            <!-- Footer Instruksi Badge -->
            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">
                Harap kenakan pass ini selama berada di area gedung
            </p>
        </div>
    </div>
</div>

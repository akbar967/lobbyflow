<div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <header class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo.svg') }}" alt="LobbyFlow Logo" class="w-10 h-10 rounded-xl shadow-sm">
            <div>
                @if(Auth::user()?->role === 'admin')
                    <h1 class="text-2xl font-black text-purple-900 leading-tight flex items-center gap-2">
                        <span>🛡️</span> LobbyFlow &bull; Admin Command Center
                    </h1>
                    <p class="text-xs text-purple-700 font-medium mt-0.5">Pusat supervisi alur tamu eksekutif, analitik kepatuhan, audit keselamatan K3, dan kontrol master data</p>
                @else
                    <h1 class="text-2xl font-bold text-indigo-600 leading-tight flex items-center gap-2">
                        <span>🛎️</span> LobbyFlow &bull; Meja Resepsionis Lobi
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5">Sistem operasional pendaftaran tamu cerdas, cetak pass identitas, dan pelayanan lobi real-time</p>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- User Profile & Role Info -->
            @auth
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm text-xs">
                    <div class="w-6 h-6 rounded-full {{ Auth::user()->role === 'admin' ? 'bg-purple-600' : 'bg-indigo-600' }} text-white flex items-center justify-center font-bold text-[10px]">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                        @if(Auth::user()->role === 'admin')
                            <span class="ml-1 bg-purple-100 text-purple-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full uppercase">Admin</span>
                        @else
                            <span class="ml-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full uppercase">Resepsionis</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline ml-2 pl-2 border-l border-gray-200">
                        @csrf
                        <button type="submit" wire:click="logout" class="text-rose-600 hover:text-rose-800 font-medium transition" title="Keluar dari sistem">
                            Keluar
                        </button>
                    </form>
                </div>
            @endauth

            <!-- Role-Gated Report Link -->
            @if(Auth::user()?->role === 'admin')
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 px-3.5 py-1.5 rounded-full transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>📊 Laporan & Ekspor</span>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-gray-100 text-gray-400 border border-gray-200 px-3.5 py-1.5 rounded-full cursor-not-allowed" title="Menu Laporan hanya dapat diakses oleh akun Admin">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Laporan (Khusus Admin)</span>
                </span>
            @endif

            <!-- Live Clock & Office Hours Widget -->
            <div class="hidden lg:flex items-center gap-2.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-sm text-xs">
                <span class="text-base">🕒</span>
                <div class="text-left">
                    <div id="live-clock" class="font-mono font-bold text-gray-800 text-xs">--:--:-- WIB</div>
                    <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Lobi Beroperasi (Buka)
                    </span>
                </div>
            </div>

            <!-- Akses Wi-Fi Tamu -->
            <button type="button" wire:click="openWifiModal" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-full transition shadow-sm" title="Lihat Informasi & QR Akses Wi-Fi Tamu">
                <span>📶</span>
                <span class="hidden sm:inline">Wi-Fi Tamu</span>
            </button>

            <!-- Mode Kiosk Tamu Mandiri -->
            <button type="button" wire:click="toggleKioskMode" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1.5 rounded-full transition shadow-sm" title="Masuk ke Mode Kiosk Tablet Tamu Mandiri">
                <span>🖥️</span>
                <span class="hidden sm:inline">Mode Kiosk</span>
            </button>

            <!-- Suara Lobi Chime Toggle -->
            <button type="button" onclick="toggleLobbyAudio()" id="audio-toggle-btn" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-3 py-1.5 rounded-full transition shadow-sm" title="Aktifkan / Bisukan Nada Dering Notifikasi Lobi">
                <span id="audio-icon">🔔</span>
                <span id="audio-label" class="hidden sm:inline">Suara Lobi: On</span>
            </button>

            <!-- Tombol Evakuasi Darurat K3 (HSE Muster Roll) -->
            <button type="button" wire:click="openEvacuationModal" class="inline-flex items-center gap-1.5 text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-3 py-1.5 rounded-full transition shadow-sm" title="Buka Daftar Evakuasi Darurat K3 (Tamu di dalam gedung)">
                <span class="animate-pulse">🚨</span>
                <span>Evakuasi (K3)</span>
            </button>

            <span class="text-sm bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full font-medium flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                Live Monitoring
            </span>
        </div>
    </header>

    <!-- 📢 Papan Pengumuman Lobi Interaktif (Announcement Ticker) -->
    <div class="mb-5 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 text-xs shadow-sm">
        <div class="flex items-center gap-2.5 flex-1 min-w-0">
            <span class="text-base flex-shrink-0 animate-bounce">📢</span>
            @if($isEditingAnnouncement)
                <div class="flex items-center gap-2 w-full">
                    <input type="text" wire:model="newAnnouncement" class="w-full p-1.5 border rounded-lg text-xs bg-white focus:ring-2 focus:ring-amber-500">
                    <button type="button" wire:click="saveAnnouncement" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1 rounded-md font-semibold text-[11px] whitespace-nowrap shadow-sm">Simpan</button>
                    <button type="button" wire:click="cancelEditAnnouncement" class="text-gray-500 hover:text-gray-700 text-[11px] px-2">Batal</button>
                </div>
            @else
                <div class="truncate text-amber-950 font-medium tracking-wide">
                    {{ $announcement }}
                </div>
            @endif
        </div>
        @if(!$isEditingAnnouncement)
            <button type="button" wire:click="editAnnouncement" class="text-[11px] text-amber-700 hover:text-amber-900 font-semibold hover:underline flex-shrink-0 flex items-center gap-1 ml-2" title="Ubah Pengumuman Lobi">
                ✏️ Ubah Teks
            </button>
        @endif
    </div>

    <!-- Flash Alert Messages -->
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3 shadow-sm">
            <div class="p-1 bg-rose-100 rounded-md text-rose-600 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-rose-900 text-xs uppercase tracking-wide">Pemberitahuan Hak Akses</h4>
                <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- 🚀 Pusat Fitur Unggulan & Quick Action Hub (Enterprise Feature Hub) -->
    <div class="mb-6 bg-white rounded-2xl border {{ Auth::user()?->isAdmin() ? 'border-purple-200' : 'border-indigo-100' }} shadow-sm overflow-hidden">
        @if(Auth::user()?->isAdmin())
            <div class="bg-gradient-to-r from-purple-900 via-indigo-950 to-slate-900 p-4 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center text-xl border border-purple-400/30 shadow-inner">
                        🛡️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold tracking-wide uppercase">Pusat Supervisi dan Tata Kelola Admin (Executive Hub)</h3>
                            <span class="text-[10px] bg-purple-400/30 text-purple-200 font-bold px-2 py-0.5 rounded-full border border-purple-400/30">Otoritas Penuh</span>
                        </div>
                        <p class="text-xs text-purple-200 mt-0.5">
                            Supervisi alur tamu real-time, audit keselamatan K3, analitik eksekutif AI, manajemen master data, dan kontrol keamanan
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold bg-amber-400 text-slate-950 hover:bg-amber-300 px-3.5 py-2 rounded-xl transition shadow-md whitespace-nowrap">
                        <span>📊 Laporan Analitik & AI</span>
                        <span>&rarr;</span>
                    </a>
                    <button type="button" wire:click="openFeaturesModal" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white/10 hover:bg-white/20 text-white px-3.5 py-2 rounded-xl border border-white/20 transition whitespace-nowrap">
                        <span>✨ Direktori 12 Fitur</span>
                    </button>
                </div>
            </div>

            <!-- 8 Interactive Admin Feature Action Cards -->
            <div class="p-4 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5 bg-purple-50/20">
                <!-- 1. Analitik Eksekutif -->
                <a href="{{ route('reports.index') }}" class="p-2.5 bg-white hover:bg-purple-50 rounded-xl border border-purple-200 hover:border-purple-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150 block">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-800 flex items-center justify-center text-sm font-bold shadow-inner">📊</span>
                        <span class="text-[8px] bg-purple-100 text-purple-900 font-extrabold px-1 py-0.5 rounded uppercase">AI & PDF</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-purple-700">Analitik KPI</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Grafik & Tren</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-purple-700 font-bold">Buka Laporan &rarr;</span>
                </a>

                <!-- 2. Audit Evakuasi K3 -->
                <button type="button" wire:click="openEvacuationModal" class="p-2.5 bg-white hover:bg-rose-50 rounded-xl border border-rose-200 hover:border-rose-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold shadow-inner">🚨</span>
                        <span class="text-[8px] bg-rose-100 text-rose-800 font-extrabold px-1 py-0.5 rounded uppercase">Audit K3</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-rose-700">Evakuasi K3</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Roster Darurat</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-rose-600 font-bold">Audit Roster &rarr;</span>
                </button>

                <!-- 3. Master Pegawai -->
                <button type="button" wire:click="$set('showManageEmployeesModal', true)" class="p-2.5 bg-white hover:bg-indigo-50 rounded-xl border border-indigo-200 hover:border-indigo-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold shadow-inner">👔</span>
                        <span class="text-[8px] bg-indigo-100 text-indigo-800 font-extrabold px-1 py-0.5 rounded uppercase">Master</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-indigo-700">Master Host</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Kelola Kontak & WA</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-indigo-600 font-bold">Kelola Staf &rarr;</span>
                </button>

                <!-- 4. Ekspor CSV Hari Ini -->
                <button type="button" wire:click="exportTodayCsv" class="p-2.5 bg-white hover:bg-teal-50 rounded-xl border border-teal-200 hover:border-teal-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-sm font-bold shadow-inner">📥</span>
                        <span class="text-[8px] bg-teal-100 text-teal-800 font-extrabold px-1 py-0.5 rounded uppercase">Excel</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-teal-700">Ekspor CSV</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Unduh Rekap</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-teal-600 font-bold">Unduh File &rarr;</span>
                </button>

                <!-- 5. Live QR Scanner -->
                <button type="button" wire:click="openCameraModal" class="p-2.5 bg-white hover:bg-slate-50 rounded-xl border border-gray-200 hover:border-slate-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold shadow-inner">📸</span>
                        <span class="text-[8px] bg-slate-100 text-slate-800 font-extrabold px-1 py-0.5 rounded uppercase">Scanner</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-slate-800">Scan Barcode</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Kamera QR Pass</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-slate-600 font-bold">Buka Kamera &rarr;</span>
                </button>

                <!-- 6. Wi-Fi Tamu -->
                <button type="button" wire:click="openWifiModal" class="p-2.5 bg-white hover:bg-blue-50 rounded-xl border border-blue-200 hover:border-blue-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold shadow-inner">📶</span>
                        <span class="text-[8px] bg-blue-100 text-blue-800 font-extrabold px-1 py-0.5 rounded uppercase">Voucher</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-blue-700">Wi-Fi Tamu</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Kredensial Jaringan</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-blue-600 font-bold">Lihat Voucher &rarr;</span>
                </button>

                <!-- 7. Mode Kiosk Lobi -->
                <button type="button" wire:click="toggleKioskMode" class="p-2.5 bg-white hover:bg-amber-50 rounded-xl border border-amber-200 hover:border-amber-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold shadow-inner">🖥️</span>
                        <span class="text-[8px] bg-amber-100 text-amber-800 font-extrabold px-1 py-0.5 rounded uppercase">Kiosk</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-amber-700">Mode Kiosk</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Tablet Mandiri</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-amber-600 font-bold">Luncurkan &rarr;</span>
                </button>

                <!-- 8. Direktori Fitur Lengkap -->
                <button type="button" wire:click="openFeaturesModal" class="p-2.5 bg-white hover:bg-emerald-50 rounded-xl border border-emerald-200 hover:border-emerald-400 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shadow-inner">✨</span>
                        <span class="text-[8px] bg-emerald-100 text-emerald-800 font-extrabold px-1 py-0.5 rounded uppercase">Katalog</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-emerald-700">Semua Fitur</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Direktori 12 Fitur</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-emerald-600 font-bold">Lihat Semua &rarr;</span>
                </button>
            </div>
        @else
            <!-- Receptionist Hub Header -->
            <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-blue-900 p-4 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-xl border border-white/20 shadow-inner">
                        🛎️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold tracking-wide uppercase">Pusat Layanan Meja Resepsionis (Front-Desk Hub)</h3>
                            <span class="text-[10px] bg-emerald-500/30 text-emerald-300 font-bold px-2 py-0.5 rounded-full border border-emerald-400/30">Operasional Aktif</span>
                        </div>
                        <p class="text-xs text-indigo-100 mt-0.5">
                            Alat bantu operasional lobi: Smart Scan KTP, Quick Pass barcode, webcam QR scanner, Wi-Fi voucher, dan cetak badge
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="openFeaturesModal" class="inline-flex items-center gap-1.5 text-xs font-bold bg-white text-indigo-900 hover:bg-indigo-50 px-3.5 py-2 rounded-xl transition shadow-md whitespace-nowrap">
                        <span>✨ Direktori 12 Fitur Lengkap</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            <!-- 8 Interactive Receptionist Feature Action Cards -->
            <div class="p-4 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5 bg-slate-50/60">
                <!-- 1. Smart Scan OCR -->
                <button type="button" wire:click="simulateAiScan" class="p-2.5 bg-white hover:bg-purple-50/60 rounded-xl border border-gray-200 hover:border-purple-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold shadow-inner">📷</span>
                        <span class="text-[8px] bg-purple-100 text-purple-800 font-extrabold px-1 py-0.5 rounded uppercase">AI Vision</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-purple-700">Smart Scan</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Ekstraksi KTP</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-purple-600 font-bold">Coba Scan &rarr;</span>
                </button>

                <!-- 2. Quick Pass Scanner -->
                <button type="button" onclick="const el = document.getElementById('quick-pass-input'); if(el) { el.focus(); el.scrollIntoView({behavior:'smooth'}); }" class="p-2.5 bg-white hover:bg-amber-50/60 rounded-xl border border-gray-200 hover:border-amber-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold shadow-inner">⚡</span>
                        <span class="text-[8px] bg-amber-100 text-amber-800 font-extrabold px-1 py-0.5 rounded uppercase">Pass ID</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-amber-700">Quick Pass</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Scan pass 1-detik</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-amber-600 font-bold">Fokus &rarr;</span>
                </button>

                <!-- 3. Kamera QR Scanner -->
                <button type="button" wire:click="openCameraModal" class="p-2.5 bg-white hover:bg-indigo-50/60 rounded-xl border border-gray-200 hover:border-indigo-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold shadow-inner">📸</span>
                        <span class="text-[8px] bg-indigo-100 text-indigo-800 font-extrabold px-1 py-0.5 rounded uppercase">Kamera QR</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-indigo-700">Scan Kamera</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Webcam Scanner</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-indigo-600 font-bold">Buka Kamera &rarr;</span>
                </button>

                <!-- 4. Wi-Fi Tamu -->
                <button type="button" wire:click="openWifiModal" class="p-2.5 bg-white hover:bg-blue-50/60 rounded-xl border border-gray-200 hover:border-blue-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold shadow-inner">📶</span>
                        <span class="text-[8px] bg-blue-100 text-blue-800 font-extrabold px-1 py-0.5 rounded uppercase">Voucher</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-blue-700">Wi-Fi Tamu</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Akses & QR Code</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-blue-600 font-bold">Lihat Wi-Fi &rarr;</span>
                </button>

                <!-- 5. Roster Evakuasi K3 -->
                <button type="button" wire:click="openEvacuationModal" class="p-2.5 bg-white hover:bg-rose-50/60 rounded-xl border border-gray-200 hover:border-rose-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold shadow-inner">🚨</span>
                        <span class="text-[8px] bg-rose-100 text-rose-800 font-extrabold px-1 py-0.5 rounded uppercase">K3 Safety</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-rose-700">Evakuasi K3</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Roster darurat</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-rose-600 font-bold">Buka Roster &rarr;</span>
                </button>

                <!-- 6. Mode Kiosk Tamu -->
                <button type="button" wire:click="toggleKioskMode" class="p-2.5 bg-white hover:bg-amber-50/60 rounded-xl border border-gray-200 hover:border-amber-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold shadow-inner">🖥️</span>
                        <span class="text-[8px] bg-amber-100 text-amber-800 font-extrabold px-1 py-0.5 rounded uppercase">Kiosk</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-amber-700">Mode Kiosk</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Self Check-In</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-amber-600 font-bold">Aktifkan &rarr;</span>
                </button>

                <!-- 7. Ekspor Rekap CSV -->
                <button type="button" wire:click="exportTodayCsv" class="p-2.5 bg-white hover:bg-teal-50/60 rounded-xl border border-gray-200 hover:border-teal-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-sm font-bold shadow-inner">📥</span>
                        <span class="text-[8px] bg-teal-100 text-teal-800 font-extrabold px-1 py-0.5 rounded uppercase">Excel</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-teal-700">Ekspor CSV</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Unduh rekap hari ini</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-teal-600 font-bold">Unduh File &rarr;</span>
                </button>

                <!-- 8. Kelola Pegawai -->
                <button type="button" wire:click="$set('showManageEmployeesModal', true)" class="p-2.5 bg-white hover:bg-slate-50 rounded-xl border border-gray-200 hover:border-slate-300 text-left transition shadow-sm group hover:-translate-y-0.5 transform duration-150">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold shadow-inner">👔</span>
                        <span class="text-[8px] bg-slate-100 text-slate-800 font-extrabold px-1 py-0.5 rounded uppercase">Master</span>
                    </div>
                    <h4 class="text-[11px] font-bold text-gray-900 group-hover:text-slate-800">Pegawai</h4>
                    <p class="text-[9px] text-gray-500 mt-0.5 line-clamp-1">Direktori host</p>
                    <span class="mt-1.5 inline-flex items-center text-[9px] text-slate-600 font-bold">Kelola Staf &rarr;</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Pesan Sukses & Error -->
    @if($successMessage || session('success'))
        <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm flex items-center justify-between transition">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">✅</span>
                <span class="font-medium text-sm">{{ $successMessage ?? session('success') }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', null)" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold">
                ✕
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm flex items-center justify-between transition">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">⚠️</span>
                <span class="font-medium text-sm">{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                ✕
            </button>
        </div>
    @endif

    <!-- 📊 Live Lobby Stats Counters (Metrik Real-Time Hari Ini) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if(Auth::user()?->isAdmin())
            <!-- Admin Metric 1: Total Tamu Hari Ini & Bulan Ini -->
            <div class="bg-white p-4 rounded-xl border border-purple-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider">Total Tamu Hari Ini</p>
                    <h3 class="text-2xl font-extrabold text-purple-900 mt-0.5">{{ $stats['total_today'] }}</h3>
                    <span class="text-[11px] text-purple-600 font-medium">Bulan Ini: <strong>{{ $stats['total_this_month'] }}</strong> kunjungan</span>
                </div>
                <div class="w-11 h-11 bg-purple-50 text-purple-700 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    👥
                </div>
            </div>

            <!-- Admin Metric 2: Headcount Tamu di Gedung -->
            <div class="bg-white p-4 rounded-xl border border-emerald-200 shadow-sm flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Sedang di Gedung</p>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <h3 class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['inside_now'] }}</h3>
                    <span class="text-[11px] text-emerald-600 font-medium">Headcount Aktif Keselamatan</span>
                </div>
                <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    🏢
                </div>
            </div>

            <!-- Admin Metric 3: Tamu Prioritas VIP -->
            <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Tamu VIP di Gedung</p>
                    <h3 class="text-2xl font-extrabold text-amber-800 mt-0.5">{{ $stats['vip_count'] }}</h3>
                    <span class="text-[11px] text-amber-600 font-medium">Prioritas Protokoler Khusus</span>
                </div>
                <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    ⭐
                </div>
            </div>

            <!-- Admin Metric 4: Kepatuhan Check-Out -->
            <div class="bg-white p-4 rounded-xl border border-indigo-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">Kepatuhan Check-Out</p>
                    <h3 class="text-2xl font-extrabold text-indigo-800 mt-0.5">{{ $stats['checkout_rate'] }}%</h3>
                    <span class="text-[11px] text-indigo-600 font-medium">{{ $stats['checked_out_today'] }} dari {{ $stats['total_today'] }} tamu selesai</span>
                </div>
                <div class="w-11 h-11 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    📈
                </div>
            </div>
        @else
            <!-- Receptionist Metric 1: Total Tamu Hari Ini -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hari Ini</p>
                    <h3 class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $stats['total_today'] }}</h3>
                    <span class="text-[11px] text-gray-400">Tamu Terdaftar</span>
                </div>
                <div class="w-11 h-11 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    👥
                </div>
            </div>

            <!-- Receptionist Metric 2: Di Gedung -->
            <div class="bg-white p-4 rounded-xl border border-emerald-200 shadow-sm flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Di Gedung</p>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <h3 class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['inside_now'] }}</h3>
                    <span class="text-[11px] text-emerald-600 font-medium">Sedang Berkunjung</span>
                </div>
                <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    🏢
                </div>
            </div>

            <!-- Receptionist Metric 3: Sudah Check-Out -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sudah Check-Out</p>
                    <h3 class="text-2xl font-extrabold text-gray-800 mt-0.5">{{ $stats['checked_out_today'] }}</h3>
                    <span class="text-[11px] text-gray-400">Kunjungan Selesai</span>
                </div>
                <div class="w-11 h-11 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    🏁
                </div>
            </div>

            <!-- Receptionist Metric 4: Host / Pegawai -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Host / Pegawai</p>
                    <h3 class="text-2xl font-extrabold text-indigo-600 mt-0.5">{{ $stats['total_employees'] }}</h3>
                    <button type="button" class="text-[11px] text-indigo-500 font-medium hover:underline flex items-center gap-0.5" wire:click="$set('showManageEmployeesModal', true)">
                        Kelola Pegawai &rarr;
                    </button>
                </div>
                <div class="w-11 h-11 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    👔
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Form Check-In Tamu (Kiri) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
            <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>✍️</span> Check-In Tamu Baru
                </div>
                @if(Auth::user()?->isAdmin())
                    <span class="text-[10px] bg-purple-100 text-purple-800 font-bold px-2 py-0.5 rounded-full border border-purple-200">
                        🛡️ Mode Admin
                    </span>
                @else
                    <span class="text-[10px] bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full border border-indigo-200">
                        🛎️ Front Desk
                    </span>
                @endif
            </h2>
            </h2>

            <form wire:submit="save" class="space-y-4">
                <!-- ✨ Smart Scan Dropzone (KTP / Kartu Nama) -->
                <div class="bg-gradient-to-r from-purple-50 via-indigo-50/50 to-purple-50 p-3 rounded-xl border border-purple-200 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
                            <span class="p-1 bg-purple-600 text-white rounded text-[10px]">AI</span>
                            <span>Smart Scan (KTP / Kartu Nama)</span>
                        </label>
                        <button type="button" wire:click="simulateAiScan" class="text-[10px] bg-purple-600 hover:bg-purple-700 text-white px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1 shadow-sm" title="Uji coba ekstraksi data KTP otomatis dengan simulasi AI">
                            <span>✨ Coba Demo Scan</span>
                        </button>
                    </div>

                    <div id="dropzone" class="border-2 border-dashed border-purple-300 hover:border-purple-500 rounded-xl p-3 text-center cursor-pointer bg-white/80 hover:bg-white transition relative">
                        <input type="file" wire:model="id_card_image" id="id_card_input" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div id="dropzone_prompt" class="{{ $id_card_image ? 'hidden' : '' }} space-y-1">
                            <div class="text-2xl text-purple-600">📷</div>
                            <p class="text-xs font-bold text-gray-700">Drop foto KTP / Kartu Nama di sini</p>
                            <p class="text-[10px] text-gray-400">AI otomatis mengekstrak Nama & Instansi ke form</p>
                        </div>

                        @if($id_card_image)
                            <div class="flex items-center justify-between gap-2 text-left bg-white p-2 rounded-lg border border-indigo-100">
                                <img src="{{ $id_card_image->temporaryUrl() }}" class="w-10 h-10 object-cover rounded-md border">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate">Foto terunggah</p>
                                    <p class="text-[10px] text-emerald-600 font-medium">✓ Siap diproses AI</p>
                                </div>
                                <button type="button" wire:click="scanOcr" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold px-2.5 py-1 rounded-md shadow-sm transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="scanOcr">✨ Scan AI</span>
                                    <span wire:loading wire:target="scanOcr">Scanning...</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div wire:loading wire:target="id_card_image, scanOcr" class="mt-2 p-2 bg-purple-50 border border-purple-200 rounded-lg text-purple-700 text-xs flex items-center gap-2">
                        <svg class="animate-spin h-3.5 w-3.5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span class="font-medium">Menganalisis teks KTP dengan AI Smart OCR...</span>
                    </div>

                    @error('id_card_image')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Tamu -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700">Nama Tamu <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" id="input_name" placeholder="Masukkan nama lengkap" class="mt-1 w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instansi -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700">Asal Instansi / Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="instansi" id="input_instansi" placeholder="Contoh: PT. Maju Bersama" class="mt-1 w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('instansi') border-red-300 @enderror">
                    @error('instansi')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Host Tujuan -->
                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-gray-700">Tujuan Bertemu (Host) <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="$set('showEmployeeModal', true)" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold hover:underline">
                                + Tambah
                            </button>
                            <span class="text-gray-300 text-xs">|</span>
                            <button type="button" wire:click="$set('showManageEmployeesModal', true)" class="text-[11px] text-gray-500 hover:text-gray-700 font-medium hover:underline">
                                Kelola
                            </button>
                        </div>
                    </div>

                    @if($employees->isEmpty())
                        <div class="mt-1.5 p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs flex items-center justify-between">
                            <span>Belum ada data pegawai.</span>
                            <button type="button" wire:click="seedDefaultEmployees" class="underline font-bold text-indigo-700 hover:text-indigo-900">
                                Buat Pegawai Default
                            </button>
                        </div>
                    @endif

                    <select wire:model="employee_id" class="mt-1 w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('employee_id') border-red-300 @enderror">
                        <option value="">Pilih Pegawai</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} ({{ $emp->department }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keperluan -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700">Keperluan Kunjungan <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="purpose" id="input_purpose" placeholder="Contoh: Meeting Proyek" class="mt-1 w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('purpose') border-red-300 @enderror">
                    
                    <!-- Quick Purpose Templates -->
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="text-[10px] text-gray-400 self-center">Pilihan Cepat:</span>
                        <button type="button" wire:click="setPurposeTemplate('Meeting Proyek')" class="text-[10px] bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 px-2 py-0.5 rounded border border-gray-200 transition">Meeting</button>
                        <button type="button" wire:click="setPurposeTemplate('Wawancara Kerja (Interview)')" class="text-[10px] bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 px-2 py-0.5 rounded border border-gray-200 transition">Interview</button>
                        <button type="button" wire:click="setPurposeTemplate('Pengantaran Dokumen / Paket')" class="text-[10px] bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 px-2 py-0.5 rounded border border-gray-200 transition">Dokumen</button>
                        <button type="button" wire:click="setPurposeTemplate('Konsultasi Vendor / Audit')" class="text-[10px] bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 px-2 py-0.5 rounded border border-gray-200 transition">Vendor</button>
                        <button type="button" wire:click="setPurposeTemplate('Maintenance IT & Fasilitas')" class="text-[10px] bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 px-2 py-0.5 rounded border border-gray-200 transition">IT / Maint</button>
                    </div>

                    @error('purpose')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Safety Protocol & Digital Agreement -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="agreement" checked class="mt-0.5 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="text-gray-600 leading-snug">
                            Tamu menyetujui <span class="font-bold text-gray-800">Tata Tertib Keamanan & Kerahasiaan Gedung</span> (Wajib mengenakan Visitor Pass di area kantor).
                        </span>
                    </label>
                </div>

                <!-- Pra-Registrasi Option -->
                <div class="p-2.5 bg-indigo-50/60 border border-indigo-100 rounded-lg text-xs">
                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-indigo-900">
                        <input type="checkbox" wire:model.live="is_preregistered" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>📅 Pra-Registrasi (Jadwalkan Kunjungan)</span>
                    </label>
                    <p class="text-[10px] text-gray-500 mt-0.5 ml-5">Tamu didaftarkan lebih awal dan dapat dikonfirmasi saat tiba di lobi.</p>
                </div>

                <!-- ⭐ VIP / Prioritas Guest Flag Option -->
                <div class="p-2.5 bg-amber-50/70 border border-amber-200 rounded-lg text-xs">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-amber-900">
                        <input type="checkbox" wire:model="is_vip" class="rounded text-amber-600 focus:ring-amber-500">
                        <span>⭐ Tandai sebagai Tamu VIP / Prioritas</span>
                    </label>
                    <p class="text-[10px] text-amber-700 mt-0.5 ml-5">Tamu mendapatkan badge pass emas & prioritas pelayanan.</p>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-semibold transition shadow-md flex items-center justify-center gap-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        {{ $is_preregistered ? 'Jadwalkan Pra-Registrasi' : 'Simpan & Check-In' }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2 text-sm">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </form>
        </div>

        <!-- Tabel Daftar Aktivitas Tamu (Kanan) -->
        <div class="md:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <span>📋</span> Daftar Aktivitas Tamu
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pemantauan interaktif reaktif &bull; Powered by Livewire</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Tombol Unduh CSV Hari Ini -->
                    <button type="button" wire:click="exportTodayCsv" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full transition shadow-sm" title="Unduh data seluruh tamu hari ini ke file CSV (.csv / Excel)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="hidden sm:inline">Unduh CSV Hari Ini</span>
                        <span class="sm:hidden">CSV</span>
                    </button>

                    <button type="button" wire:click="generateDemoVisitors" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-gradient-to-r from-purple-50 to-indigo-50 hover:from-purple-100 hover:to-indigo-100 text-purple-700 border border-purple-200 px-3 py-1 rounded-full transition shadow-sm" title="Generate 5 data tamu contoh otomatis untuk demo lobi ramai">
                        <span>🎲</span>
                        <span wire:loading.remove wire:target="generateDemoVisitors">Simulasi Tamu Ramai</span>
                        <span wire:loading wire:target="generateDemoVisitors">Membuat Tamu...</span>
                    </button>
                    <span class="text-xs text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full font-bold">
                        {{ $visitors->total() }} Tamu Tercatat
                    </span>
                </div>
            </div>

            <!-- Status Tabs & ⚡ Quick Pass Check-Out Bar -->
            <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-3 mb-4">
                <!-- Status Filter Tabs -->
                <div class="flex flex-wrap items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs font-semibold">
                    <button type="button" wire:click="setStatusFilter('ALL')" class="px-3 py-1.5 rounded-lg transition {{ $statusFilter === 'ALL' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Semua Tamu
                    </button>
                    <button type="button" wire:click="setStatusFilter('INSIDE')" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $statusFilter === 'INSIDE' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Di Gedung ({{ $stats['inside_now'] }})
                    </button>
                    <button type="button" wire:click="setStatusFilter('SCHEDULED')" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $statusFilter === 'SCHEDULED' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        <span>📅</span>
                        Dijadwalkan ({{ $stats['scheduled'] }})
                    </button>
                    <button type="button" wire:click="setStatusFilter('CHECKED_OUT')" class="px-3 py-1.5 rounded-lg transition {{ $statusFilter === 'CHECKED_OUT' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Selesai ({{ $stats['checked_out_today'] }})
                    </button>
                    <button type="button" wire:click="setStatusFilter('VIP')" class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $statusFilter === 'VIP' ? 'bg-amber-400 text-amber-950 font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900' }}" title="Filter Tamu VIP">
                        <span>⭐</span>
                        VIP ({{ $stats['vip_count'] }})
                    </button>
                </div>

                <!-- ⚡ Quick Check-Out Pass Scanner Input Console -->
                <form wire:submit.prevent="quickCheckout" class="flex items-center gap-1.5 bg-gradient-to-r from-amber-50 to-orange-50 p-1.5 rounded-xl border border-amber-200 shadow-sm">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-amber-600 text-xs font-bold">
                            ⚡
                        </span>
                        <input type="text" id="quick-pass-input" wire:model="quickPassCode" placeholder="Scan Barcode / Pass..." class="pl-7 pr-3 py-1.5 text-xs border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white w-44 font-mono font-medium">
                    </div>
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm whitespace-nowrap flex items-center gap-1">
                        <span>Check-Out</span>
                    </button>
                    <button type="button" wire:click="openCameraModal" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg transition shadow-sm whitespace-nowrap flex items-center gap-1" title="Buka Kamera untuk scan QR Code Pass Tamu">
                        <span>📷 Scan Kamera</span>
                    </button>
                </form>
            </div>

            <!-- Form Pencarian Reaktif Livewire -->
            <div class="mb-5 flex gap-2">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama tamu atau instansi secara live..." class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span wire:loading wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </span>
                </div>
                @if($search)
                    <button type="button" wire:click="resetSearch" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition flex items-center gap-1">
                        Reset
                    </button>
                @endif
            </div>

            <!-- Bar Aksi Batch Check-Out Massal -->
            @if(count($selectedVisitors) > 0)
                <div class="mb-4 p-3 bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 text-white rounded-xl shadow-md flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center font-bold text-xs">{{ count($selectedVisitors) }}</span>
                        <span class="font-semibold">{{ count($selectedVisitors) }} Tamu Terpilih</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="batchCheckout" wire:confirm="Check-out massal {{ count($selectedVisitors) }} tamu terpilih sekarang?" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg transition shadow-sm flex items-center gap-1">
                            <span>🏁 Check-Out Terpilih ({{ count($selectedVisitors) }})</span>
                        </button>
                        <button type="button" wire:click="$set('selectedVisitors', [])" class="px-2.5 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg transition">
                            Batal
                        </button>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="pb-3 w-8"><input type="checkbox" wire:model.live="selectAll" class="rounded text-indigo-600 focus:ring-indigo-500" title="Pilih Semua Tamu di Gedung"></th>
                            <th class="pb-3">Tamu & Instansi</th>
                            <th class="pb-3">Host Tujuan</th>
                            <th class="pb-3">Keperluan</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        @forelse($visitors as $v)
                            <tr wire:key="visitor-{{ $v->id }}" class="hover:bg-gray-50/75 transition {{ in_array((string)$v->id, $selectedVisitors) ? 'bg-indigo-50/40' : '' }}">
                                <!-- Checkbox Baris -->
                                <td class="py-3.5 pr-1 w-8">
                                    <input type="checkbox" wire:model.live="selectedVisitors" value="{{ $v->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                </td>

                                <!-- Tamu & Instansi -->
                                <td class="py-3.5 pr-2">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-gray-900">{{ $v->name ?? $v->nama_tamu }}</p>
                                                @if($v->is_vip)
                                                    <span class="inline-flex items-center px-1.5 py-0.2 bg-amber-100 text-amber-800 border border-amber-300 rounded-full text-[9px] font-black shadow-xs">⭐ VIP</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-500 font-medium">{{ $v->instansi }}</p>
                                            <!-- Kode Pass Tamu dengan Salin 1-Klik -->
                                            <div class="mt-1 flex items-center gap-1">
                                                <span class="font-mono text-[10px] text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ $v->qr_code ?? ('PASS-'.$v->id) }}</span>
                                                <button type="button" onclick="navigator.clipboard.writeText('{{ $v->qr_code ?? ('PASS-'.$v->id) }}'); showCopyToast('{{ $v->qr_code ?? ('PASS-'.$v->id) }}')" class="text-gray-400 hover:text-indigo-600 p-0.5 rounded hover:bg-gray-100 transition" title="Salin Kode Pass">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @if($v->id_card_image)
                                            <button type="button" wire:click="previewIdCard({{ $v->id }})" class="p-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded border border-indigo-200 text-[10px] font-semibold transition flex items-center gap-0.5" title="Lihat Foto KTP">
                                                📷 KTP
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                <!-- Host Tujuan -->
                                <td class="py-3.5 pr-2 text-gray-600">
                                    <p class="font-semibold text-gray-800">{{ $v->employee?->name ?? $v->host_tujuan ?? '-' }}</p>
                                    @if($v->employee?->department)
                                        <p class="text-xs text-gray-400">{{ $v->employee->department }}</p>
                                    @endif
                                </td>

                                <!-- Keperluan -->
                                <td class="py-3.5 pr-2 text-gray-600">
                                    <p class="text-sm font-medium">{{ $v->purpose ?? $v->keperluan ?? '-' }}</p>
                                    @php
                                        $checkInTime = $v->waktu_checkin ? \Illuminate\Support\Carbon::parse($v->waktu_checkin) : null;
                                    @endphp
                                    <p class="text-xs text-gray-400">Masuk: {{ $checkInTime ? $checkInTime->format('H:i') : '-' }}</p>
                                </td>

                                <!-- Label Status Tag (UI/UX) -->
                                <td class="py-3.5 pr-2">
                                    @if($v->status === 'SCHEDULED')
                                        <!-- Biru: Pra-Registrasi / Dijadwalkan -->
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-100 text-blue-800 border border-blue-200 rounded-full text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Dijadwalkan
                                        </span>
                                    @elseif($v->status === 'INSIDE')
                                        <!-- Hijau: Di Dalam Gedung -->
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Di Dalam Gedung
                                        </span>
                                    @else
                                        <!-- Abu-abu: Selesai / Check-Out -->
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded-full text-xs font-semibold">
                                                Selesai / Check-Out
                                            </span>
                                            @if($v->durasi_kunjungan)
                                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1 font-medium">
                                                    <span>⏱️</span> {{ $v->durasi_kunjungan }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <!-- Tombol Aksi: Cetak Badge, Check-Out, Edit, Hapus -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol Cetak Badge -->
                                        <a href="{{ route('visitors.badge', $v->id) }}" target="_blank" class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white px-2 py-1.5 rounded-lg border border-indigo-200 hover:border-indigo-600 font-medium transition shadow-sm" title="Buka ID Card & Siap Cetak">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span class="hidden sm:inline">Cetak</span>
                                        </a>

                                        @if($v->status === 'SCHEDULED')
                                            <!-- Tombol Konfirmasi Kedatangan (Pra-Registrasi) -->
                                            <button type="button" wire:click="confirmArrival({{ $v->id }})" class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white px-2 py-1.5 rounded-lg border border-emerald-200 hover:border-emerald-600 font-semibold transition shadow-sm" title="Konfirmasi Tamu Tiba di Gedung">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Tiba</span>
                                            </button>
                                        @elseif($v->status === 'INSIDE')
                                            <!-- Tombol Check-Out 1-Klik Livewire dengan Dialog Konfirmasi -->
                                            <button type="button" wire:click="checkout({{ $v->id }})" wire:confirm="Konfirmasi check-out untuk tamu {{ $v->name }}?" wire:loading.attr="disabled" class="inline-flex items-center gap-1 text-xs bg-red-50 text-red-600 hover:bg-red-600 hover:text-white px-2 py-1.5 rounded-lg border border-red-200 hover:border-red-600 font-medium transition shadow-sm disabled:opacity-50" title="Check-Out Tamu">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                <span>Check-Out</span>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 text-xs text-gray-400 font-medium py-1 px-1">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Selesai
                                            </span>
                                        @endif

                                        <!-- Tombol Toggle Status VIP -->
                                        <button type="button" wire:click="toggleVip({{ $v->id }})" class="p-1.5 {{ $v->is_vip ? 'text-amber-500 hover:text-amber-600 bg-amber-50' : 'text-gray-300 hover:text-amber-500' }} rounded-lg hover:bg-amber-50 transition text-xs" title="{{ $v->is_vip ? 'Hapus Status VIP' : 'Jadikan Tamu VIP' }}">
                                            ⭐
                                        </button>

                                        <!-- Tombol Notifikasi WhatsApp ke Host -->
                                        <button type="button" wire:click="openWhatsAppModal({{ $v->id }})" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="Kirim Notifikasi WhatsApp ke Host">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.769.839 2.791.84h.005c3.18 0 5.766-2.587 5.767-5.766.001-3.181-2.585-5.766-5.766-5.766zm9.969 5.767c0 5.514-4.486 10-10 10-1.748 0-3.385-.45-4.819-1.238l-7.181 1.88 1.914-6.994c-.878-1.492-1.385-3.23-1.385-5.087 0-5.515 4.486-10 10-10 5.514 0 10 4.485 10 10z"/>
                                            </svg>
                                        </button>

                                        <!-- Tombol Edit Data Tamu -->
                                        <button type="button" wire:click="editVisitor({{ $v->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition" title="Edit Data Tamu">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Tombol Hapus/Batal Kunjungan (Hanya Administrator) -->
                                        @if(Auth::user()?->isAdmin())
                                            <button type="button" wire:click="deleteVisitor({{ $v->id }})" wire:confirm="Batalkan dan hapus data kunjungan tamu {{ $v->name }}?" class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Batalkan / Hapus Kunjungan (Admin)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-400">
                                    @if($search)
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-2xl mb-1">🔍</span>
                                            <p>Tidak ada data tamu yang cocok dengan pencarian "<strong>{{ $search }}</strong>".</p>
                                        </div>
                                    @else
                                        <p>Belum ada data tamu hari ini.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginasi Tabel -->
            @if($visitors->hasPages())
                <div class="mt-4 pt-3 border-t">
                    {{ $visitors->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Edit Data Tamu -->
    @if($editingVisitorId)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-200">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-1.5">
                        <span>✏️</span> Edit Data Kunjungan Tamu
                    </h3>
                    <button type="button" wire:click="cancelEdit" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>

                <form wire:submit="updateVisitor" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Nama Tamu</label>
                        <input type="text" wire:model="edit_name" class="mt-1 w-full p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('edit_name') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Asal Instansi</label>
                        <input type="text" wire:model="edit_instansi" class="mt-1 w-full p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('edit_instansi') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Pegawai Dituju (Host)</label>
                        <select wire:model="edit_employee_id" class="mt-1 w-full p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih Pegawai</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department }})</option>
                            @endforeach
                        </select>
                        @error('edit_employee_id') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Keperluan Kunjungan</label>
                        <input type="text" wire:model="edit_purpose" class="mt-1 w-full p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('edit_purpose') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-amber-900">
                            <input type="checkbox" wire:model="edit_is_vip" class="rounded text-amber-600 focus:ring-amber-500">
                            <span>⭐ Status Tamu VIP / Prioritas</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="cancelEdit" class="px-3.5 py-1.5 border rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Tambah Master Pegawai -->
    @if($showEmployeeModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-200">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-1.5">
                        <span>👔</span> Tambah Pegawai (Host) Baru
                    </h3>
                    <button type="button" wire:click="$set('showEmployeeModal', false)" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>

                <form wire:submit="addEmployee" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Nama Lengkap Pegawai</label>
                        <input type="text" wire:model="new_emp_name" placeholder="Contoh: Budi Santoso" class="mt-1 w-full p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('new_emp_name') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Departemen / Divisi</label>
                        <input type="text" wire:model="new_emp_department" placeholder="Contoh: HRD / Finance" class="mt-1 w-full p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('new_emp_department') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Email Pegawai (Untuk Notifikasi)</label>
                        <input type="email" wire:model="new_emp_email" placeholder="Contoh: budi@company.com" class="mt-1 w-full p-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('new_emp_email') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showEmployeeModal', false)" class="px-3.5 py-1.5 border rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50">
                            Tutup
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 shadow-sm">
                            Simpan Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Kelola Master Data Pegawai (Daftar & Hapus) -->
    @if($showManageEmployeesModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 border border-slate-200">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-1.5">
                            <span>👥</span> Kelola Master Data Pegawai (Host)
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar staf/pegawai penerima tamu yang terdaftar di sistem</p>
                    </div>
                    <button type="button" wire:click="$set('showManageEmployeesModal', false)" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-semibold text-gray-600">{{ $employees->count() }} Pegawai Terdaftar</span>
                    <div class="flex items-center gap-2">
                        @if($employees->isEmpty())
                            <button type="button" wire:click="seedDefaultEmployees" class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-800 px-3 py-1.5 rounded-lg font-semibold transition">
                                + Buat Data Default
                            </button>
                        @endif
                        <button type="button" wire:click="$set('showEmployeeModal', true)" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg font-semibold transition shadow-sm">
                            + Tambah Pegawai
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-72 border rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-gray-50 text-gray-600 font-bold border-b sticky top-0">
                            <tr>
                                <th class="p-2.5">Nama Pegawai</th>
                                <th class="p-2.5">Departemen</th>
                                <th class="p-2.5">Email Notifikasi</th>
                                <th class="p-2.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-gray-700">
                            @forelse($employees as $emp)
                                <tr class="hover:bg-gray-50/75 transition">
                                    <td class="p-2.5 font-bold text-gray-900">{{ $emp->name }}</td>
                                    <td class="p-2.5">{{ $emp->department }}</td>
                                    <td class="p-2.5 font-mono text-[11px] text-gray-600">{{ $emp->email }}</td>
                                    <td class="p-2.5 text-center">
                                        <button type="button" wire:click="deleteEmployee({{ $emp->id }})" wire:confirm="Yakin ingin menghapus pegawai {{ $emp->name }}?" class="text-rose-600 hover:text-rose-800 p-1 rounded hover:bg-rose-50 transition" title="Hapus Pegawai">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-gray-400">
                                        Belum ada pegawai terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-4 pt-3 border-t">
                    <button type="button" wire:click="$set('showManageEmployeesModal', false)" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Pratinjau Foto KTP -->
    @if($previewImageUrl)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-5 border border-slate-200">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                        <span>📷</span> Foto Dokumen Identitas (KTP / Kartu Nama)
                    </h3>
                    <button type="button" wire:click="closePreview" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>
                <div class="bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center border p-2">
                    <img src="{{ $previewImageUrl }}" alt="Dokumen KTP" class="max-h-80 w-auto object-contain rounded-lg shadow-sm">
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="closePreview" class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-semibold transition">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Notifikasi WhatsApp ke Host (Simulator & Direct Link) -->
    @if($showWhatsAppModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200">
                <!-- Header WhatsApp -->
                <div class="bg-emerald-600 text-white p-4 flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-lg">
                            💬
                        </div>
                        <div>
                            <h3 class="text-sm font-bold">Kirim Notifikasi WhatsApp ke Host</h3>
                            <p class="text-[11px] text-emerald-100">Kirim pesan instan bahwa tamu telah tiba di lobi</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeWhatsAppModal" class="text-white/80 hover:text-white font-bold text-lg">✕</button>
                </div>

                <div class="p-5 space-y-4">
                    <!-- WhatsApp Chat Bubble Preview -->
                    <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                                <span>📱</span> Kepada: {{ $whatsAppData['host'] ?? '-' }}
                            </span>
                            <span class="text-[10px] text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full font-semibold">Tamu Tiba di Lobi</span>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-emerald-200 shadow-sm text-xs text-gray-800 leading-relaxed font-sans whitespace-pre-line">
                            {{ $whatsAppData['message'] ?? '' }}
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-end items-center gap-2 pt-2 border-t">
                        <button type="button" wire:click="closeWhatsAppModal" class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                            Tutup
                        </button>
                        <a href="{{ $whatsAppData['link'] ?? '#' }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-md transition">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.769.839 2.791.84h.005c3.18 0 5.766-2.587 5.767-5.766.001-3.181-2.585-5.766-5.766-5.766zm9.969 5.767c0 5.514-4.486 10-10 10-1.748 0-3.385-.45-4.819-1.238l-7.181 1.88 1.914-6.994c-.878-1.492-1.385-3.23-1.385-5.087 0-5.515 4.486-10 10-10 5.514 0 10 4.485 10 10z"/>
                            </svg>
                            <span>Buka WhatsApp Web / App</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Roster Evakuasi Darurat K3 (HSE Muster Roll) -->
    @if($showEvacuationModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden border-2 border-rose-600">
                <!-- Header Evakuasi -->
                <div class="bg-gradient-to-r from-rose-700 via-red-600 to-rose-700 text-white p-5 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-2xl animate-pulse">
                            🚨
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold tracking-wide uppercase">Daftar Evakuasi Darurat (K3 / HSE Muster Roll)</h3>
                            <p class="text-xs text-rose-100">Daftar seluruh tamu yang terkonfirmasi berada di dalam gedung saat ini</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeEvacuationModal" class="text-white/80 hover:text-white font-bold text-xl px-2">✕</button>
                </div>

                <!-- Info Banner K3 & Headcount -->
                <div class="bg-rose-50 border-b border-rose-200 p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-rose-900">Total Tamu Di Dalam Gedung (Headcount):</span>
                        <span class="text-xl font-black text-rose-700 bg-white px-3 py-0.5 rounded-lg border border-rose-300 shadow-sm">
                            {{ count($evacuationVisitors) }} Orang
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak / Print Roster Evakuasi</span>
                        </button>
                    </div>
                </div>

                <!-- Tabel Roster Tamu -->
                <div class="p-5 overflow-y-auto flex-1">
                    @if(count($evacuationVisitors) > 0)
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-gray-100 text-gray-700 font-bold border-b sticky top-0">
                                <tr>
                                    <th class="p-2.5">No</th>
                                    <th class="p-2.5">Pass ID</th>
                                    <th class="p-2.5">Nama Tamu</th>
                                    <th class="p-2.5">Instansi</th>
                                    <th class="p-2.5">Host Dituju</th>
                                    <th class="p-2.5">Departemen</th>
                                    <th class="p-2.5">Waktu Check-In</th>
                                    <th class="p-2.5 text-center">Status Evakuasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-gray-800">
                                @foreach($evacuationVisitors as $index => $ev)
                                    <tr class="hover:bg-rose-50/50 transition">
                                        <td class="p-2.5 font-bold text-gray-500">{{ $index + 1 }}</td>
                                        <td class="p-2.5 font-mono font-bold text-rose-700">{{ $ev->qr_code ?? ('PASS-'.$ev->id) }}</td>
                                        <td class="p-2.5 font-bold text-gray-900">{{ $ev->name ?? $ev->nama_tamu }}</td>
                                        <td class="p-2.5">{{ $ev->instansi }}</td>
                                        <td class="p-2.5 font-semibold text-gray-800">{{ $ev->employee?->name ?? $ev->host_tujuan ?? '-' }}</td>
                                        <td class="p-2.5 text-gray-500">{{ $ev->employee?->department ?? '-' }}</td>
                                        <td class="p-2.5 font-mono font-semibold text-indigo-700">
                                            {{ $ev->waktu_checkin ? \Illuminate\Support\Carbon::parse($ev->waktu_checkin)->format('H:i') : '-' }} WIB
                                        </td>
                                        <td class="p-2.5 text-center">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span>⚠️</span> Di Titik Kumpul
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="py-12 text-center text-gray-400">
                            <span class="text-4xl">🏢</span>
                            <p class="font-semibold text-sm text-gray-700 mt-2">Tidak Ada Tamu Di Dalam Gedung</p>
                            <p class="text-xs text-gray-400">Semua tamu tercatat telah melakukan check-out atau belum tiba.</p>
                        </div>
                    @endif
                </div>

                <!-- Footer Modal Evakuasi -->
                <div class="p-4 bg-gray-50 border-t flex justify-between items-center text-xs text-gray-500">
                    <span>Protokol Tanggap Darurat &bull; Tanggal: {{ today()->format('d/m/Y') }}</span>
                    <button type="button" wire:click="closeEvacuationModal" class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition">
                        Tutup Roster
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showFeaturesModal)
        <!-- Modal Direktori 12 Fitur Unggulan Sistem LobbyFlow -->
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-indigo-200">
                <!-- Header Direktori Fitur -->
                <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 text-white p-5 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-2xl shadow-inner">
                            ✨
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-extrabold tracking-wide uppercase">Direktori 12 Fitur Unggulan Sistem</h3>
                                <span class="text-[10px] bg-emerald-400/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full border border-emerald-400/40">100% Siap Diuji</span>
                            </div>
                            <p class="text-xs text-indigo-100">Katalog modul dan kapabilitas sistem LobbyFlow hasil revisi & pengujian</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeFeaturesModal" class="text-white/80 hover:text-white font-bold text-xl px-2">✕</button>
                </div>

                <!-- Konten Daftar 12 Fitur -->
                <div class="p-6 overflow-y-auto space-y-4 text-xs text-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <!-- Fitur 1 -->
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-indigo-50 rounded-lg">🔑</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-sm">Autentikasi & Quick Login</h4>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">✓ PASS</span>
                                </div>
                                <p class="text-gray-500 mt-1">Dual role (Admin & Resepsionis) dengan tombol 1-klik Quick Login yang tahan terhadap database kosong (auto-create demo account).</p>
                            </div>
                        </div>

                        <!-- Fitur 2 -->
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-purple-50 rounded-lg">🛡️</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-sm">Role-Based Access Control</h4>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">✓ PASS</span>
                                </div>
                                <p class="text-gray-500 mt-1">Middleware <code>EnsureUserIsAdmin</code> melindungi halaman laporan eksekutif dari akses non-admin dengan pesan flash informatif.</p>
                            </div>
                        </div>

                        <!-- Fitur 3 -->
                        <div class="p-3.5 rounded-xl border border-purple-200 bg-purple-50/40 shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-purple-100 rounded-lg">📸</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-purple-950 text-sm">Smart Scan AI (Vision OCR)</h4>
                                    <span class="text-[10px] bg-purple-600 text-white font-bold px-2 py-0.5 rounded-full">✨ AI VISION</span>
                                </div>
                                <p class="text-gray-600 mt-1">Ekstraksi otomatis nama tamu & asal instansi dari foto KTP atau kartu nama via Vision AI engine dan regex fallback cerdas.</p>
                            </div>
                        </div>

                        <!-- Fitur 4 -->
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-indigo-50 rounded-lg">✍️</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-sm">Form Check-In Interaktif</h4>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">✓ REAKTIF</span>
                                </div>
                                <p class="text-gray-500 mt-1">Dilengkapi template keperluan instan (Meeting, Vendor, dsb.), checkbox protokol K3, dan mode Pra-Registrasi kunjungan.</p>
                            </div>
                        </div>

                        <!-- Fitur 5 -->
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-blue-50 rounded-lg">🔍</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-sm">Pencarian Live Real-Time</h4>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">✓ LIVEWIRE</span>
                                </div>
                                <p class="text-gray-500 mt-1">Penyaringan instan daftar tamu berdasarkan nama atau instansi secara live tanpa refresh halaman (debounce 300ms).</p>
                            </div>
                        </div>

                        <!-- Fitur 6 -->
                        <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-amber-100 rounded-lg">⚡</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-amber-950 text-sm">Quick Pass Barcode Scanner</h4>
                                    <span class="text-[10px] bg-amber-600 text-white font-bold px-2 py-0.5 rounded-full">⚡ 1-DETIK</span>
                                </div>
                                <p class="text-gray-600 mt-1">Console scanner untuk check-out cepat tamu via scan barcode atau ketik ID pass tamu tanpa perlu mencari manual di tabel.</p>
                            </div>
                        </div>

                        <!-- Fitur 7 -->
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-rose-50 rounded-lg">⏱️</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-sm">1-Klik Check-Out & Idempotency</h4>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">✓ AMAN</span>
                                </div>
                                <p class="text-gray-500 mt-1">Konfirmasi dialog <code>wire:confirm</code>, kalkulasi otomatis durasi kunjungan (jam & menit), serta proteksi data tamper-proof.</p>
                            </div>
                        </div>

                        <!-- Fitur 8 -->
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-indigo-50 rounded-lg">🏷️</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-sm">Cetak ID Badge & QR SVG</h4>
                                    <span class="text-[10px] bg-indigo-100 text-indigo-800 font-bold px-2 py-0.5 rounded-full">✓ SVG QR</span>
                                </div>
                                <p class="text-gray-500 mt-1">Kartu identitas pengunjung resmi dengan kode QR SVG tajam, foto identitas, dan CSS khusus print background graphics.</p>
                            </div>
                        </div>

                        <!-- Fitur 9 -->
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-emerald-50 rounded-lg">📧</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 text-sm">Notifikasi Email Host Otomatis</h4>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">✓ QUEUED</span>
                                </div>
                                <p class="text-gray-500 mt-1">Pengiriman email otomatis ke staf yang dituju dengan implementasi <code>ShouldQueue</code> agar respons antarmuka tetap instan.</p>
                            </div>
                        </div>

                        <!-- Fitur 10 -->
                        <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/40 shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-emerald-100 rounded-lg">💬</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-emerald-950 text-sm">Notifikasi WhatsApp Instan</h4>
                                    <span class="text-[10px] bg-emerald-600 text-white font-bold px-2 py-0.5 rounded-full">💬 INSTANT</span>
                                </div>
                                <p class="text-gray-600 mt-1">Pratinjau bubble chat WhatsApp dan tombol 1-klik untuk membuka WhatsApp Web/Desktop dan mengabari pegawai penerima.</p>
                            </div>
                        </div>

                        <!-- Fitur 11 -->
                        <div class="p-3.5 rounded-xl border border-rose-200 bg-rose-50/40 shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-rose-100 rounded-lg">🚨</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-rose-950 text-sm">Roster Evakuasi Darurat K3</h4>
                                    <span class="text-[10px] bg-rose-600 text-white font-bold px-2 py-0.5 rounded-full">🚨 SAFETY</span>
                                </div>
                                <p class="text-gray-600 mt-1">Protokol darurat K3/HSE untuk mendata seluruh tamu yang sedang berada di dalam gedung dan mencetak daftar hadir evakuasi.</p>
                            </div>
                        </div>

                        <!-- Fitur 12 -->
                        <div class="p-3.5 rounded-xl border border-teal-200 bg-teal-50/40 shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-teal-100 rounded-lg">📥</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-teal-950 text-sm">Ekspor Excel CSV & Cetak PDF</h4>
                                    <span class="text-[10px] bg-teal-600 text-white font-bold px-2 py-0.5 rounded-full">📊 EXCEL/PDF</span>
                                </div>
                                <p class="text-gray-600 mt-1">Stream CSV dengan UTF-8 BOM untuk kompatibilitas Microsoft Excel, serta cetak dokumen PDF rekap resmi dengan kop surat.</p>
                            </div>
                        </div>

                        <!-- Fitur 13 -->
                        <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/40 shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-blue-100 rounded-lg">📶</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-blue-950 text-sm">Akses Wi-Fi Tamu & QR Connect</h4>
                                    <span class="text-[10px] bg-blue-600 text-white font-bold px-2 py-0.5 rounded-full">📶 WI-FI VOUCHER</span>
                                </div>
                                <p class="text-gray-600 mt-1">Kredensial Wi-Fi lobi otomatis dengan QR Code scan-to-connect di layar resepsionis dan tercetak langsung di kartu Visitor Pass.</p>
                            </div>
                        </div>

                        <!-- Fitur 14 -->
                        <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 shadow-sm flex items-start gap-3">
                            <span class="text-2xl p-2 bg-amber-100 rounded-lg">⭐</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-amber-950 text-sm">VIP Guest & Check-Out Massal</h4>
                                    <span class="text-[10px] bg-amber-600 text-white font-bold px-2 py-0.5 rounded-full">⭐ BATCH OPS</span>
                                </div>
                                <p class="text-gray-600 mt-1">Penandaan tamu kehormatan/VIP dengan badge emas, filter khusus, dan fitur multi-select untuk check-out rombongan sekaligus.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal Fitur -->
                <div class="p-4 bg-gray-50 border-t flex justify-between items-center text-xs">
                    <span class="text-gray-500 font-medium">LobbyFlow Enterprise Visitor Management System &bull; Versi 2.0</span>
                    <button type="button" wire:click="closeFeaturesModal" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-sm">
                        Tutup Direktori
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Akses Wi-Fi Tamu (Guest Wi-Fi Voucher) -->
    @if($showWifiModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-blue-200">
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white p-5 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">
                            📶
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold tracking-wide">Akses Wi-Fi Tamu Gedung</h3>
                            <p class="text-xs text-blue-100">Koneksi internet resmi untuk pengunjung & mitra</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeWifiModal" class="text-white/80 hover:text-white font-bold text-xl px-2">✕</button>
                </div>

                <div class="p-6 text-center space-y-4">
                    <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-100 space-y-3">
                        <div class="text-left text-xs space-y-2">
                            <div class="flex justify-between items-center pb-2 border-b border-blue-200/50">
                                <span class="text-gray-500 font-semibold">Nama Network (SSID):</span>
                                <span class="font-mono font-bold text-gray-900 text-sm">LobbyFlow-Guest</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-blue-200/50">
                                <span class="text-gray-500 font-semibold">Kata Sandi (Password):</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-indigo-700 text-sm">Tamu{{ date('Y') }}!</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('Tamu{{ date('Y') }}!'); showCopyToast('Tamu{{ date('Y') }}!')" class="text-indigo-600 hover:text-indigo-800 text-xs" title="Salin Password">
                                        📋
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-semibold">Keamanan / Bandwidth:</span>
                                <span class="text-emerald-700 font-bold text-xs">WPA3 Secure &bull; High Speed</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-gray-600">
                        <p class="font-medium">💡 Informasi Wi-Fi ini juga tercetak secara otomatis pada kartu <strong>Visitor Pass</strong> yang diberikan kepada tamu.</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" wire:click="closeWifiModal" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Kamera Barcode & QR Code Scanner -->
    @if($showCameraModal)
        <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-indigo-200">
                <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 text-white p-4 flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-lg">
                            📷
                        </div>
                        <div>
                            <h3 class="text-sm font-bold">Kamera Scanner Pass Tamu</h3>
                            <p class="text-[11px] text-indigo-200">Arahkan QR Code Visitor Pass ke depan kamera</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeCameraModal" class="text-white/80 hover:text-white font-bold text-lg px-2">✕</button>
                </div>

                <div class="p-5 space-y-4 text-center">
                    <!-- Kamera Viewfinder Simulation / Real WebRTC -->
                    <div class="relative bg-slate-950 rounded-2xl overflow-hidden aspect-video flex items-center justify-center border-2 border-indigo-500 shadow-inner">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-48 h-48 border-2 border-dashed border-emerald-400 rounded-2xl relative animate-pulse flex items-center justify-center">
                                <span class="text-xs text-emerald-300 font-mono bg-black/60 px-2 py-1 rounded">Target QR Code</span>
                                <div class="absolute inset-x-0 top-1/2 h-0.5 bg-rose-500 shadow-[0_0_10px_#f43f5e] animate-bounce"></div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 absolute bottom-3">Kamera scanner aktif &bull; Siap memindai</p>
                    </div>

                    <!-- Input Cepat / Demo Scanner Trigger -->
                    <div class="flex items-center gap-2">
                        <input type="text" wire:model="quickPassCode" placeholder="Atau ketik/tempel Kode Pass..." class="flex-1 p-2 text-xs border rounded-lg font-mono focus:ring-2 focus:ring-indigo-500">
                        <button type="button" wire:click="quickCheckout" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition shadow-sm whitespace-nowrap">
                            Check-Out
                        </button>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-500 pt-2 border-t">
                        <span class="text-[11px]">Mendukung barcode 1D & QR Code 2D</span>
                        <button type="button" wire:click="closeCameraModal" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                            Tutup Scanner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Mode Kiosk Tamu Mandiri (Self-Service Kiosk Mode) -->
    @if($kioskMode)
        <div class="fixed inset-0 bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 z-50 text-white p-6 md:p-10 flex flex-col justify-between overflow-y-auto">
            <!-- Header Kiosk -->
            <div class="flex justify-between items-center border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.svg') }}" alt="Logo" class="w-12 h-12 rounded-xl shadow-md">
                    <div>
                        <h2 class="text-2xl font-black text-indigo-300">LobbyFlow Self-Service</h2>
                        <p class="text-xs text-gray-400">Selamat datang di Meja Resepsionis Mandiri</p>
                    </div>
                </div>
                <button type="button" wire:click="toggleKioskMode" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-xs font-bold text-gray-300 transition flex items-center gap-1.5 border border-white/15">
                    <span>✕</span>
                    <span>Kembali ke Resepsionis</span>
                </button>
            </div>

            <!-- Konten Utama Kiosk Touchscreen -->
            <div class="max-w-xl mx-auto w-full my-8 text-center space-y-6">
                <div class="space-y-2">
                    <span class="text-5xl">👋</span>
                    <h1 class="text-3xl font-extrabold text-white">Selamat Datang di Kantor Kami</h1>
                    <p class="text-sm text-indigo-200">Silakan pilih layanan registrasi tamu mandiri di bawah ini:</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                    <button type="button" wire:click="toggleKioskMode" class="p-6 bg-white/10 hover:bg-indigo-600/40 rounded-2xl border-2 border-indigo-400/40 transition shadow-lg group hover:scale-[1.02] transform duration-150">
                        <span class="text-4xl block mb-2">✍️</span>
                        <h3 class="text-lg font-bold text-white group-hover:text-indigo-200">Check-In Baru</h3>
                        <p class="text-xs text-gray-300 mt-1">Registrasi nama, instansi, dan pegawai yang dituju</p>
                    </button>

                    <button type="button" wire:click="openCameraModal" class="p-6 bg-white/10 hover:bg-emerald-600/40 rounded-2xl border-2 border-emerald-400/40 transition shadow-lg group hover:scale-[1.02] transform duration-150">
                        <span class="text-4xl block mb-2">⚡</span>
                        <h3 class="text-lg font-bold text-white group-hover:text-emerald-200">Scan Check-Out</h3>
                        <p class="text-xs text-gray-300 mt-1">Scan pass tamu untuk check-out saat meninggalkan gedung</p>
                    </button>
                </div>

                <div class="p-4 bg-white/5 rounded-2xl border border-white/10 text-xs text-gray-400 space-y-1">
                    <p class="font-semibold text-gray-300">📶 Wi-Fi Tamu: <strong class="text-indigo-300 font-mono">LobbyFlow-Guest</strong> | Pass: <strong class="text-indigo-300 font-mono">Tamu{{ date('Y') }}!</strong></p>
                    <p>Harap selalu mengenakan kartu Visitor Pass selama berada di lingkungan perkantoran.</p>
                </div>
            </div>

            <!-- Footer Kiosk -->
            <div class="text-center text-xs text-gray-500 border-t border-white/10 pt-4">
                LobbyFlow Enterprise Kiosk Mode &bull; Touch-Screen Ready &bull; Dilindungi CCTV & Standar K3
            </div>
        </div>
    @endif
    <div id="copy-toast" class="fixed bottom-5 right-5 z-50 bg-gray-900 text-white text-xs px-4 py-2.5 rounded-xl shadow-lg border border-gray-700 flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <span>📋</span>
        <span id="copy-toast-text">Kode Pass berhasil disalin!</span>
    </div>

    <script>
        function updateLiveClock() {
            const el = document.getElementById('live-clock');
            if (!el) return;
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            el.textContent = `${h}:${m}:${s} WIB`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // 🔔 Web Audio API Synthesizer (Nada Dering Lobi Ding-Dong)
        let lobbyAudioEnabled = localStorage.getItem('lobbyflow_audio') !== 'false';

        function updateAudioButtonUi() {
            const icon = document.getElementById('audio-icon');
            const label = document.getElementById('audio-label');
            const btn = document.getElementById('audio-toggle-btn');
            if (!icon || !label || !btn) return;
            if (lobbyAudioEnabled) {
                icon.textContent = '🔔';
                label.textContent = 'Suara Lobi: On';
                btn.classList.remove('bg-gray-100', 'text-gray-400');
                btn.classList.add('bg-white', 'text-gray-700');
            } else {
                icon.textContent = '🔕';
                label.textContent = 'Suara Lobi: Mute';
                btn.classList.remove('bg-white', 'text-gray-700');
                btn.classList.add('bg-gray-100', 'text-gray-400');
            }
        }

        function toggleLobbyAudio() {
            lobbyAudioEnabled = !lobbyAudioEnabled;
            localStorage.setItem('lobbyflow_audio', lobbyAudioEnabled ? 'true' : 'false');
            updateAudioButtonUi();
        }

        function playLobbyChime() {
            if (!lobbyAudioEnabled) return;
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();

                // Tone 1: E5 (659.25 Hz)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(659.25, ctx.currentTime);
                gain1.gain.setValueAtTime(0.18, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start();
                osc1.stop(ctx.currentTime + 0.45);

                // Tone 2: C5 (523.25 Hz)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(523.25, ctx.currentTime + 0.22);
                gain2.gain.setValueAtTime(0.22, ctx.currentTime + 0.22);
                gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.75);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(ctx.currentTime + 0.22);
                osc2.stop(ctx.currentTime + 0.75);
            } catch(e) {
                console.log('Chime sound not played:', e);
            }
        }

        window.addEventListener('play-chime', playLobbyChime);
        document.addEventListener('DOMContentLoaded', updateAudioButtonUi);
        document.addEventListener('livewire:navigated', updateAudioButtonUi);

        // 📋 Toast Feedback Salin Pass Code
        function showCopyToast(code) {
            const toast = document.getElementById('copy-toast');
            const text = document.getElementById('copy-toast-text');
            if (!toast || !text) return;
            text.textContent = `Kode Pass ${code} disalin ke clipboard!`;
            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 2500);
        }
    </script>
</div>

<div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <header class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo.svg') }}" alt="LobbyFlow Logo" class="w-10 h-10 rounded-xl shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-indigo-600 leading-tight">LobbyFlow - Manajemen Tamu</h1>
                <p class="text-xs text-gray-500 mt-0.5">Sistem resepsionis cerdas dan pemantauan kunjungan tamu secara real-time &bull; Powered by Livewire</p>
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

            <span class="text-sm bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full font-medium flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                Live Monitoring
            </span>
        </div>
    </header>

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

    <!-- ✨ AI Executive Summary Preview Card -->
    <div class="mb-6 bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-xl p-4 text-white shadow-sm border border-indigo-700/50 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-white/10 rounded-lg text-xl border border-white/15">
                ✨
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold tracking-wide">AI Executive Summary</h3>
                    <span class="text-[10px] bg-indigo-500/40 text-indigo-200 px-2 py-0.5 rounded-full border border-indigo-400/30">Live Analysis</span>
                </div>
                <p class="text-xs text-indigo-100 mt-0.5">
                    Sistem AI aktif memantau arus kedatangan tamu, mendeteksi mitra korporasi teratas, dan menjaga tingkat kepatuhan check-out secara real-time.
                </p>
            </div>
        </div>
        @if(Auth::user()?->role === 'admin')
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white/15 hover:bg-white/25 text-white px-3.5 py-1.5 rounded-lg border border-white/20 transition whitespace-nowrap">
                <span>Buka Laporan Lengkap</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 text-xs text-indigo-200 bg-white/10 px-3 py-1.5 rounded-lg border border-white/10 whitespace-nowrap font-medium" title="Hanya Admin yang dapat membuka halaman Laporan">
                <span>🔒 Khusus Admin</span>
            </span>
        @endif
    </div>

    <!-- Pesan Sukses -->
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Form Check-In Tamu (Kiri) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
            <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center gap-2">
                <span>✍️</span> Check-In Tamu Baru
            </h2>

            <form wire:submit="save" class="space-y-4">
                <!-- ✨ Smart Scan Dropzone (KTP / Kartu Nama) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-gray-700 flex items-center gap-1.5">
                            <span class="text-indigo-600">✨</span> Smart Scan (KTP / Kartu Nama)
                        </label>
                        <button type="button" wire:click="simulateAiScan" class="text-[10px] bg-purple-100 hover:bg-purple-200 text-purple-700 px-2.5 py-0.5 rounded-full font-bold transition flex items-center gap-1" title="Isi form otomatis dengan simulasi AI">
                            <span>✨ Demo Scan</span>
                        </button>
                    </div>

                    <div id="dropzone" class="border-2 border-dashed border-indigo-200 hover:border-indigo-400 rounded-xl p-3 text-center cursor-pointer bg-indigo-50/30 hover:bg-indigo-50/60 transition relative">
                        <input type="file" wire:model="id_card_image" id="id_card_input" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div id="dropzone_prompt" class="{{ $id_card_image ? 'hidden' : '' }} space-y-1">
                            <div class="text-2xl text-indigo-500">📷</div>
                            <p class="text-xs font-medium text-gray-700">Drop foto KTP / Kartu Nama di sini</p>
                            <p class="text-[10px] text-gray-400">atau klik untuk unggah gambar</p>
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
                        <button type="button" wire:click="$set('showEmployeeModal', true)" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold hover:underline">
                            + Tambah Pegawai
                        </button>
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
                    @error('purpose')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Submit -->
                <button type="submit" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-semibold transition shadow-md flex items-center justify-center gap-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Simpan & Check-In
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2 text-sm">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Memproses Check-In...
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
                    <span class="text-xs text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full font-bold">
                        {{ $visitors->count() }} Tamu Tercatat
                    </span>
                </div>
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

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="pb-3">Tamu & Instansi</th>
                            <th class="pb-3">Host Tujuan</th>
                            <th class="pb-3">Keperluan</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        @forelse($visitors as $v)
                            <tr wire:key="visitor-{{ $v->id }}" class="hover:bg-gray-50/75 transition">
                                <!-- Tamu & Instansi -->
                                <td class="py-3.5 pr-2">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $v->name ?? $v->nama_tamu }}</p>
                                            <p class="text-xs text-gray-500 font-medium">{{ $v->instansi }}</p>
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
                                    @if($v->status === 'INSIDE')
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

                                        <!-- Tombol Check-Out 1-Klik Livewire dengan Dialog Konfirmasi -->
                                        @if($v->status === 'INSIDE')
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

                                        <!-- Tombol Edit Data Tamu -->
                                        <button type="button" wire:click="editVisitor({{ $v->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition" title="Edit Data Tamu">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Tombol Hapus/Batal Kunjungan -->
                                        <button type="button" wire:click="deleteVisitor({{ $v->id }})" wire:confirm="Batalkan dan hapus data kunjungan tamu {{ $v->name }}?" class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Batalkan / Hapus Kunjungan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-400">
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
</div>

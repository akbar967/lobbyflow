<div class="bg-slate-50 text-slate-800 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-3">
                <img src="{{ asset('favicon.svg') }}" alt="LobbyFlow Logo" class="w-14 h-14 rounded-2xl shadow-lg">
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">LobbyFlow Enterprise</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem Resepsionis Pintar & Pemantauan Tamu Multi-Role &bull; Powered by Livewire</p>
        </div>

        <!-- Card Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8">
            <!-- Flash Message -->
            @if(session('success'))
                <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Form Login Livewire -->
            <form wire:submit.prevent="login" action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Pengguna</label>
                    <input type="email" wire:model="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@lobbyflow.com" class="w-full p-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" wire:model="password" name="password" required placeholder="••••••••" class="w-full p-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-rose-500 @enderror">
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="remember" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-sm shadow-md transition flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="login">Masuk ke Sistem</span>
                    <span wire:loading wire:target="login">Memverifikasi...</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-[10px] uppercase">
                    <span class="bg-white px-3 text-slate-400 font-bold tracking-widest">Akses Cepat Pengujian (2 User)</span>
                </div>
            </div>

            <!-- Quick Login Buttons untuk Uji Coba Dosen -->
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('login.quick', 'admin') }}" wire:click.prevent="quickLogin('admin')" class="flex flex-col items-center justify-center p-3 rounded-xl border border-indigo-200 bg-indigo-50/50 hover:bg-indigo-100/60 transition group text-center cursor-pointer">
                    <span class="text-base mb-1">👑</span>
                    <span class="text-xs font-bold text-indigo-900 group-hover:text-indigo-950">Masuk sbg Admin</span>
                    <span class="text-[10px] text-indigo-500 font-medium mt-0.5">Akses Laporan & AI</span>
                </a>
                <a href="{{ route('login.quick', 'receptionist') }}" wire:click.prevent="quickLogin('receptionist')" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition group text-center cursor-pointer">
                    <span class="text-base mb-1">🛎️</span>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-slate-950">Masuk sbg Resepsionis</span>
                    <span class="text-[10px] text-slate-500 font-medium mt-0.5">Operasional Tamu</span>
                </a>
            </div>
        </div>
    </div>
</div>

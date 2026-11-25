<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Login - LobbyFlow')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('visitors.index'));
        }
    }

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    protected $messages = [
        'email.required' => 'Email pengguna wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'password.required' => 'Password wajib diisi.',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();
            $roleLabel = $user->isAdmin() ? 'Administrator' : 'Resepsionis';

            session()->flash('success', "Selamat datang kembali, {$user->name}! Masuk sebagai {$roleLabel}.");

            return $this->redirectIntended(route('visitors.index'));
        }

        $this->addError('email', 'Email atau password yang Anda masukkan salah.');
    }

    public function quickLogin(string $role)
    {
        $targetEmail = $role === 'admin' ? 'admin@lobbyflow.com' : 'resepsionis@lobbyflow.com';
        $user = User::where('email', $targetEmail)->first();

        // Jika database belum di-seed, buat akun default secara instan agar tidak melempar ModelNotFoundException
        if (! $user) {
            $user = User::create([
                'name' => $role === 'admin' ? 'Administrator Lobi' : 'Resepsionis Utama',
                'email' => $targetEmail,
                'password' => bcrypt('password'),
                'role' => $role === 'admin' ? 'admin' : 'receptionist',
            ]);
        }

        Auth::login($user);
        session()->regenerate();

        $roleLabel = $user->isAdmin() ? 'Administrator' : 'Resepsionis';
        session()->flash('success', "Berhasil masuk sebagai {$roleLabel} ({$user->name}).");

        return $this->redirect(route('visitors.index'));
    }

    /**
     * HTTP POST Fallback Handler (menggantikan AuthController::login)
     */
    public static function handleLoginPost(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            $roleLabel = $user->isAdmin() ? 'Administrator' : 'Resepsionis';

            return redirect()->intended(route('visitors.index'))
                ->with('success', "Selamat datang kembali, {$user->name}! Masuk sebagai {$roleLabel}.");
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * HTTP GET Quick Login Route Handler (menggantikan AuthController::quickLogin)
     */
    public static function handleQuickLogin(string $role)
    {
        $email = $role === 'admin' ? 'admin@lobbyflow.com' : 'resepsionis@lobbyflow.com';
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $role === 'admin' ? 'Administrator Lobi' : 'Resepsionis Utama',
                'email' => $email,
                'password' => bcrypt('password'),
                'role' => $role === 'admin' ? 'admin' : 'receptionist',
            ]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        $roleLabel = $user->isAdmin() ? 'Administrator' : 'Resepsionis';

        return redirect()->route('visitors.index')
            ->with('success', "Berhasil masuk sebagai {$roleLabel} ({$user->name}).");
    }

    /**
     * HTTP POST Logout Handler (menggantikan AuthController::logout)
     */
    public static function handleLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    public function render()
    {
        return view('livewire.login');
    }
}

<?php

namespace App\Livewire;

use App\Mail\VisitorArrivedMail;
use App\Models\Employee;
use App\Models\Visitor;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('components.layouts.app')]
#[Title('LobbyFlow - Resepsionis Dashboard')]
class LobbyDashboard extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $instansi = '';

    public ?int $employee_id = null;

    public string $purpose = '';

    public $id_card_image = null;

    public string $search = '';

    public ?string $successMessage = null;

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'employee_id' => 'required|exists:employees,id',
            'purpose' => 'required|string|max:255',
            'id_card_image' => 'nullable|image|max:3072',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama tamu wajib diisi.',
        'instansi.required' => 'Asal instansi / perusahaan wajib diisi.',
        'employee_id.required' => 'Silakan pilih pegawai yang dituju.',
        'purpose.required' => 'Keperluan kunjungan wajib diisi.',
    ];

    /**
     * Otak AI Smart Scan: Dieksekusi otomatis ketika foto KTP/Kartu Nama diunggah.
     */
    public function updatedIdCardImage(): void
    {
        $this->scanOcr();
    }

    public function scanOcr(): void
    {
        if (! $this->id_card_image) {
            return;
        }

        try {
            $ocrService = app(OcrService::class);
            $parsed = $ocrService->scanKtp($this->id_card_image->getRealPath());

            if (! empty($parsed['name'])) {
                $this->name = $parsed['name'];
            }
            if (! empty($parsed['instansi'])) {
                $this->instansi = $parsed['instansi'];
            }
            if (! empty($parsed['purpose']) && empty($this->purpose)) {
                $this->purpose = $parsed['purpose'];
            }

            $this->successMessage = '✨ Smart Scan AI Berhasil: Nama ('.($this->name ?: '-').') dan Instansi ('.($this->instansi ?: '-').') terisi otomatis dari foto KTP!';
        } catch (Throwable $e) {
            Log::warning('Gagal melakukan OCR scan: '.$e->getMessage());
        }
    }

    public function save(): void
    {
        $this->validate();

        $employee = Employee::find($this->employee_id);
        $hostName = $employee ? $employee->name : null;
        $now = now();
        $qrCodePass = 'PASS-'.strtoupper(uniqid());

        $idCardPath = null;
        if ($this->id_card_image) {
            $idCardPath = $this->id_card_image->store('ktp_cards', 'public');
        }

        $visitor = Visitor::create([
            'name' => $this->name,
            'nama_tamu' => $this->name,
            'instansi' => $this->instansi,
            'employee_id' => $this->employee_id,
            'host_tujuan' => $hostName,
            'purpose' => $this->purpose,
            'keperluan' => $this->purpose,
            'qr_code' => $qrCodePass,
            'status' => 'INSIDE',
            'check_in_at' => $now,
            'waktu_checkin' => $now,
            'id_card_image' => $idCardPath,
        ]);

        if ($employee && ! empty($employee->email)) {
            try {
                Mail::to($employee->email)->send(new VisitorArrivedMail($visitor, $employee));
            } catch (Throwable $e) {
                Log::warning('Gagal mengirim email notifikasi ke host: '.$e->getMessage());
            }
        }

        $this->reset(['name', 'instansi', 'employee_id', 'purpose', 'id_card_image']);

        $this->successMessage = 'Tamu berhasil check-in! Notifikasi email telah dikirim ke host.';
    }

    public function checkout(int $id): void
    {
        $visitor = Visitor::findOrFail($id);

        $now = now();
        $checkIn = $visitor->waktu_checkin ?? $visitor->check_in_at ?? $visitor->created_at;

        $durasi = null;
        if ($checkIn) {
            $diffInMinutes = $checkIn->diffInMinutes($now);
            $hours = intdiv($diffInMinutes, 60);
            $minutes = $diffInMinutes % 60;

            if ($hours > 0 && $minutes > 0) {
                $durasi = "{$hours} jam {$minutes} menit";
            } elseif ($hours > 0) {
                $durasi = "{$hours} jam";
            } else {
                $durasi = "{$minutes} menit";
            }
        }

        $visitor->update([
            'status' => 'CHECKED_OUT',
            'check_out_at' => $now,
            'waktu_checkout' => $now,
            'durasi' => $durasi,
        ]);

        $this->successMessage = $durasi
            ? "Tamu {$visitor->name} berhasil check-out! Durasi kunjungan: {$durasi}."
            : "Tamu {$visitor->name} berhasil check-out.";
    }

    public function resetSearch(): void
    {
        $this->search = '';
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirect(route('login'));
    }

    /**
     * HTTP Fallback Store Handler (menggantikan VisitorController::store)
     */
    public static function handleStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'employee_id' => 'required|exists:employees,id',
            'purpose' => 'required|string|max:255',
        ]);

        $employee = Employee::find($request->employee_id);
        $hostName = $employee ? $employee->name : null;
        $now = now();
        $qrCodePass = 'PASS-'.strtoupper(uniqid());

        $idCardPath = null;
        if ($request->hasFile('id_card_image')) {
            $idCardPath = $request->file('id_card_image')->store('ktp_cards', 'public');
        }

        $visitor = Visitor::create([
            'name' => $request->name,
            'nama_tamu' => $request->name,
            'instansi' => $request->instansi,
            'employee_id' => $request->employee_id,
            'host_tujuan' => $hostName,
            'purpose' => $request->purpose,
            'keperluan' => $request->purpose,
            'qr_code' => $qrCodePass,
            'status' => 'INSIDE',
            'check_in_at' => $now,
            'waktu_checkin' => $now,
            'id_card_image' => $idCardPath,
        ]);

        if ($employee && ! empty($employee->email)) {
            try {
                Mail::to($employee->email)->send(new VisitorArrivedMail($visitor, $employee));
            } catch (Throwable $e) {
                Log::warning('Gagal mengirim email notifikasi ke host: '.$e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Tamu berhasil check-in! Notifikasi email telah dikirim ke host.');
    }

    /**
     * HTTP Fallback Check-Out Handler (menggantikan VisitorController::checkout)
     */
    public static function handleCheckout(int $id)
    {
        $visitor = Visitor::findOrFail($id);

        $now = now();
        $checkIn = $visitor->waktu_checkin ?? $visitor->check_in_at ?? $visitor->created_at;

        $durasi = null;
        if ($checkIn) {
            $diffInMinutes = $checkIn->diffInMinutes($now);
            $hours = intdiv($diffInMinutes, 60);
            $minutes = $diffInMinutes % 60;

            if ($hours > 0 && $minutes > 0) {
                $durasi = "{$hours} jam {$minutes} menit";
            } elseif ($hours > 0) {
                $durasi = "{$hours} jam";
            } else {
                $durasi = "{$minutes} menit";
            }
        }

        $visitor->update([
            'status' => 'CHECKED_OUT',
            'check_out_at' => $now,
            'waktu_checkout' => $now,
            'durasi' => $durasi,
        ]);

        $message = $durasi
            ? "Tamu {$visitor->name} berhasil check-out! Durasi kunjungan: {$durasi}."
            : "Tamu {$visitor->name} berhasil check-out.";

        return redirect()->back()->with('success', $message);
    }

    public function render()
    {
        $visitors = Visitor::with('employee')
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nama_tamu', 'like', "%{$search}%")
                        ->orWhere('instansi', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $employees = Employee::all();

        return view('livewire.lobby-dashboard', compact('visitors', 'employees'));
    }
}

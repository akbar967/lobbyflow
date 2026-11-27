<?php

namespace App\Livewire;

use App\Mail\VisitorArrivedMail;
use App\Models\Employee;
use App\Models\Visitor;
use App\Services\OcrService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

#[Layout('components.layouts.app')]
#[Title('LobbyFlow - Resepsionis Dashboard')]
class LobbyDashboard extends Component
{
    use WithFileUploads, WithPagination;

    public string $name = '';

    public string $instansi = '';

    public ?int $employee_id = null;

    public string $purpose = '';

    public $id_card_image = null;

    public string $search = '';

    public string $statusFilter = 'ALL';

    public string $quickPassCode = '';

    public bool $agreement = true;

    public bool $is_preregistered = false;

    public ?string $scheduled_at = null;

    public bool $is_vip = false;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    // Properti Edit Tamu
    public ?int $editingVisitorId = null;

    public string $edit_name = '';

    public string $edit_instansi = '';

    public ?int $edit_employee_id = null;

    public string $edit_purpose = '';

    public bool $edit_is_vip = false;

    public ?string $edit_scheduled_at = null;

    // Properti Multi-Select Check-Out Massal (Batch Check-Out)
    public array $selectedVisitors = [];

    public bool $selectAll = false;

    // Properti Modal Wi-Fi Tamu
    public bool $showWifiModal = false;

    // Properti Modal Kamera QR Scanner
    public bool $showCameraModal = false;

    // Properti Mode Kiosk Lobi (Self-Service)
    public bool $kioskMode = false;

    // Properti Tambah & Kelola Pegawai (Master Data)
    public bool $showEmployeeModal = false;

    public bool $showManageEmployeesModal = false;

    public string $new_emp_name = '';

    public string $new_emp_department = '';

    public string $new_emp_email = '';

    // Properti Preview KTP
    public ?string $previewImageUrl = null;

    // Properti Papan Pengumuman Lobi (Announcement Ticker)
    public string $announcement = '📢 Selamat Datang di LobbyFlow Enterprise! Harap selalu mengenakan Visitor Pass di area gedung kantor & melapor ke meja resepsionis saat check-out.';

    public bool $isEditingAnnouncement = false;

    public string $newAnnouncement = '';

    // Properti WhatsApp Notification Simulator
    public bool $showWhatsAppModal = false;

    public array $whatsAppData = [];

    // Properti Roster Evakuasi Darurat (K3 / HSE)
    public bool $showEvacuationModal = false;

    // Properti Modal Direktori 12 Fitur Unggulan
    public bool $showFeaturesModal = false;

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function setPurposeTemplate(string $text): void
    {
        $this->purpose = $text;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'employee_id' => 'required|exists:employees,id',
            'purpose' => 'required|string|max:255',
            'id_card_image' => 'nullable|image|max:3072',
            'scheduled_at' => 'nullable|date',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama tamu wajib diisi.',
        'instansi.required' => 'Asal instansi / perusahaan wajib diisi.',
        'employee_id.required' => 'Silakan pilih pegawai yang dituju.',
        'purpose.required' => 'Keperluan kunjungan wajib diisi.',
        'scheduled_at.date' => 'Format tanggal dan jam jadwal janji temu tidak valid.',
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

        $status = $this->is_preregistered ? 'SCHEDULED' : 'INSIDE';
        $checkInTime = $this->is_preregistered ? null : $now;
        $scheduledTime = $this->is_preregistered ? ($this->scheduled_at ? Carbon::parse($this->scheduled_at) : now()->addDay()) : null;

        $visitor = Visitor::create([
            'name' => $this->name,
            'nama_tamu' => $this->name,
            'instansi' => $this->instansi,
            'employee_id' => $this->employee_id,
            'host_tujuan' => $hostName,
            'purpose' => $this->purpose,
            'keperluan' => $this->purpose,
            'qr_code' => $qrCodePass,
            'status' => $status,
            'check_in_at' => $checkInTime,
            'waktu_checkin' => $checkInTime,
            'scheduled_at' => $scheduledTime,
            'id_card_image' => $idCardPath,
            'is_vip' => $this->is_vip,
        ]);

        if (! $this->is_preregistered && $employee && ! empty($employee->email)) {
            try {
                Mail::to($employee->email)->send(new VisitorArrivedMail($visitor, $employee));
            } catch (Throwable $e) {
                Log::warning('Gagal mengirim email notifikasi ke host: '.$e->getMessage());
            }
        }

        $this->reset(['name', 'instansi', 'employee_id', 'purpose', 'id_card_image', 'is_preregistered', 'is_vip', 'scheduled_at']);

        $this->dispatch('play-chime');

        $this->successMessage = $status === 'SCHEDULED'
            ? 'Pra-Registrasi Tamu berhasil dijadwalkan! Tamu dapat konfirmasi tiba saat sampai di lobi.'
            : 'Tamu berhasil check-in! Notifikasi email telah dikirim ke host.';
    }

    public function confirmArrival(int $id): void
    {
        $visitor = Visitor::findOrFail($id);
        $now = now();

        $visitor->update([
            'status' => 'INSIDE',
            'check_in_at' => $now,
            'waktu_checkin' => $now,
        ]);

        if ($visitor->employee && ! empty($visitor->employee->email)) {
            try {
                Mail::to($visitor->employee->email)->send(new VisitorArrivedMail($visitor, $visitor->employee));
            } catch (Throwable $e) {
                Log::warning('Gagal mengirim email notifikasi ke host: '.$e->getMessage());
            }
        }

        $this->dispatch('play-chime');

        $this->successMessage = "Tamu {$visitor->name} telah dikonfirmasi tiba di lobi!";
    }

    public function simulateAiScan(): void
    {
        $this->name = 'Hendra Pratama';
        $this->instansi = 'PT Inovasi Solusi AI';
        $this->purpose = 'Presentasi Kemitraan Digital';
        $this->successMessage = '✨ Smart Scan AI Demo: Data KTP/Kartu Nama berhasil diekstraksi ke form!';
    }

    public function checkout(int $id): void
    {
        $visitor = Visitor::findOrFail($id);

        if ($visitor->status === 'CHECKED_OUT') {
            $this->successMessage = "Tamu {$visitor->name} sudah melakukan check-out sebelumnya.";

            return;
        }

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

        $this->dispatch('play-chime');

        $this->successMessage = $durasi
            ? "Tamu {$visitor->name} berhasil check-out! Durasi kunjungan: {$durasi}."
            : "Tamu {$visitor->name} berhasil check-out.";
    }

    public function editVisitor(int $id): void
    {
        $visitor = Visitor::findOrFail($id);
        $this->editingVisitorId = $visitor->id;
        $this->edit_name = $visitor->name;
        $this->edit_instansi = $visitor->instansi;
        $this->edit_employee_id = $visitor->employee_id;
        $this->edit_purpose = $visitor->purpose;
        $this->edit_is_vip = (bool) $visitor->is_vip;
        $this->edit_scheduled_at = $visitor->scheduled_at ? $visitor->scheduled_at->format('Y-m-d\TH:i') : null;
    }

    public function updateVisitor(): void
    {
        $this->validate([
            'edit_name' => 'required|string|max:255',
            'edit_instansi' => 'required|string|max:255',
            'edit_employee_id' => 'required|exists:employees,id',
            'edit_purpose' => 'required|string|max:255',
            'edit_scheduled_at' => 'nullable|date',
        ]);

        $visitor = Visitor::findOrFail($this->editingVisitorId);
        $employee = Employee::find($this->edit_employee_id);

        $visitor->update([
            'name' => $this->edit_name,
            'nama_tamu' => $this->edit_name,
            'instansi' => $this->edit_instansi,
            'employee_id' => $this->edit_employee_id,
            'host_tujuan' => $employee?->name,
            'purpose' => $this->edit_purpose,
            'keperluan' => $this->edit_purpose,
            'is_vip' => $this->edit_is_vip,
            'scheduled_at' => $this->edit_scheduled_at ? Carbon::parse($this->edit_scheduled_at) : $visitor->scheduled_at,
        ]);

        $this->cancelEdit();
        $this->successMessage = 'Data kunjungan tamu berhasil diperbarui.';
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingVisitorId', 'edit_name', 'edit_instansi', 'edit_employee_id', 'edit_purpose', 'edit_is_vip', 'edit_scheduled_at']);
    }

    public function deleteVisitor(int $id): void
    {
        if (Auth::check() && ! Auth::user()->isAdmin()) {
            $this->errorMessage = 'Akses Terbatas: Hanya Administrator yang memiliki hak akses menghapus riwayat kunjungan tamu secara permanen.';

            return;
        }

        $visitor = Visitor::findOrFail($id);
        $name = $visitor->name;
        $visitor->delete();
        $this->successMessage = "Data kunjungan tamu {$name} berhasil dibatalkan/dihapus oleh Administrator.";
    }

    public function addEmployee(): void
    {
        $this->validate([
            'new_emp_name' => 'required|string|max:255',
            'new_emp_department' => 'required|string|max:255',
            'new_emp_email' => 'required|email|unique:employees,email',
        ]);

        $emp = Employee::create([
            'name' => $this->new_emp_name,
            'department' => $this->new_emp_department,
            'email' => $this->new_emp_email,
        ]);

        $this->employee_id = $emp->id;
        $this->reset(['new_emp_name', 'new_emp_department', 'new_emp_email', 'showEmployeeModal']);
        $this->successMessage = "Pegawai {$emp->name} berhasil ditambahkan!";
    }

    public function seedDefaultEmployees(): void
    {
        if (Employee::count() === 0) {
            Employee::insert([
                ['name' => 'Budi Santoso', 'department' => 'Human Resources (HRD)', 'email' => 'budi@company.com', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Siti Aminah', 'department' => 'Finance & Accounting', 'email' => 'siti@company.com', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Andi Pratama', 'department' => 'IT & Engineering', 'email' => 'andi@company.com', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Dewi Lestari', 'department' => 'Marketing & Partnership', 'email' => 'dewi@company.com', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Rahmat Hidayat', 'department' => 'Operations & Facility', 'email' => 'rahmat@company.com', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->successMessage = 'Data pegawai default berhasil digenerate!';
        }
    }

    public function deleteEmployee(int $id): void
    {
        $employee = Employee::findOrFail($id);
        $name = $employee->name;
        $employee->delete();
        $this->successMessage = "Pegawai {$name} berhasil dihapus dari master data.";
    }

    public function openWhatsAppModal(int $id): void
    {
        $visitor = Visitor::with('employee')->findOrFail($id);
        $hostName = $visitor->employee?->name ?? $visitor->host_tujuan ?? 'Bapak/Ibu Pegawai';
        $guestName = $visitor->name;
        $guestCompany = $visitor->instansi;
        $purpose = $visitor->purpose ?? '-';
        $pass = $visitor->qr_code ?? ('PASS-'.$visitor->id);

        if ($visitor->status === 'SCHEDULED') {
            $scheduleFormatted = $visitor->scheduled_at
                ? Carbon::parse($visitor->scheduled_at)->isoFormat('dddd, D MMMM Y - HH:mm').' WIB'
                : 'Belum ditentukan';

            $text = "Halo *{$hostName}*,\n\nInformasi Pra-Registrasi Janji Temu:\nTamu *{$guestName}* dari *{$guestCompany}* telah dijadwalkan bertemu dengan Anda pada:\n📅 *{$scheduleFormatted}*\nKeperluan: {$purpose}\nKode Pass Tamu: {$pass}\n\nTerima kasih! (LobbyFlow Enterprise)";
        } else {
            $text = "Halo *{$hostName}*,\n\nTamu Anda *{$guestName}* dari *{$guestCompany}* telah tiba di Meja Resepsionis Lobi Gedung.\n\nKeperluan: {$purpose}\nKode Pass: {$pass}\n\nMohon dapat segera menemui tamu di lobi. Terima kasih! (LobbyFlow Enterprise)";
        }

        $this->whatsAppData = [
            'visitor_id' => $visitor->id,
            'name' => $guestName,
            'host' => $hostName,
            'instansi' => $guestCompany,
            'purpose' => $purpose,
            'pass' => $pass,
            'message' => $text,
            'link' => 'https://wa.me/?text='.urlencode($text),
        ];

        $this->showWhatsAppModal = true;
    }

    public function closeWhatsAppModal(): void
    {
        $this->showWhatsAppModal = false;
        $this->whatsAppData = [];
    }

    public function openEvacuationModal(): void
    {
        $this->showEvacuationModal = true;
    }

    public function closeEvacuationModal(): void
    {
        $this->showEvacuationModal = false;
    }

    public function openFeaturesModal(): void
    {
        $this->showFeaturesModal = true;
    }

    public function closeFeaturesModal(): void
    {
        $this->showFeaturesModal = false;
    }

    /**
     * Ekspor Data Tamu Hari Ini Langsung ke File CSV (.csv / Excel)
     */
    public function exportTodayCsv(): StreamedResponse
    {
        $today = today();
        $visitors = Visitor::with('employee')
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        $filename = 'rekap-tamu-hari-ini-'.$today->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($visitors) {
            $handle = fopen('php://output', 'w');
            // Menulis UTF-8 BOM agar rapi di Microsoft Excel Windows
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No',
                'Kode Pass',
                'Nama Tamu',
                'Asal Instansi',
                'Pegawai Dituju (Host)',
                'Departemen',
                'Keperluan',
                'Status',
                'Waktu Check-In',
                'Waktu Check-Out',
                'Durasi Kunjungan',
            ]);

            $i = 1;
            foreach ($visitors as $v) {
                $checkIn = $v->waktu_checkin ? Carbon::parse($v->waktu_checkin)->format('d/m/Y H:i') : ($v->check_in_at ? Carbon::parse($v->check_in_at)->format('d/m/Y H:i') : '-');
                $checkOut = $v->waktu_checkout ? Carbon::parse($v->waktu_checkout)->format('d/m/Y H:i') : ($v->check_out_at ? Carbon::parse($v->check_out_at)->format('d/m/Y H:i') : '-');

                fputcsv($handle, [
                    $i++,
                    $v->qr_code ?? ('PASS-'.$v->id),
                    $v->name ?? $v->nama_tamu,
                    $v->instansi,
                    $v->employee?->name ?? $v->host_tujuan ?? '-',
                    $v->employee?->department ?? '-',
                    $v->purpose ?? $v->keperluan ?? '-',
                    $v->status,
                    $checkIn,
                    $checkOut,
                    $v->durasi_kunjungan ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function editAnnouncement(): void
    {
        $this->newAnnouncement = $this->announcement;
        $this->isEditingAnnouncement = true;
    }

    public function saveAnnouncement(): void
    {
        if (trim($this->newAnnouncement) !== '') {
            $this->announcement = trim($this->newAnnouncement);
            $this->successMessage = 'Pengumuman lobi berhasil diperbarui!';
        }
        $this->isEditingAnnouncement = false;
    }

    public function cancelEditAnnouncement(): void
    {
        $this->isEditingAnnouncement = false;
    }

    public function generateDemoVisitors(): void
    {
        $this->seedDefaultEmployees();

        $employees = Employee::all();
        if ($employees->isEmpty()) {
            return;
        }

        $now = now();
        $demos = [
            [
                'name' => 'Dimas Surya, S.T.',
                'instansi' => 'Google Indonesia',
                'employee_id' => $employees[0]->id,
                'host' => $employees[0]->name,
                'purpose' => 'Presentasi Integrasi Cloud Enterprise',
                'status' => 'INSIDE',
                'is_vip' => true,
                'check_in' => $now->copy()->subMinutes(25),
                'check_out' => null,
                'durasi' => null,
            ],
            [
                'name' => 'Dr. Ratna Kusuma',
                'instansi' => 'PT Bank Central Asia Tbk',
                'employee_id' => $employees[1]->id ?? $employees[0]->id,
                'host' => $employees[1]->name ?? $employees[0]->name,
                'purpose' => 'Koordinasi Kerjasama Payroll Perusahaan',
                'status' => 'INSIDE',
                'check_in' => $now->copy()->subMinutes(50),
                'check_out' => null,
                'durasi' => null,
            ],
            [
                'name' => 'Agus Wicaksono',
                'instansi' => 'PT Telkomsel Regional',
                'employee_id' => $employees[2]->id ?? $employees[0]->id,
                'host' => $employees[2]->name ?? $employees[0]->name,
                'purpose' => 'Maintenance Jaringan Fiber Optik Lobi',
                'status' => 'CHECKED_OUT',
                'check_in' => $now->copy()->subHours(2),
                'check_out' => $now->copy()->subMinutes(30),
                'durasi' => '1 jam 30 menit',
            ],
            [
                'name' => 'Clarissa Stephanie',
                'instansi' => 'Kementerian Investasi / BKPM',
                'employee_id' => $employees[3]->id ?? $employees[0]->id,
                'host' => $employees[3]->name ?? $employees[0]->name,
                'purpose' => 'Audit Dokumen Legalitas Fasilitas',
                'status' => 'SCHEDULED',
                'check_in' => null,
                'check_out' => null,
                'durasi' => null,
                'scheduled_at' => $now->copy()->addDay()->setHour(10)->setMinute(30),
            ],
            [
                'name' => 'Bambang Trihatmojo',
                'instansi' => 'PT Astra International Tbk',
                'employee_id' => $employees[4]->id ?? $employees[0]->id,
                'host' => $employees[4]->name ?? $employees[0]->name,
                'purpose' => 'Pertemuan Evaluasi Pengadaan Armada',
                'status' => 'CHECKED_OUT',
                'check_in' => $now->copy()->subHours(3),
                'check_out' => $now->copy()->subHours(1),
                'durasi' => '2 jam',
            ],
        ];

        foreach ($demos as $demo) {
            Visitor::create([
                'name' => $demo['name'],
                'nama_tamu' => $demo['name'],
                'instansi' => $demo['instansi'],
                'employee_id' => $demo['employee_id'],
                'host_tujuan' => $demo['host'],
                'purpose' => $demo['purpose'],
                'keperluan' => $demo['purpose'],
                'qr_code' => 'PASS-'.strtoupper(uniqid()),
                'status' => $demo['status'],
                'check_in_at' => $demo['check_in'],
                'waktu_checkin' => $demo['check_in'],
                'check_out_at' => $demo['check_out'],
                'waktu_checkout' => $demo['check_out'],
                'scheduled_at' => $demo['scheduled_at'] ?? null,
                'durasi' => $demo['durasi'],
                'is_vip' => $demo['is_vip'] ?? false,
            ]);
        }

        $this->successMessage = '✨ 5 Data Tamu Contoh Berhasil Ditambahkan! Lobi kini ramai dengan tamu aktif, terjadwal, dan riwayat selesai.';
    }

    public function quickCheckout(): void
    {
        $code = trim($this->quickPassCode);
        if (empty($code)) {
            $this->errorMessage = 'Silakan masukkan atau scan Kode Pass Tamu terlebih dahulu.';

            return;
        }

        // Cari visitor berdasarkan qr_code atau ID
        $visitor = Visitor::where('qr_code', $code)
            ->orWhere('id', ltrim($code, 'PASS-'))
            ->orWhere('qr_code', 'like', "%{$code}%")
            ->first();

        if (! $visitor) {
            $this->errorMessage = "Kode Pass '{$code}' tidak ditemukan di sistem.";
            $this->quickPassCode = '';

            return;
        }

        if ($visitor->status === 'CHECKED_OUT') {
            $this->errorMessage = "Tamu {$visitor->name} ({$visitor->qr_code}) sudah melakukan check-out sebelumnya.";
            $this->quickPassCode = '';

            return;
        }

        $this->checkout($visitor->id);
        $this->quickPassCode = '';
    }

    public function previewIdCard(int $id): void
    {
        $visitor = Visitor::findOrFail($id);
        if ($visitor->id_card_image) {
            $this->previewImageUrl = Storage::url($visitor->id_card_image);
        }
    }

    public function closePreview(): void
    {
        $this->previewImageUrl = null;
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
            'id_card_image' => 'nullable|image|max:3072',
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

        if ($visitor->status === 'CHECKED_OUT') {
            return redirect()->back()->with('error', "Tamu {$visitor->name} sudah melakukan check-out sebelumnya.");
        }

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

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedVisitors = Visitor::where('status', 'INSIDE')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedVisitors = [];
        }
    }

    public function batchCheckout(): void
    {
        if (empty($this->selectedVisitors)) {
            $this->errorMessage = 'Pilih minimal satu tamu yang berada di dalam gedung untuk check-out massal.';

            return;
        }

        $count = 0;
        foreach ($this->selectedVisitors as $id) {
            $visitor = Visitor::find($id);
            if ($visitor && $visitor->status === 'INSIDE') {
                $this->checkout((int) $id);
                $count++;
            }
        }

        $this->selectedVisitors = [];
        $this->selectAll = false;
        $this->successMessage = "✨ Berhasil melakukan check-out massal untuk {$count} tamu!";
    }

    public function toggleVip(int $id): void
    {
        $visitor = Visitor::findOrFail($id);
        $visitor->update(['is_vip' => ! $visitor->is_vip]);
        $status = $visitor->is_vip ? 'VIP (Tamu Prioritas)' : 'Reguler';
        $this->successMessage = "Status tamu {$visitor->name} diubah menjadi {$status}.";
    }

    public function openWifiModal(): void
    {
        $this->showWifiModal = true;
    }

    public function closeWifiModal(): void
    {
        $this->showWifiModal = false;
    }

    public function openCameraModal(): void
    {
        $this->showCameraModal = true;
    }

    public function closeCameraModal(): void
    {
        $this->showCameraModal = false;
    }

    public function toggleKioskMode(): void
    {
        $this->kioskMode = ! $this->kioskMode;
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
            ->when($this->statusFilter === 'VIP', function ($query) {
                $query->where('is_vip', true);
            })
            ->when($this->statusFilter !== 'ALL' && $this->statusFilter !== 'VIP', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        $employees = Employee::orderBy('name')->get();

        $totalToday = Visitor::whereDate('created_at', today())->count();
        $checkedOutToday = Visitor::whereDate('created_at', today())->where('status', 'CHECKED_OUT')->count();
        $checkoutRate = $totalToday > 0 ? round(($checkedOutToday / $totalToday) * 100) : 100;
        $totalThisMonth = Visitor::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();

        $stats = [
            'total_today' => $totalToday,
            'inside_now' => Visitor::where('status', 'INSIDE')->count(),
            'scheduled' => Visitor::where('status', 'SCHEDULED')->count(),
            'checked_out_today' => $checkedOutToday,
            'vip_count' => Visitor::where('is_vip', true)->where('status', 'INSIDE')->count(),
            'total_employees' => $employees->count(),
            'checkout_rate' => $checkoutRate,
            'total_this_month' => $totalThisMonth,
        ];

        $evacuationVisitors = $this->showEvacuationModal
            ? Visitor::with('employee')->where('status', 'INSIDE')->orderBy('check_in_at', 'asc')->get()
            : collect();

        return view('livewire.lobby-dashboard', compact('visitors', 'employees', 'stats', 'evacuationVisitors'));
    }
}

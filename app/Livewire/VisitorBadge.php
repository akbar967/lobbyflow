<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\QrCodeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class VisitorBadge extends Component
{
    public Visitor $visitor;

    public string $qrCodeSvg = '';

    public function mount(int $id, QrCodeService $qrCodeService): void
    {
        $this->visitor = Visitor::with('employee')->findOrFail($id);
        $qrData = $this->visitor->qr_code ?: ('PASS-'.$this->visitor->id);
        $this->qrCodeSvg = $qrCodeService->generateSvg($qrData, 140);
    }

    public function render()
    {
        return view('livewire.visitor-badge')
            ->title('Cetak Badge Tamu - '.$this->visitor->name);
    }
}

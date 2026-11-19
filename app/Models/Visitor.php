<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nama_tamu',
        'instansi',
        'employee_id',
        'host_tujuan',
        'purpose',
        'keperluan',
        'qr_code',
        'status',
        'check_in_at',
        'waktu_checkin',
        'check_out_at',
        'waktu_checkout',
        'durasi',
        'id_card_image',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'waktu_checkin' => 'datetime',
        'check_out_at' => 'datetime',
        'waktu_checkout' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getNamaTamuAttribute(?string $value): ?string
    {
        return $value ?? $this->name;
    }

    public function getHostTujuanAttribute(?string $value): ?string
    {
        return $value ?? ($this->employee?->name ?? '-');
    }

    public function getKeperluanAttribute(?string $value): ?string
    {
        return $value ?? $this->purpose;
    }

    public function getWaktuCheckinAttribute($value): ?Carbon
    {
        $val = $value ?? ($this->attributes['check_in_at'] ?? null);

        return $val ? Carbon::parse($val) : null;
    }

    public function getWaktuCheckoutAttribute($value): ?Carbon
    {
        $val = $value ?? ($this->attributes['check_out_at'] ?? null);

        return $val ? Carbon::parse($val) : null;
    }

    public function getDurasiKunjunganAttribute(): ?string
    {
        if ($this->durasi) {
            return $this->durasi;
        }

        $checkIn = $this->waktu_checkin ?? $this->check_in_at;
        $checkOut = $this->waktu_checkout ?? $this->check_out_at;

        if (! $checkIn || ! $checkOut) {
            return null;
        }

        $cIn = Carbon::parse($checkIn);
        $cOut = Carbon::parse($checkOut);

        $diffInMinutes = $cIn->diffInMinutes($cOut);
        $hours = intdiv($diffInMinutes, 60);
        $minutes = $diffInMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$minutes} menit";
    }
}

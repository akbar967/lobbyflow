<?php

use App\Services\OcrService;
use Tests\TestCase;

uses(TestCase::class);

test('ocr service extracts name and institution from ktp text accurately', function () {
    $service = new OcrService;

    $sampleKtpText = <<<'TXT'
PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA
JAKARTA SELATAN
NIK : 3174051208920001
NAMA : HENDRA PRATAMA
TEMPAT/TGL LAHIR : JAKARTA, 12-08-1992
JENIS KELAMIN : LAKI-LAKI
ALAMAT : JL. SUDIRMAN NO. 45
RT/RW : 002/005
KEL/DESA : KARET SEMANGGI
KECAMATAN : SETIABUDI
AGAMA : ISLAM
STATUS PERKAWINAN : BELUM KAWIN
PEKERJAAN : PT SOLUSI INOVASI DIGITAL
KEWARGANEGARAAN : WNI
BERLAKU HINGGA : SEUMUR HIDUP
TXT;

    $result = $service->extractKtpFields($sampleKtpText);

    expect($result['name'])->toBe('Hendra Pratama')
        ->and($result['instansi'])->toBe('Pt Solusi Inovasi Digital')
        ->and($result['purpose'])->toBe('Koordinasi Bisnis');
});

test('ocr service handles sample file path fallback gracefully', function () {
    $service = new OcrService;

    $result = $service->scanKtp('/path/to/ktp-bambang.jpg');

    expect($result)->toHaveKeys(['name', 'instansi', 'purpose', 'raw_text'])
        ->and($result['name'])->not->toBeEmpty();
});

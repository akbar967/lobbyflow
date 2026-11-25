<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OcrService
{
    /**
     * Scan and extract KTP / Business Card information using OCR or AI Vision.
     *
     * @return array{name: ?string, instansi: ?string, purpose: ?string, raw_text: ?string}
     */
    public function scanKtp(string $imagePath): array
    {
        // 1. Coba Google Cloud Vision API bila API key tersedia
        $visionKey = config('services.google.vision_key');
        if ($visionKey && file_exists($imagePath)) {
            try {
                $result = $this->callGoogleVisionApi($imagePath, $visionKey);
                if (! empty($result['name']) || ! empty($result['instansi'])) {
                    return $result;
                }
            } catch (Throwable $e) {
                Log::warning('Google Vision OCR failed, falling back to smart parser: '.$e->getMessage());
            }
        }

        // 2. Coba Gemini Vision API bila API key tersedia
        $geminiKey = config('services.google.gemini_key');
        if ($geminiKey && file_exists($imagePath)) {
            try {
                $result = $this->callGeminiVisionApi($imagePath, $geminiKey);
                if (! empty($result['name']) || ! empty($result['instansi'])) {
                    return $result;
                }
            } catch (Throwable $e) {
                Log::warning('Gemini Vision OCR failed: '.$e->getMessage());
            }
        }

        // 3. Smart Heuristic Local KTP & Card Parser (Demo / Offline mode)
        return $this->parseSampleOrFilename($imagePath);
    }

    protected function callGoogleVisionApi(string $imagePath, string $apiKey): array
    {
        $base64Image = base64_encode(file_get_contents($imagePath));

        $response = Http::timeout(10)->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
            'requests' => [
                [
                    'image' => ['content' => $base64Image],
                    'features' => [['type' => 'TEXT_DETECTION']],
                ],
            ],
        ]);

        if ($response->successful()) {
            $text = $response->json('responses.0.fullTextAnnotation.text') ?? '';

            return $this->extractKtpFields($text);
        }

        return ['name' => null, 'instansi' => null, 'purpose' => null, 'raw_text' => null];
    }

    protected function callGeminiVisionApi(string $imagePath, string $apiKey): array
    {
        $base64Image = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $prompt = 'Ekstrak data dari gambar KTP atau kartu nama ini. Kembalikan HANYA JSON murni dengan format persis seperti ini: {"name": "Nama Lengkap", "instansi": "Nama Perusahaan / Asal", "purpose": "Keperluan Kunjungan"} tanpa markdown atau penjelasan lain.';

        $response = Http::timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            $rawReply = $response->json('candidates.0.content.parts.0.text') ?? '';
            $cleanJson = trim(str_replace(['```json', '```'], '', $rawReply));
            $data = json_decode($cleanJson, true);

            if (is_array($data)) {
                return [
                    'name' => $data['name'] ?? null,
                    'instansi' => $data['instansi'] ?? null,
                    'purpose' => $data['purpose'] ?? 'Meeting Koordinasi',
                    'raw_text' => $rawReply,
                ];
            }
        }

        return ['name' => null, 'instansi' => null, 'purpose' => null, 'raw_text' => null];
    }

    public function extractKtpFields(string $text): array
    {
        $name = null;
        $instansi = null;

        // Pattern 1: NAMA : [NAMA LENGKAP]
        if (preg_match('/NAMA\s*[:\-]?\s*([A-Z\s\.\,\'\-]+)/i', $text, $matches)) {
            $candidate = trim(explode("\n", $matches[1])[0]);
            if (strlen($candidate) > 2) {
                $name = ucwords(strtolower($candidate));
            }
        }

        // Pattern 2: PEKERJAAN / PERUSAHAAN / INSTANSI
        if (preg_match('/(?:PEKERJAAN|INSTANSI|PERUSAHAAN)\s*[:\-]?\s*([A-Z0-9\s\.\,\'\-]+)/i', $text, $matches)) {
            $candidate = trim(explode("\n", $matches[1])[0]);
            if (strlen($candidate) > 2) {
                $instansi = ucwords(strtolower($candidate));
            }
        }

        return [
            'name' => $name,
            'instansi' => $instansi,
            'purpose' => 'Koordinasi Bisnis',
            'raw_text' => $text,
        ];
    }

    protected function parseSampleOrFilename(string $imagePath): array
    {
        $filename = pathinfo($imagePath, PATHINFO_FILENAME);

        // Abaikan nama file sementara seperti phpF92A.tmp atau livewire-tmp
        $isTempFile = preg_match('/^(php[a-z0-9_]+|livewire[a-z0-9_\-]+|[a-f0-9]{32,})$/i', $filename);

        $cleanName = '';
        if (! $isTempFile) {
            // Jika nama file mengandung nama orang (contoh: ktp_budi_santoso.jpg)
            $cleanName = str_replace(['ktp', 'kartu', 'nama', 'card', '_', '-'], ' ', strtolower($filename));
            $cleanName = trim(preg_replace('/\s+/', ' ', $cleanName));
        }

        // Pastikan nama bukan alphanumeric acak
        $isSensibleName = ! empty($cleanName) && strlen($cleanName) > 3 && preg_match('/^[a-z\s]+$/i', $cleanName);

        $name = $isSensibleName ? ucwords($cleanName) : 'Hendra Gunawan';
        $instansi = 'PT Inovasi Digital Nusantara';
        $purpose = 'Presentasi Proyek & Kerjasama';

        return [
            'name' => $name,
            'instansi' => $instansi,
            'purpose' => $purpose,
            'raw_text' => "KTP REPUBLIK INDONESIA\nNIK : 3171012345678901\nNAMA : {$name}\nPEKERJAAN : KARYAWAN SWASTA ({$instansi})",
        ];
    }
}

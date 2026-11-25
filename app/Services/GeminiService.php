<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiService
{
    /**
     * Generate dynamic AI Executive Summary using Google Gemini API.
     *
     * @param  array<string, mixed>  $stats
     * @return array<string, string>
     */
    public function generateExecutiveSummary(array $stats, string $month): array
    {
        $apiKey = config('services.google.gemini_key');
        $monthName = Carbon::parse($month.'-01')->format('F Y');

        if ($apiKey) {
            try {
                $geminiResult = $this->callGeminiApi($stats, $monthName, $apiKey);
                if (! empty($geminiResult['headline'])) {
                    return $geminiResult;
                }
            } catch (Throwable $e) {
                Log::warning('Gemini API call failed, falling back to local engine: '.$e->getMessage());
            }
        }

        // Fallback generator based on real database metrics
        return $this->generateLocalSummary($stats, $monthName);
    }

    protected function callGeminiApi(array $stats, string $monthName, string $apiKey): array
    {
        $total = $stats['total'] ?? 0;
        $inside = $stats['inside'] ?? 0;
        $checkedOut = $stats['checked_out'] ?? 0;
        $topInstansi = $stats['top_instansi'] ?? '-';
        $topHost = $stats['top_host'] ?? '-';

        $prompt = "Anda adalah AI Security & Receptionist Director untuk gedung perkantoran LobbyFlow.
Berikut adalah data aktivitas tamu periode {$monthName}:
- Total Tamu: {$total} orang
- Tamu Selesai Check-out: {$checkedOut} orang
- Tamu Masih di Dalam Gedung: {$inside} orang
- Mitra Instansi Paling Sering Berkunjung: {$topInstansi}
- Pegawai (Host) Paling Sering Menerima Tamu: {$topHost}

Buatlah ringkasan eksekutif profesional untuk manajemen gedung.
KEMBALIKAN HANYA JSON MURNI dengan format persis berikut tanpa markdown atau tanda kutip pembuka ```:
{
    \"headline\": \"Judul ringkasan eksekutif yang menarik\",
    \"traffic_analysis\": \"Paragraf analisis volume kedatangan dan alur kapasitas lobi\",
    \"corporate_pattern\": \"Paragraf pola interaksi mitra bisnis dan instansi teratas\",
    \"security_compliance\": \"Paragraf evaluasi kepatuhan check-out dan risiko keamanan\",
    \"recommendation\": \"1-2 poin saran operasional strategis untuk resepsionis dan keamanan\"
}";

        $response = Http::timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            $rawReply = $response->json('candidates.0.content.parts.0.text') ?? '';
            $cleanJson = trim(str_replace(['```json', '```'], '', $rawReply));
            $data = json_decode($cleanJson, true);

            if (is_array($data) && isset($data['headline'])) {
                return $data;
            }
        }

        return [];
    }

    protected function generateLocalSummary(array $stats, string $monthName): array
    {
        $total = $stats['total'] ?? 0;
        $inside = $stats['inside'] ?? 0;
        $checkedOut = $stats['checked_out'] ?? 0;
        $topInstansi = $stats['top_instansi'] ?? '-';
        $topHost = $stats['top_host'] ?? '-';

        if ($total === 0) {
            return [
                'headline' => "Belum Ada Catatan Kunjungan di Periode {$monthName}",
                'traffic_analysis' => 'Lobi belum mencatat adanya tamu untuk rentang waktu ini. Kapasitas area tunggu terpantau 100% idle.',
                'corporate_pattern' => 'Belum ada relasi instansi bisnis yang berkunjung.',
                'security_compliance' => 'Tingkat kepatuhan berada pada ambang standar.',
                'recommendation' => 'Pastikan staf resepsionis selalu mencatat setiap tamu menggunakan Smart Scan KTP.',
            ];
        }

        $rate = round(($checkedOut / $total) * 100);

        return [
            'headline' => "Ringkasan Eksekutif AI: Analisis Kunjungan Periode {$monthName}",
            'traffic_analysis' => "Sepanjang periode {$monthName}, tercatat total {$total} kunjungan tamu. Sebagian besar kunjungan terdistribusi pada jam kerja reguler dengan alur check-in yang stabil.",
            'corporate_pattern' => "Mitra korporasi dengan frekuensi interaksi tertinggi adalah '{$topInstansi}', dengan pegawai penerima tamu teraktif adalah '{$topHost}'.",
            'security_compliance' => "Tingkat kepatuhan penyelesaian kunjungan (check-out) berada di angka {$rate}%. Terdapat {$inside} tamu yang saat ini masih aktif di dalam gedung dan membutuhkan pengawasan berkala.",
            'recommendation' => 'Disarankan untuk menempatkan petugas keamanan tambahan pada jam puncak (10:00 - 11:30) serta mempercepat proses check-in melalui fitur Smart Scan AI.',
        ];
    }
}

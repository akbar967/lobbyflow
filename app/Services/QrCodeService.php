<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Throwable;

class QrCodeService
{
    /**
     * Generate an SVG string of the QR Code.
     */
    public function generateSvg(string $data, int $size = 180): string
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle($size, 1),
                new SvgImageBackEnd
            );

            $writer = new Writer($renderer);

            return $writer->writeString($data);
        } catch (Throwable $e) {
            // Fallback: minimal inline SVG with text or API if exception occurs
            $safeData = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

            return "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 {$size} {$size}\"><rect width=\"100%\" height=\"100%\" fill=\"#f8fafc\"/><text x=\"50%\" y=\"50%\" font-size=\"14\" text-anchor=\"middle\" fill=\"#4f46e5\" font-family=\"sans-serif\">{$safeData}</text></svg>";
        }
    }

    /**
     * Generate a Base64 data URI of the QR Code SVG.
     */
    public function generateDataUri(string $data, int $size = 180): string
    {
        $svg = $this->generateSvg($data, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}

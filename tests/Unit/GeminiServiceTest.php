<?php

use App\Services\GeminiService;
use Tests\TestCase;

uses(TestCase::class);

test('gemini service generates structured executive summary from statistics', function () {
    $service = new GeminiService;

    $stats = [
        'total' => 45,
        'inside' => 5,
        'checked_out' => 40,
        'top_instansi' => 'PT Telkom Indonesia',
        'top_host' => 'Budi Santoso',
    ];

    $summary = $service->generateExecutiveSummary($stats, '2026-11');

    expect($summary)->toHaveKeys([
        'headline',
        'traffic_analysis',
        'corporate_pattern',
        'security_compliance',
        'recommendation',
    ])
        ->and($summary['headline'])->not->toBeEmpty()
        ->and($summary['traffic_analysis'])->toContain('45');
});

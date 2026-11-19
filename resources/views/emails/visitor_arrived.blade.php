<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Tamu Tiba</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            opacity: 0.85;
        }
        .content {
            padding: 28px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
        }
        .alert-box {
            background-color: #eef2ff;
            border-left: 4px solid #4f46e5;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 15px;
            line-height: 1.5;
            color: #312e81;
        }
        .card-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .card-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .card-table td.label {
            width: 35%;
            font-weight: 600;
            color: #64748b;
        }
        .card-table td.value {
            color: #0f172a;
            font-weight: 500;
        }
        .footer {
            background-color: #f8fafc;
            padding: 16px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🏢 LobbyFlow Notification</h1>
            <p>Sistem Pemantauan Tamu & Resepsionis</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Halo, {{ $employee->name }}!</p>

            <div class="alert-box">
                📢 <strong>Ada tamu atas nama <span style="text-decoration: underline;">{{ $visitor->name }}</span> dari <span style="text-decoration: underline;">{{ $visitor->instansi }}</span> sedang menunggu Anda di Lobi.</strong>
            </div>

            <table class="card-table">
                <tr>
                    <td class="label">Nama Tamu</td>
                    <td class="value"><strong>{{ $visitor->name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Asal Instansi</td>
                    <td class="value">{{ $visitor->instansi }}</td>
                </tr>
                <tr>
                    <td class="label">Keperluan</td>
                    <td class="value">{{ $visitor->purpose ?? $visitor->keperluan ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Check-In</td>
                    <td class="value">
                        @php
                            $waktu = $visitor->waktu_checkin ?? $visitor->check_in_at;
                            $formatted = $waktu ? \Illuminate\Support\Carbon::parse($waktu)->format('d M Y, H:i') : now()->format('d M Y, H:i');
                        @endphp
                        {{ $formatted }} WIB
                    </td>
                </tr>
                <tr>
                    <td class="label">Kode ID Pass</td>
                    <td class="value"><code style="background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #4f46e5;">{{ $visitor->qr_code ?? 'PASS-' . $visitor->id }}</code></td>
                </tr>
            </table>

            <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0;">
                Silakan menemui tamu di resepsionis lobi utama atau hubungi staf lobi bila Anda memerlukan bantuan tambahan.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            Pemberitahuan otomatis oleh LobbyFlow &bull; Mohon tidak membalas email ini secara langsung.
        </div>
    </div>
</body>
</html>

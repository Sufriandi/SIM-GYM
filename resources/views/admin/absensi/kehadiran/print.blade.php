{{-- resources/views/admin/absensi/kehadiran/print.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Absensi – BETA GYM</title>

    <style>
        @page {
            size: A4;
            margin: 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f3f3;
            font-family: system-ui, -apple-system, BlinkMacSystemFont,
                "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f1f1f;
        }

        .sheet {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 0 0.8cm rgba(0, 0, 0, 0.08);
            padding: 24px 28px 30px;
            min-height: calc(297mm - 36mm);
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            object-fit: cover;
            border: 2px solid #d4a757;
        }

        .brand-name {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .brand-sub {
            font-size: 10px;
            color: #6b7280;
        }

        .header-right {
            text-align: right;
        }

        .tag {
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #d4a757;
        }

        .header-note {
            font-size: 10px;
            margin-top: 4px;
            color: #4b5563;
        }

        .card {
            margin-top: 6px;
            border-radius: 18px;
            border: 1px solid #f0e2cc;
            background: #faf3e8;
            padding: 26px 30px;
            display: flex;
            flex: 1;
            gap: 28px;
        }

        .card-left {
            flex: 1.4;
            padding-right: 12px;
        }

        .card-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .label-sm {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.20em;
            text-transform: uppercase;
            color: #374151;
        }

        .period-title {
            font-size: 20px;
            font-weight: 800;
            margin: 10px 0 12px;
        }

        .period-range {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .meta {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
        }

        .meta + .meta {
            margin-top: 10px;
        }

        .qr-wrapper {
            padding: 14px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 0 0 1px #e5e7eb;
        }

        .qr-wrapper img {
            display: block;
            width: 220px;
            height: 220px;
        }

        .qr-caption {
            margin-top: 8px;
            font-size: 10px;
            text-align: center;
            color: #4b5563;
            max-width: 240px;
        }

        .footer {
            margin-top: 18px;
            font-size: 9px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer-right {
            text-align: right;
        }

        .btn-print {
            position: fixed;
            right: 18px;
            top: 16px;
            padding: 6px 14px;
            font-size: 11px;
            border-radius: 999px;
            border: none;
            background: #111827;
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.18);
        }

        .btn-print span {
            margin-left: 6px;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .sheet {
                box-shadow: none;
                margin: 0;
                min-height: auto;
            }

            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">
    🖨 <span>Cetak</span>
</button>

<div class="sheet">
    {{-- HEADER --}}
    <div class="header">
        <div class="brand-left">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="Logo BETA GYM"
                class="brand-logo"
            >
            <div>
                <div class="brand-name">BETA GYM</div>
                <div class="brand-sub">Sistem Absensi Anggota</div>
            </div>
        </div>

        <div class="header-right">
            <div class="tag">Kartu QR Absensi</div>
            <div class="header-note">
                Tempel di area resepsionis / pintu masuk untuk proses check-in member.
            </div>
        </div>
    </div>

    {{-- KARTU QR --}}
    <div class="card">
        {{-- Kiri: info periode --}}
        <div class="card-left">
            <div class="label-sm">
                Periode Absensi Aktif ({{ strtoupper($periodeAktif->tipe_periode) }})
            </div>

            <div class="period-title">
                Scan untuk Kehadiran Latihan
            </div>

            <div class="period-range">
                {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d M Y') }}
                –
                {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d M Y') }}
            </div>

            <div class="meta">
                <strong>Kode QR:</strong> {{ $periodeAktif->kode_qr }}<br>
                Kartu ini berlaku selama periode tanggal di atas. Setiap scan yang
                dilakukan member akan tercatat sebagai kehadiran pada periode ini.
            </div>

            <div class="meta">
                Mintalah member untuk membuka menu <strong>Kehadiran</strong> pada
                aplikasi SIM-GYM lalu arahkan kamera ke kode QR ini. Tidak perlu login
                tambahan di sisi admin.
            </div>

            <div class="meta">
                Disarankan mencetak menggunakan kertas ukuran A4 dengan laminasi ringan
                agar kartu lebih awet saat ditempel di meja resepsionis atau dekat pintu masuk.
            </div>
        </div>

        {{-- Kanan: QR code --}}
        <div class="card-right">
            <div class="qr-wrapper">
                <img
                    src="data:image/png;base64,{{ base64_encode(
                        QrCode::format('png')
                            ->size(400)
                            ->margin(1)
                            ->generate($qrUrl)
                    ) }}"
                    alt="QR Absensi BETA GYM"
                >
            </div>
            <div class="qr-caption">
                Scan dengan aplikasi SIM-GYM pada menu Kehadiran
                untuk mencatat kehadiran latihan Anda.
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div>
            Dicetak oleh: BETA GYM<br>
            Tanggal cetak: {{ now()->translatedFormat('d M Y H:i') }}
        </div>
        <div class="footer-right">
            <div>Kode kartu: ABS-{{ str_pad($periodeAktif->id, 4, '0', STR_PAD_LEFT) }}</div>
            <div>Jika kartu rusak / hilang, silakan cetak ulang dari halaman absensi admin.</div>
        </div>
    </div>
</div>

</body>
</html>

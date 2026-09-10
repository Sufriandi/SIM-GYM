{{--
    resources/views/admin/laporan/partials/pdf_header.blade.php
    Partial header dipakai di semua template PDF laporan (Kehadiran & Keuangan).
--}}
@php
    use Carbon\Carbon;

    $periodeAwalLabel  = $periodeAwal ? Carbon::parse($periodeAwal)->translatedFormat('d M Y') : '-';
    $periodeAkhirLabel = $periodeAkhir ? Carbon::parse($periodeAkhir)->translatedFormat('d M Y') : '-';
@endphp

<div style="border-bottom: 2.5px solid #c59b27; padding-bottom: 8px; margin-bottom: 14px;">
    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
        <tr>
            <td style="border: none; padding: 0; vertical-align: top; text-align: left;">
                <div style="font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; text-transform: uppercase;">BETA GYM</div>
                <div style="font-size: 12px; font-weight: 700; color: #334155; margin-top: 2px;">{{ $reportTitle }}</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 1px;">Sistem Informasi Manajemen BETA GYM</div>
            </td>
            <td style="border: none; padding: 0; vertical-align: top; text-align: right; width: 45%;">
                <div style="display: inline-block; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px 8px; text-align: right;">
                    <div style="font-size: 9.5px; font-weight: 700; color: #1e293b;">
                        Periode: {{ $periodeAwalLabel }} &ndash; {{ $periodeAkhirLabel }}
                    </div>
                    <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                        Dicetak: {{ now()->translatedFormat('d M Y, H:i') }} WIB
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

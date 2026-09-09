{{--
    resources/views/admin/laporan/partials/pdf_header.blade.php

    Partial header dipakai di semua template PDF laporan (Kehadiran & Keuangan).
    Include dengan:
    @include('admin.laporan.partials.pdf_header', [
        'reportTitle' => 'Laporan Keuangan - Produk',
        'periodeAwal' => $fromDate,
        'periodeAkhir' => $toDate,
    ])
--}}
@php
    use Carbon\Carbon;

    $periodeAwalLabel  = Carbon::parse($periodeAwal)->translatedFormat('d M Y');
    $periodeAkhirLabel = Carbon::parse($periodeAkhir)->translatedFormat('d M Y');
@endphp
<div style="border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 14px;">
    <div style="font-size: 18px; font-weight: 700; letter-spacing: 1px;">BETA GYM</div>
    <div style="font-size: 13px; font-weight: 600; margin-top: 2px;">{{ $reportTitle }}</div>
    <div style="font-size: 11px; color: #555; margin-top: 2px;">
        Periode: {{ $periodeAwalLabel }} &ndash; {{ $periodeAkhirLabel }}
    </div>
</div>

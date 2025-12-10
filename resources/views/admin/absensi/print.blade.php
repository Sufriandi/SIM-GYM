{{-- resources/views/admin/absensi/print.blade.php --}}
@php
    $pageTitle = $pageTitle ?? 'Kartu Absensi Member';
    $gymName   = 'BETA GYM';

    // Ambil 8 karakter pertama kode_qr untuk ID kartu tampilan
    $qrSnippet = isset($periodeAktif->kode_qr)
        ? strtoupper(substr($periodeAktif->kode_qr, 0, 8))
        : 'UNKNOWN';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle }} – {{ $gymName }}</title>

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <style>
        body {
            background: #e5e7eb; /* gray-200 */
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                background: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">

<div class="min-h-screen flex items-center justify-center py-8 px-4">
    {{-- KARTU ABSENSI --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-200 w-full max-w-sm overflow-hidden">

        {{-- Strip atas --}}
        <div class="h-3 bg-gradient-to-r from-amber-400 via-amber-500 to-rose-500"></div>

        <div class="px-6 pt-6 pb-6 flex flex-col items-center gap-4">

            {{-- Header --}}
            <div class="w-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Logo  --}}
                    <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-900 flex items-center justify-center">
                        <img
                            src="{{ asset('images/Logo.png') }}"
                            alt="Logo {{ $gymName }}"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <div class="leading-tight">
                        <p class="text-[10px] text-gray-500 uppercase tracking-[0.16em]">
                            Kartu
                        </p>
                        <p class="text-xs font-semibold text-gray-900">
                            Absensi Member
                        </p>
                    </div>
                </div>
                <div class="text-right text-[10px] text-gray-500 leading-tight">
                    <p class="font-semibold text-xs text-gray-800">
                        {{ $gymName }}
                    </p>
                </div>
            </div>

            {{-- Judul Tengah --}}
            <div class="text-center mt-2">
                <p class="text-[11px] tracking-[0.2em] text-gray-400 uppercase">
                    Absensi
                </p>
                <p class="text-lg font-semibold text-gray-900 tracking-tight">
                    QR Absensi Member
                </p>
            </div>

            {{-- QR Code --}}
            <div class="mt-2 mb-1 bg-gray-100 rounded-2xl p-3 border border-gray-200">
                <div class="bg-white p-3 rounded-xl border border-gray-300">
                    {!! QrCode::size(220)->margin(1)->generate($qrUrl) !!}
                </div>
            </div>

            {{-- Step singkat (versi baru) --}}
            <div class="mt-3 flex items-center justify-center gap-2 text-[10px] text-gray-600">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                    Scan QR
                </span>
                <span class="text-gray-400">➝</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                    Hadir
                </span>
                <span class="text-gray-400">➝</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-900 text-amber-400 border border-gray-900">
                    Selesai
                </span>
            </div>

            {{-- Footer --}}
            <div class="mt-4 pt-3 border-t border-gray-200 w-full text-[9px] text-gray-400 flex items-center justify-between">
                <span>ID Kartu: GM-{{ $qrSnippet }}</span>
                <span>© {{ date('Y') }} {{ $gymName }}</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>

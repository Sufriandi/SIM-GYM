{{-- resources/views/components/ui/badge.blade.php --}}
@props([
    'variant' => 'neutral', // primary, success, warning, danger, info, neutral
])

@php
    /*
    |--------------------------------------------------------------------------
    | BASE CLASSES
    |--------------------------------------------------------------------------
    | Clean, Flat, & Modern.
    | - rounded-full: Bentuk pil klasik.
    | - font-medium: Tidak terlalu tebal, mudah dibaca.
    | - px-2.5 py-0.5: Proporsi padding yang pas untuk data.
    */
    $base = implode(' ', [
        'inline-flex items-center',
        'px-2.5 py-0.5',
        'rounded-full',
        'text-xs font-medium', // Ukuran text-xs (12px) lebih standar daripada 10px
        'border',
        'transition-colors focus:outline-none',
        'whitespace-nowrap',
    ]);

    /*
    |--------------------------------------------------------------------------
    | VARIANTS (Standard Tailwind Palette)
    |--------------------------------------------------------------------------
    | Menggunakan kombinasi warna standar yang sudah teruji harmonis.
    | Format: Background Terang + Border Halus + Teks Gelap.
    */
    $variants = [
        // Primary (Indigo/Brand): Untuk label utama
        'primary' => 'bg-indigo-50 text-indigo-700 border-indigo-200',

        // Success (Green): Untuk 'Disetujui'
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',

        // Warning (Yellow/Amber): Untuk 'Pending'
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',

        // Danger (Red): Untuk 'Ditolak' atau Error
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200',

        // Info (Blue): Untuk Status informatif
        'info' => 'bg-blue-50 text-blue-700 border-blue-200',

        // Neutral (Gray): Untuk data umum (Hari, Tanggal)
        'neutral' => 'bg-gray-50 text-gray-600 border-gray-200',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Dot Indicator (Otomatis muncul kecil di kiri) --}}
    {{-- Ini opsional visual trick: Dot kecil membuat badge terasa "hidup" --}}
    @if (in_array($variant, ['primary', 'success', 'warning', 'danger']))
        <svg class="-ml-0.5 mr-1.5 h-2 w-2 opacity-75" fill="currentColor" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3" />
        </svg>
    @endif

    {{ $slot }}
</span>

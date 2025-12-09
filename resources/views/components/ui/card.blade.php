{{-- resources/views/components/ui/card.blade.php --}}
@props([
    'title' => null,
    'subtitle' => null,
    'highlight' => false,   // kalau true, card sedikit lebih menonjol
])

@php
    // Card dasar – dipakai di hampir semua halaman (termasuk Data Produk)
    $baseClasses = 'rounded-3xl border border-brand-borderSoft bg-brand-card shadow-card';

    // Versi highlight – warna sedikit lebih terang & shadow lebih kuat,
    // tapi TIDAK mengubah layout (tidak ada border-3, radius aneh, dsb).
    $highlightClasses = 'rounded-3xl border border-brand-borderStrong bg-brand-cardSoft shadow-card-strong';
@endphp

<section
    {{ $attributes->merge([
        'class' => ($highlight ? $highlightClasses : $baseClasses) . ' p-5 lg:p-6',
    ]) }}
>
    @if($title || $subtitle)
        <header class="mb-4">
            @if($title)
                <h2 class="text-base lg:text-lg font-semibold text-text-main">
                    {{ $title }}
                </h2>
            @endif

            @if($subtitle)
                <p class="text-xs text-text-muted mt-1">
                    {{ $subtitle }}
                </p>
            @endif
        </header>
    @endif

    <div class="space-y-3">
        {{ $slot }}
    </div>
</section>

{{-- resources/views/components/ui/card.blade.php --}}
@props([
    'title' => null,
    'subtitle' => null,
    'highlight' => false,   // kalau true, card sedikit lebih menonjol
])

@php
    /**
     * Prinsip:
     * - Light: tetap seperti desain sekarang.
     * - Dark: tambah ring + border opacity + surface sedikit berbeda agar card tidak menyatu.
     * - Tidak mengganggu class override dari pemanggil (tetap bisa di-override via $attributes->merge).
     */

    // Card dasar (lebih kontras di dark, tapi tetap brand)
    $baseClasses = implode(' ', [
        'rounded-3xl',
        'border border-brand-borderSoft/80 dark:border-brand-borderSoft/35',
        'bg-brand-card dark:bg-brand-card/70',
        'shadow-card',
        // garis pemisah halus supaya card tidak menyatu di dark
        'ring-1 ring-black/5 dark:ring-brand-borderSoft/20',
        // feel
        'backdrop-blur-[1px]',
        'transition-colors duration-200',
    ]);

    // Card highlight (lebih kuat, tapi tetap konsisten)
    $highlightClasses = implode(' ', [
        'rounded-3xl',
        'border border-brand-borderStrong/90 dark:border-brand-borderStrong/55',
        'bg-brand-cardSoft dark:bg-brand-cardSoft/70',
        'shadow-card-strong',
        'ring-1 ring-brand-borderStrong/20 dark:ring-brand-borderStrong/25',
        'backdrop-blur-[1px]',
        'transition-colors duration-200',
    ]);
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

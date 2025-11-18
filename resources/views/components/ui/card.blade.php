{{-- resources/views/components/ui/card.blade.php --}}
@props([
    'title' => null,
    'subtitle' => null,
    'highlight' => false,   // kalau true pakai style sedikit lebih “wah”
])

@php
    $baseClasses = 'rounded-2xl border bg-brand-card shadow-card';
    $highlightClasses = 'bg-brand-cardSoft border-3 border-brand-borderStrong shadow-card-strong bg-brand-radial-spot bg-no-repeat';
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

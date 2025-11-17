{{-- resources/views/components/ui/divider.blade.php --}}
@props([
    'label' => null,
    'variant' => 'soft',  // soft | strong | accent
])

@php
    $lineBase = 'h-px flex-1 rounded-full';
    switch ($variant) {
        case 'strong':
            $lineClass = $lineBase . ' bg-brand-borderStrong/80';
            break;
        case 'accent':
            $lineClass = $lineBase . ' bg-gradient-to-r from-transparent via-accent-500/70 to-transparent';
            break;
        default: // soft
            $lineClass = $lineBase . ' bg-gradient-to-r from-transparent via-brand-borderSoft to-transparent';
    }
@endphp

@if ($label)
    <div {{ $attributes->merge(['class' => 'my-6 flex items-center gap-3 text-xs text-text-muted uppercase tracking-[0.2em]']) }}>
        <div class="{{ $lineClass }}"></div>
        <span>{{ $label }}</span>
        <div class="{{ $lineClass }}"></div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'my-6']) }}>
        <div class="{{ $lineClass }}"></div>
    </div>
@endif

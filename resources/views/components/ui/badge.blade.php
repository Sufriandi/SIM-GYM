{{-- resources/views/components/ui/badge.blade.php --}}
@props([
    'variant' => 'neutral', // primary, accent, success, warning, danger, info, neutral
])

@php
    $base = 'inline-flex items-center px-2.5 py-0.5 rounded-pill text-[11px] font-semibold';

    $variants = [
        'primary' => 'bg-primary-soft text-primary-dark',
        'accent'  => 'bg-accent-50 text-accent-600',
        'success' => 'bg-success-soft text-success-DEFAULT',
        'warning' => 'bg-warning-soft text-warning-DEFAULT',
        'danger'  => 'bg-danger-soft text-danger-DEFAULT',
        'info'    => 'bg-info-soft text-info-DEFAULT',
        'neutral' => 'bg-brand-cardSoft text-text-muted',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

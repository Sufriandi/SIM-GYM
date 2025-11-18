{{-- resources/views/components/ui/button-secondary.blade.php --}}
@props([
    'type' => 'button',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center gap-2
                    rounded-pill px-4 py-2.5 text-sm font-semibold
                    border border-brand-borderStrong
                    text-text-main bg-brand-cardSoft
                    shadow-btn-soft
                    transition-all duration-normal ease-smooth
                    hover:bg-brand-shell hover:-translate-y-0.5 hover:shadow-card
                    active:scale-98
                    focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
    ]) }}
>
    {{ $slot }}
</button>

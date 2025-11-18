{{-- resources/views/components/ui/button-primary.blade.php --}}
@props([
    'type' => 'button',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center gap-2
                    rounded-pill px-4 py-2.5 text-sm font-semibold
                    bg-accent-500 text-brand-white
                    shadow-btn-primary
                    transition-all duration-normal ease-smooth
                    hover:bg-accent-600 hover:shadow-btn-primary-hover hover:-translate-y-0.5
                    active:scale-98
                    focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent',
    ]) }}
>
    {{ $slot }}
</button>

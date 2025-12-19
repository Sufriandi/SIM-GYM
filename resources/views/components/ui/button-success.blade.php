{{-- resources/views/components/ui/button-success.blade.php --}}
@props([
    'type' => 'button',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center gap-2
                    rounded-pill px-4 py-2.5 text-sm font-semibold
                    bg-emerald-600 text-white
                    shadow-btn-primary
                    transition-all duration-normal ease-smooth
                    hover:bg-emerald-700 hover:shadow-btn-primary-hover hover:-translate-y-0.5
                    active:scale-98
                    focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400',
    ]) }}
>
    {{ $slot }}
</button>

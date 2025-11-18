{{-- resources/views/components/ui/input.blade.php --}}
@props([
    'type' => 'text',
    'name' => null,
])

<input
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $attributes->get('id') ?? $name }}"
    {{ $attributes->merge([
        'class' =>
            'w-full rounded-gym border border-brand-borderSoft bg-brand-card
             px-3 py-2.5 text-sm text-text-main
             placeholder:text-text-muted/60
             focus:outline-none focus:ring-2 focus:ring-gold-400/40 focus:border-gold-500
             transition-all duration-200'
    ]) }}
/>

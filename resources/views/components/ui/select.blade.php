{{-- resources/views/components/ui/select.blade.php --}}
@props([
    'name' => null,
])

<select
    name="{{ $name }}"
    id="{{ $attributes->get('id') ?? $name }}"
    {{ $attributes->merge([
        'class' =>
            'w-full rounded-gym border border-brand-borderSoft bg-brand-card
             px-3 py-2.5 text-sm text-text-main
             focus:outline-none focus:ring-2 focus:ring-gold-400/40 focus:border-gold-500
             transition-all duration-200 appearance-none
             bg-[url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' fill=\'none\' stroke=\'%236C5A46\' stroke-width=\'1.5\' viewBox=\'0 0 24 24\'%3E%3Cpath d=\'m6 9 6 6 6-6\'/%3E%3C/svg%3E")] bg-no-repeat bg-[right_0.85rem_center]'
    ]) }}
>
    {{ $slot }}
</select>

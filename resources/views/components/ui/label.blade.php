@props([
    'value' => null,
    'required' => false,
    'icon' => null,
])

<label {{ $attributes->merge([
        'class' =>
            'flex items-center gap-2 text-sm font-semibold text-text-main tracking-wide'
    ]) }}>

    {{-- ICON (Lucide) Opsional --}}
    @if($icon)
        <i data-lucide="{{ $icon }}" class="w-4 h-4 text-gold"></i>
    @endif

    {{-- TEXT --}}
    <span>{{ $value ?? $slot }}</span>

    {{-- REQUIRED BADGE --}}
    @if($required)
        <span class="text-accent-500 text-xs font-bold">*</span>
    @endif
</label>

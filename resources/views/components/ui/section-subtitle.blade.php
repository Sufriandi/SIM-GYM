{{-- resources/views/components/ui/section-subtitle.blade.php --}}
@props([
    'text' => null,
    'icon' => null,    // opsional: lucide icon (ex: "info", "bookmark", "clock")
    'divider' => true, // tampilkan garis bawah atau tidak
])

<div class="flex flex-col gap-1.5 w-full">

    {{-- TEXT + ICON --}}
    <div class="flex items-center gap-2">

        {{-- ICON optional --}}
        @if($icon)
            <i data-lucide="{{ $icon }}"
               class="w-4 h-4 text-gold opacity-80"></i>
        @endif

        {{-- TITLE --}}
        <h3 class="text-sm font-semibold tracking-wide text-text-muted uppercase">
            {{ $text ?? $slot }}
        </h3>
    </div>

    {{-- Divider / garis bawah --}}
    @if($divider)
        <div class="w-full h-px bg-brand-borderSoft/60"></div>
    @endif
</div>

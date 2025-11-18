{{-- resources/views/components/ui/form-group.blade.php --}}
@props([
    'label' => null,
    'for' => null,
    'required' => false,
    'helper' => null,
    'icon' => null,          // icon di label (Lucide)
    'errorKey' => null,      // nama field untuk error, default = for
])

@php
    $errorKey = $errorKey ?? $for;
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <x-ui.label
            :value="$label"
            :required="$required"
            :icon="$icon"
            :for="$for"
            class="block"
        />
    @endif

    {{-- SLOT: tempat <x-ui.input>, <x-ui.select>, dll --}}
    {{ $slot }}

    @if($helper)
        <p class="text-[11px] text-text-muted mt-0.5">
            {{ $helper }}
        </p>
    @endif

    @if($errorKey)
        <x-ui.input-error :messages="$errors->get($errorKey)" />
    @endif
</div>

{{-- resources/views/components/ui/input-error.blade.php --}}
@props(['messages'])

@php
    $messages = $messages instanceof \Illuminate\Support\ViewErrorBag
        ? $messages->all()
        : (array) $messages;
@endphp

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-1 space-y-0.5 text-xs text-danger']) }}>
        @foreach ($messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif

{{-- resources/views/components/ui/section-header.blade.php --}}
@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-4">
    <div>
        <h1 class="text-2xl lg:text-3xl font-heading text-text-main">
            {{ $title }}
        </h1>
        @if($subtitle)
            <p class="text-sm text-text-muted mt-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if(trim($slot))
        <div class="flex items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

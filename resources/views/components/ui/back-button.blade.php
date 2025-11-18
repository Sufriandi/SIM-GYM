@props([
    'href' => '#',
    'text' => 'Kembali'
])

<a
    href="{{ $href }}"
    class="inline-flex items-center text-gold-700 hover:text-gold-500
           text-sm font-semibold transition-colors"
>
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    {{ $text }}
</a>

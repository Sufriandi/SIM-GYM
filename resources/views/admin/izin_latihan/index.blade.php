{{-- resources/views/admin/izin_latihan/index.blade.php --}}

@php
    $pageTitle = $pageTitle ?? 'Permintaan Izin Baru';
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Permintaan izin yang belum diproses."
>
    {{-- FLASH MESSAGE GLOBAL --}}
    @if (session('success'))
        <div class="mb-4 rounded-xl border border-success-soft bg-success-soft/20 px-4 py-3 text-sm text-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-danger-soft bg-danger-soft/20 px-4 py-3 text-sm text-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 rounded-xl border border-info-soft bg-info-soft/20 px-4 py-3 text-sm text-info">
            {{ session('info') }}
        </div>
    @endif

    {{-- KOMPONEN LIVEWIRE --}}
    <livewire:admin.izin-latihan />
</x-layouts.admin>

{{-- File: resources/views/admin/stok_produk/history_detail.blade.php --}}

@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Detail Log Stok';
    $log = $stokProduk; // Menggunakan variabel yang lebih mudah dibaca
    $produk = $log->produk;

    $perubahan = $log->jumlah;
    $status = $perubahan > 0 ? 'Stok Masuk' : ($perubahan < 0 ? 'Stok Keluar/Terjual' : 'Penyesuaian (Netral)');
    $badgeColor = $perubahan > 0 ? 'success' : ($perubahan < 0 ? 'danger' : 'info');
    $perubahanDisplay = ($perubahan > 0 ? '+' : '') . number_format(abs($perubahan));

@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Informasi detail pergerakan stok produk."
>
    {{-- JUDUL + SUBTITLE ATAS --}}
    <x-ui.section-header
        :title="$pageTitle"
        subtitle="Detail log perubahan stok untuk produk: {{ $produk?->nama ?? '[Produk Dihapus]' }}"
    />

    {{-- TOMBOL KEMBALI DI BAWAH SUBTITLE --}}
    <div class="mt-2">
        <x-ui.back-button
            href="{{ route('admin.stok_produk.history') }}"
            text="Kembali ke Riwayat Stok"
        />
    </div>

    {{-- GARIS PEMBATAS --}}
    <hr class="border-t border-brand-borderSoft mb-6 mt-2">

    {{-- CARD DETAIL UTAMA --}}
    <x-ui.card class="border-brand-borderSoft max-w-4xl mx-auto">
        
        {{-- HEADER RINGKASAN --}}
        <div class="px-6 pt-4 pb-4 border-b border-brand-borderSoft">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-text-main">
                    Log ID: #{{ $log->id }}
                </h3>
                <x-ui.badge :variant="$badgeColor">
                    {{ strtoupper($status) }}
                </x-ui.badge>
            </div>
            <p class="text-sm text-text-muted mt-1">
                Dicatat pada: {{ Carbon::parse($log->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm') }}
            </p>
        </div>

        {{-- DETAIL UTAMA --}}
        <div class="p-6 space-y-6">
            
            {{-- INFORMASI PRODUK --}}
            <div class="border border-brand-borderSoft rounded-xl p-4 bg-brand-surface-50">
                <h4 class="text-sm font-semibold text-text-muted border-b border-brand-borderSoft/50 pb-2 mb-3">Informasi Produk</h4>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="font-medium text-text-main">Nama Produk:</dt>
                        <dd class="text-text-main font-semibold {{ $produk ? '' : 'text-danger italic' }}">
                            {{ $produk?->nama ?? '[Produk Dihapus]' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-text-main">Kategori:</dt>
                        <dd class="text-text-muted">
                            {{ $produk?->kategori ? ucwords($produk->kategori) : '-' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-text-main">Stok Produk Saat Ini:</dt>
                        <dd class="font-bold {{ $produk?->stok > 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($produk?->stok ?? 0) }} Unit
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- INFORMASI PERGERAKAN STOK --}}
            <div class="border border-brand-borderSoft rounded-xl p-4 bg-brand-surface-50">
                <h4 class="text-sm font-semibold text-text-muted border-b border-brand-borderSoft/50 pb-2 mb-3">Rincian Pergerakan</h4>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="font-medium text-text-main">Jenis Perubahan:</dt>
                        <dd class="font-bold {{ $badgeColor === 'success' ? 'text-success' : ($badgeColor === 'danger' ? 'text-danger' : 'text-info') }}">
                            {{ $status }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-text-main">Jumlah Perubahan:</dt>
                        <dd class="font-bold {{ $perubahan > 0 ? 'text-success' : ($perubahan < 0 ? 'text-danger' : 'text-info') }}">
                            {{ $perubahanDisplay }} Unit
                        </dd>
                    </div>
                    <div class="pt-2">
                        <dt class="font-medium text-text-main">Keterangan Log:</dt>
                        <dd class="text-text-muted italic mt-1 bg-brand-shell p-3 rounded-lg">
                            {{ $log->keterangan ?? 'Tidak ada keterangan tambahan.' }}
                        </dd>
                    </div>
                </dl>
            </div>

        </div>

        {{-- FOOTER TOMBOL --}}
        <div class="flex justify-end p-6 border-t border-brand-borderSoft">
            <x-ui.back-button
                href="{{ route('admin.stok_produk.history') }}"
                text="Tutup Detail"
                variant="secondary"
            />
        </div>

    </x-ui.card>
</x-layouts.admin>
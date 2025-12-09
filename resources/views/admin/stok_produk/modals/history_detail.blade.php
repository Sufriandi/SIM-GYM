{{-- resources/views/admin/stok_produk/modals/history_detail.blade.php --}}

@php
    use Carbon\Carbon;

    /** @var \App\Models\StokProduk $log */
    $produk = $log->produk;

    $perubahan = $log->jumlah;
    $status = $perubahan > 0
        ? 'Stok Masuk'
        : ($perubahan < 0 ? 'Stok Keluar/Terjual' : 'Penyesuaian (Netral)');

    $badgeColor = $perubahan > 0
        ? 'success'
        : ($perubahan < 0 ? 'danger' : 'info');

    $perubahanDisplay = ($perubahan > 0 ? '+' : '') . number_format(abs($perubahan));
@endphp

<div
    x-show="openDetailId === {{ $log->id }}"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null"
    @keydown.escape.window="openDetailId = null"
>
    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[92vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Detail Log Stok
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Produk: {{ $produk?->nama ?? '[Produk Dihapus]' }}
                </p>
            </div>

            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 space-y-6">
            {{-- RINGKASAN --}}
            <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-text-muted mb-0.5">
                            Log ID
                        </p>
                        <p class="text-lg font-bold text-text-main leading-snug">
                            #{{ $log->id }}
                        </p>
                    </div>

                    <div class="flex flex-col items-end gap-1">
                        <x-ui.badge :variant="$badgeColor">
                            {{ strtoupper($status) }}
                        </x-ui.badge>

                        <p class="text-[11px] text-text-muted text-right">
                            Dicatat:
                            {{ Carbon::parse($log->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- KIRI: INFORMASI PRODUK --}}
                <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                    <h3 class="text-sm font-semibold text-text-main mb-2">
                        Informasi Produk
                    </h3>

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-text-muted">Nama Produk</dt>
                            <dd class="font-semibold text-right break-words whitespace-normal {{ $produk ? 'text-text-main' : 'text-danger italic' }}">
                                {{ $produk?->nama ?? '[Produk Dihapus]' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-3">
                            <dt class="text-text-muted">Kategori</dt>
                            <dd class="text-right text-text-main">
                                {{ $produk?->kategori ? ucwords($produk->kategori) : '-' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-3 pt-1 border-t border-brand-borderSoft/60 mt-2">
                            <dt class="text-text-muted">Stok Saat Ini</dt>
                            <dd class="font-bold text-right {{ ($produk?->stok ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($produk?->stok ?? 0) }} unit
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- KANAN: INFORMASI PERGERAKAN --}}
                <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                    <h3 class="text-sm font-semibold text-text-main mb-2">
                        Rincian Pergerakan Stok
                    </h3>

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-text-muted">Jenis Perubahan</dt>
                            <dd class="font-bold text-right
                                {{ $badgeColor === 'success'
                                    ? 'text-success'
                                    : ($badgeColor === 'danger' ? 'text-danger' : 'text-info') }}">
                                {{ $status }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-3">
                            <dt class="text-text-muted">Jumlah Perubahan</dt>
                            <dd class="font-bold text-right
                                {{ $perubahan > 0 ? 'text-success' : ($perubahan < 0 ? 'text-danger' : 'text-info') }}">
                                {{ $perubahanDisplay }} unit
                            </dd>
                        </div>

                        <div class="pt-2 border-t border-brand-borderSoft/60 mt-2">
                            <dt class="text-text-muted mb-1">Keterangan Log</dt>
                            <dd
                                class="text-sm text-text-muted italic bg-brand-surface-50 border border-brand-borderSoft
                                       rounded-xl px-3 py-2 leading-relaxed whitespace-pre-line"
                            >
                                {{ $log->keterangan ?? 'Tidak ada keterangan tambahan.' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- resources/views/admin/transaksi_membership/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $today = $today ?? Carbon::today();

    $buyer = $trx->buyer; // Member model
    $paket = $trx->paket;

    $mulai = $trx->tanggal_mulai ? Carbon::parse($trx->tanggal_mulai) : null;
    $akhir = $trx->tanggal_akhir ? Carbon::parse($trx->tanggal_akhir) : null;
    $canceledAt = $trx->canceled_at ? Carbon::parse($trx->canceled_at) : null;

    if ($canceledAt) {
        $statusLabel = 'Dibatalkan';
    } elseif ($mulai && $today->lt($mulai)) {
        $statusLabel = 'Belum Aktif';
    } elseif ($mulai && $akhir && $today->between($mulai, $akhir)) {
        $statusLabel = 'Aktif';
    } elseif ($akhir && $today->gt($akhir)) {
        $statusLabel = 'Expired';
    } else {
        $statusLabel = 'Tidak Diketahui';
    }

    $periodeText = $mulai && $akhir ? $mulai->format('d M Y') . ' – ' . $akhir->format('d M Y') : '—';

    $metodeOptions = $metodeOptions ?? [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];

    $metodeLabel = $trx->metode_pembayaran
        ? $metodeOptions[$trx->metode_pembayaran] ?? Str::title($trx->metode_pembayaran)
        : '—';

    // peserta tambahan = semua participants selain role primary
    $additionalNames = $trx->participants
        ? $trx->participants
            ->filter(fn($p) => ($p->role ?? null) !== 'primary')
            ->map(fn($p) => $p->member?->user?->name)
            ->filter()
            ->values()
        : collect();

    $jenisLabel = $trx->jenis_transaksi ? Str::upper($trx->jenis_transaksi) : '—';
@endphp

<div x-show="openDetailId === {{ $trx->id }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null" @keydown.escape.window="openDetailId = null" @wheel.prevent @touchmove.prevent>
    <div
        class="relative w-full max-w-3xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Transaksi Membership</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Informasi lengkap transaksi beserta periode, metode, dan status.
                </p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- KIRI --}}
                <div class="space-y-4">
                    {{-- MEMBER --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Pembeli (Primary)</p>
                        <p class="text-base font-semibold text-text-main mt-1">
                            {{ $buyer?->user?->name ?? '[Member dihapus]' }}
                        </p>

                        @if ($buyer?->user?->username)
                            <p class="text-[11px] text-text-muted mt-0.5">
                                Username:
                                <span class="font-medium text-text-main">{{ $buyer->user->username }}</span>
                            </p>
                        @endif

                        @if ($additionalNames->isNotEmpty())
                            <p class="text-[11px] text-text-muted mt-2">
                                Peserta tambahan:
                                <span class="font-medium text-text-main">{{ $additionalNames->join(', ') }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- PAKET --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Paket</p>
                        <p class="text-sm font-semibold text-text-main mt-1">
                            {{ $paket?->nama ?? '[Paket dihapus]' }}
                        </p>

                        @if ($paket)
                            <p class="mt-2 text-xs font-semibold text-gold-500">
                                {{ Str::ucfirst($paket->tipe) }}
                                <span class="text-[11px] text-text-muted ml-1">
                                    · {{ $paket->durasi }} hari
                                </span>
                            </p>
                        @endif
                    </div>

                    {{-- METODE + JENIS --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Jenis & Metode</p>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm font-semibold text-gold-500">
                                {{ $jenisLabel }}
                            </p>
                            <p class="text-[12px] text-text-main">
                                Metode: <span class="font-semibold text-gold-500">{{ $metodeLabel }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KANAN --}}
                <div class="space-y-4">
                    {{-- STATUS --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Status</p>
                        <p class="mt-2 text-sm font-semibold tracking-wide text-gold-500">
                            {{ Str::upper($statusLabel) }}
                        </p>

                        @if ($canceledAt)
                            <p class="text-[11px] text-text-muted mt-2">
                                Dibatalkan:
                                <span class="font-medium text-text-main">{{ $canceledAt->format('d M Y H:i') }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- PERIODE --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Periode Membership</p>
                        <p class="text-sm font-semibold text-gold-500 mt-1">{{ $periodeText }}</p>
                        <p class="text-[11px] text-text-muted mt-1">
                            Periode dihitung otomatis (auto-extend) berdasarkan transaksi sebelumnya.
                        </p>
                    </div>

                    {{-- TANGGAL TRANSAKSI --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Tanggal Transaksi</p>
                        <p class="text-sm font-semibold text-gold-500 mt-1">
                            {{ $trx->tanggal_transaksi ? Carbon::parse($trx->tanggal_transaksi)->format('d M Y H:i') : '—' }}
                        </p>
                        @if ($trx->creator)
                            <p class="text-[11px] text-text-muted mt-2">
                                Diinput oleh: <span
                                    class="font-medium text-text-main">{{ $trx->creator?->name ?? '—' }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- KETERANGAN --}}
            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                <p class="text-xs text-text-muted uppercase tracking-wider mb-1">Keterangan</p>
                <p class="text-sm text-text-main">
                    {{ $trx->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                </p>
            </div>
        </div>
    </div>
</div>

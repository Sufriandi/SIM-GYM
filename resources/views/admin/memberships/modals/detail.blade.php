{{-- resources/views/admin/memberships/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $member = $membership->member;
    $paket = $membership->paket;

    $mulai = $membership->tanggal_mulai ? Carbon::parse($membership->tanggal_mulai) : null;
    $akhir = $membership->tanggal_akhir ? Carbon::parse($membership->tanggal_akhir) : null;
    $canceledAt = $membership->canceled_at ? Carbon::parse($membership->canceled_at) : null;

    if ($canceledAt) {
        $statusKey = 'canceled';
        $statusLabel = 'Dibatalkan';
    } elseif ($mulai && $today->lt($mulai)) {
        $statusKey = 'upcoming';
        $statusLabel = 'Belum Aktif';
    } elseif ($mulai && $akhir && $today->between($mulai, $akhir)) {
        $statusKey = 'active';
        $statusLabel = 'Aktif';
    } elseif ($akhir && $today->gt($akhir)) {
        $statusKey = 'expired';
        $statusLabel = 'Expired';
    } else {
        $statusKey = 'unknown';
        $statusLabel = 'Tidak Diketahui';
    }

    $periodeText = $mulai && $akhir ? $mulai->format('d M Y') . ' – ' . $akhir->format('d M Y') : '—';

    $metodeOptions = $metodeOptions ?? [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];

    $metodeLabel = $metodeOptions[$membership->metode_pembayaran] ?? Str::title($membership->metode_pembayaran);

    $groupNames = $membership->groupMembers->map(fn($gm) => $gm->member?->nama)->filter()->values();
@endphp

<div x-show="openDetailId === {{ $membership->id }}" x-cloak x-transition
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
                    Informasi lengkap transaksi membership beserta periode dan statusnya.
                </p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar space-y-5">
            {{-- GRID 2 KOLOM --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- KOLOM KIRI: NAMA, PAKET, METODE PEMBAYARAN --}}
                <div class="space-y-4">
                    {{-- NAMA MEMBER --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Member Utama</p>
                        <p class="text-base font-semibold text-text-main mt-1">
                            {{ $member->nama ?? '[Member dihapus]' }}
                        </p>

                        @if ($member && $member->user?->username)
                            <p class="text-[11px] text-text-muted mt-0.5">
                                Username:
                                <span class="font-medium text-text-main">
                                    {{ $member->user->username }}
                                </span>
                            </p>
                        @endif

                        @if ($groupNames->isNotEmpty())
                            <p class="text-[11px] text-text-muted mt-2">
                                Anggota tambahan:
                                <span class="font-medium text-text-main">
                                    {{ $groupNames->join(', ') }}
                                </span>
                            </p>
                        @endif
                    </div>

                    {{-- PAKET --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Paket</p>
                        <p class="text-sm font-semibold text-text-main mt-1">
                            {{ $paket->nama ?? '[Paket dihapus]' }}
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

                    {{-- METODE PEMBAYARAN --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Metode Pembayaran</p>
                        <p class="mt-2 text-sm font-semibold text-gold-500">
                            {{ $metodeLabel }}
                        </p>
                    </div>
                </div>

                {{-- KOLOM KANAN: STATUS, PERIODE, TANGGAL TRANSAKSI --}}
                <div class="space-y-4">
                    {{-- STATUS --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Status Membership</p>
                        <p class="mt-2 text-sm font-semibold tracking-wide text-gold-500">
                            {{ Str::upper($statusLabel) }}
                        </p>

                        @if ($canceledAt)
                            <p class="text-[11px] text-text-muted mt-2">
                                Dibatalkan:
                                <span class="font-medium text-text-main">
                                    {{ $canceledAt->format('d M Y H:i') }}
                                </span>
                            </p>
                        @endif
                    </div>

                    {{-- PERIODE MEMBERSHIP --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Periode Membership</p>
                        <p class="text-sm font-semibold text-gold-500 mt-1">
                            {{ $periodeText }}
                        </p>
                        <p class="text-[11px] text-text-muted mt-1">
                            Periode aktif diambil dari tanggal mulai dan tanggal akhir pada transaksi ini.
                        </p>
                    </div>

                    {{-- TANGGAL TRANSAKSI --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Tanggal Transaksi</p>
                        <p class="text-sm font-semibold text-gold-500 mt-1">
                            {{ Carbon::parse($membership->tanggal_transaksi)->format('d M Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- KETERANGAN (FULL WIDTH) --}}
            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                <p class="text-xs text-text-muted uppercase tracking-wider mb-1">Keterangan</p>
                <p class="text-sm text-text-main">
                    {{ $membership->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                </p>
            </div>
        </div>
    </div>
</div>

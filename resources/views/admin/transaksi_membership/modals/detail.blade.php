{{-- resources/views/admin/transaksi_membership/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $today = $today ?? Carbon::today();
    $buyer = $trx->buyer;
    $paket = $trx->paket;

    // 1. Definisikan Meta Status (Sama seperti Index)
    $statusMeta = [
        'belum_aktif' => ['label' => 'Belum Aktif', 'variant' => 'info'],
        'aktif' => ['label' => 'Aktif', 'variant' => 'success'],
        'expired' => ['label' => 'Expired', 'variant' => 'warning'],
        'canceled' => ['label' => 'Dibatalkan', 'variant' => 'danger'],
        'unknown' => ['label' => 'Tidak Diketahui', 'variant' => 'neutral'],
    ];

    // 2. Ambil Status Utama Transaksi
    // Kita gunakan $trx->status jika ada (konsisten dengan index),
    // atau fallback ke logic manual jika accessor model belum ada.
    if (isset($trx->status)) {
        $mainStatusKey = $trx->status;
    } else {
        // Fallback logic manual (jika model belum punya accessor status)
        $m = $trx->tanggal_mulai ? Carbon::parse($trx->tanggal_mulai) : null;
        $a = $trx->tanggal_akhir ? Carbon::parse($trx->tanggal_akhir) : null;
        $c = $trx->canceled_at ? Carbon::parse($trx->canceled_at) : null;

        if ($c) {
            $mainStatusKey = 'canceled';
        } elseif ($m && $today->lt($m)) {
            $mainStatusKey = 'belum_aktif';
        } elseif ($m && $a && $today->between($m, $a)) {
            $mainStatusKey = 'aktif';
        } elseif ($a && $today->gt($a)) {
            $mainStatusKey = 'expired';
        } else {
            $mainStatusKey = 'unknown';
        }
    }

    $mainStatusData = $statusMeta[$mainStatusKey] ?? $statusMeta['unknown'];

    // --- Data Lainnya ---
    $mulai = $trx->tanggal_mulai ? Carbon::parse($trx->tanggal_mulai) : null;
    $akhir = $trx->tanggal_akhir ? Carbon::parse($trx->tanggal_akhir) : null;
    $canceledAt = $trx->canceled_at ? Carbon::parse($trx->canceled_at) : null;

    $periodeText = $mulai && $akhir ? $mulai->format('d M Y') . ' – ' . $akhir->format('d M Y') : '—';
    $jenisLabel = $trx->jenis_transaksi ? Str::upper($trx->jenis_transaksi) : '—';

    $metodeOptions = $metodeOptions ?? ['cash' => 'Cash', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
    $metodeLabel = $trx->metode_pembayaran
        ? $metodeOptions[$trx->metode_pembayaran] ?? Str::title($trx->metode_pembayaran)
        : '—';

    $additionalNames = $trx->participants
        ? $trx->participants
            ->filter(fn($p) => ($p->role ?? null) !== 'primary')
            ->map(fn($p) => $p->member?->user?->name)
            ->filter()
            ->values()
        : collect();

    $rows = ($trx->participants ?? collect())->sortBy(fn($p) => $p->role === 'primary' ? 0 : 1)->values();

    $fmtDate = function ($d) {
        try {
            return $d ? Carbon::parse($d)->format('d M Y') : '—';
        } catch (\Throwable $e) {
            return '—';
        }
    };

    // 3. Helper Function untuk Status Per-Member (Tabel Bawah)
    // Mengembalikan array yang berisi 'variant' untuk <x-ui.badge>
    $memberStatusBadge = function ($start, $end, $canceledAt, $today) {
        if ($canceledAt) {
            return ['label' => 'Dibatalkan', 'variant' => 'danger'];
        }
        if (!$start || !$end) {
            return ['label' => 'Unknown', 'variant' => 'neutral'];
        }

        $s = Carbon::parse($start)->startOfDay();
        $e = Carbon::parse($end)->startOfDay();
        $t = $today->copy()->startOfDay();

        if ($t->lt($s)) {
            return ['label' => 'Belum Aktif', 'variant' => 'info'];
        }
        if ($t->between($s, $e, true)) {
            return ['label' => 'Aktif', 'variant' => 'success'];
        }
        if ($t->gt($e)) {
            return ['label' => 'Expired', 'variant' => 'warning'];
        }

        return ['label' => 'Unknown', 'variant' => 'neutral'];
    };
@endphp

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div x-show="openDetailId === {{ $trx->id }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null" @keydown.escape.window="openDetailId = null">

    <div
        class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto hide-scrollbar rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        {{-- HEADER --}}
        <div
            class="sticky top-0 z-20 bg-brand-shell/95 backdrop-blur-md flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
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
        <div class="px-6 pb-6 pt-4 space-y-5">
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
                                Username: <span class="font-medium text-text-main">{{ $buyer->user->username }}</span>
                            </p>
                        @endif
                        @if ($additionalNames->isNotEmpty())
                            <p class="text-[11px] text-text-muted mt-2">
                                Peserta tambahan: <span
                                    class="font-medium text-text-main">{{ $additionalNames->join(', ') }}</span>
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
                                <span class="text-[11px] text-text-muted ml-1">· {{ $paket->durasi }} hari</span>
                            </p>
                        @endif
                    </div>

                    {{-- JENIS --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Jenis & Metode</p>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm font-semibold text-gold-500">{{ $jenisLabel }}</p>
                            <p class="text-[12px] text-text-main">
                                Metode: <span class="font-semibold text-gold-500">{{ $metodeLabel ?? '—' }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KANAN --}}
                <div class="space-y-4">
                    {{-- STATUS (UPDATED: Menggunakan x-ui.badge) --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider mb-2">Status (Ringkasan)</p>

                        {{-- Penggunaan Badge --}}
                        <x-ui.badge :variant="$mainStatusData['variant']">
                            {{ $mainStatusData['label'] }}
                        </x-ui.badge>

                        @if ($canceledAt)
                            <p class="text-[11px] text-text-muted mt-3 pt-2 border-t border-brand-borderSoft/50">
                                Dibatalkan: <span
                                    class="font-medium text-text-main">{{ $canceledAt->format('d M Y H:i') }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- PERIODE --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Periode (Ringkasan)</p>
                        <p class="text-sm font-semibold text-gold-500 mt-1">{{ $periodeText }}</p>
                    </div>

                    {{-- TANGGAL TRX --}}
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

            {{-- PERIODE PER MEMBER (Hanya jika Double/Triple) --}}
            @if (in_array(Str::lower($paket->tipe ?? ''), ['double', 'triple']))
                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-text-muted uppercase tracking-wider">Periode per Member (Akurat)</p>
                            <p class="text-[11px] text-text-muted mt-1">Masa aktif per member.</p>
                        </div>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-xs text-text-muted uppercase tracking-wider">
                                <tr class="border-b border-brand-borderSoft/70">
                                    <th class="py-2 pr-3 text-left">Member</th>
                                    <th class="py-2 pr-3 text-left">Role</th>
                                    <th class="py-2 pr-3 text-left">Mulai</th>
                                    <th class="py-2 pr-3 text-left">Akhir</th>
                                    <th class="py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-borderSoft/60">
                                @forelse($rows as $p)
                                    @php
                                        $name = $p->member?->user?->name ?? '[Member dihapus]';
                                        $roleLabel = $p->role === 'primary' ? 'Primary' : 'Member';

                                        // Hitung status badge per row
                                        $st = $memberStatusBadge(
                                            $p->tanggal_mulai,
                                            $p->tanggal_akhir,
                                            $canceledAt,
                                            $today,
                                        );
                                    @endphp
                                    <tr>
                                        <td class="py-2 pr-3 text-text-main font-medium">{{ $name }}</td>
                                        <td class="py-2 pr-3 text-text-muted">{{ $roleLabel }}</td>
                                        <td class="py-2 pr-3 text-text-main">{{ $fmtDate($p->tanggal_mulai) }}</td>
                                        <td class="py-2 pr-3 text-text-main">{{ $fmtDate($p->tanggal_akhir) }}</td>
                                        <td class="py-2">
                                            {{-- Penggunaan Badge di Tabel --}}
                                            <x-ui.badge :variant="$st['variant']" class="text-[10px]">
                                                {{ $st['label'] }}
                                            </x-ui.badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-3 text-sm text-text-muted">Data belum tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- KETERANGAN --}}
            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                <p class="text-xs text-text-muted uppercase tracking-wider mb-1">Keterangan</p>
                <p class="text-sm text-text-main">{{ $trx->keterangan ?: 'Tidak ada keterangan tambahan.' }}</p>
            </div>
        </div>
    </div>
</div>

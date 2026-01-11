{{-- resources/views/member/membership_transaksi/status.blade.php --}}

@php
    $p = $trx->paket;

    $fmt = fn($v) => number_format((int)$v, 0, ',', '.');

    $payLabel = [
        \App\Models\TransaksiMembership::PAY_PENDING   => 'Menunggu Pembayaran',
        \App\Models\TransaksiMembership::PAY_SUBMITTED => 'Menunggu Verifikasi Admin',
        \App\Models\TransaksiMembership::PAY_CONFIRMED => 'Dikonfirmasi',
        \App\Models\TransaksiMembership::PAY_REJECTED  => 'Ditolak',
        \App\Models\TransaksiMembership::PAY_EXPIRED   => 'Kedaluwarsa',
    ];

    $canPay = in_array((string) $trx->payment_status, [
        \App\Models\TransaksiMembership::PAY_PENDING,
        \App\Models\TransaksiMembership::PAY_SUBMITTED
    ], true);

    $badgeClass = function ($s) {
        return match ((string)$s) {
            \App\Models\TransaksiMembership::PAY_CONFIRMED => 'bg-emerald-500/15 border-emerald-500/20 text-emerald-200',
            \App\Models\TransaksiMembership::PAY_SUBMITTED => 'bg-amber-500/15 border-amber-500/20 text-amber-200',
            \App\Models\TransaksiMembership::PAY_REJECTED  => 'bg-red-500/15 border-red-500/20 text-red-200',
            \App\Models\TransaksiMembership::PAY_EXPIRED   => 'bg-slate-500/15 border-slate-400/20 text-slate-200',
            default                                      => 'bg-white/10 border-white/15 text-white/90',
        };
    };
@endphp

<x-layouts.member
    :title="'Status Transaksi Membership'"
    page-title="Status Transaksi"
    page-subtitle="Lakukan pembayaran, lalu upload bukti untuk verifikasi admin."
>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 pb-10">

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-4 text-emerald-200 shadow-card-soft">
                <div class="flex items-start gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-300 mt-0.5"></i>
                    <div class="font-semibold">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        <section class="relative overflow-hidden rounded-3xl border border-brand-borderSoft/60 shadow-card-strong mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/20"></div>
            <div class="absolute -top-24 -right-24 w-[560px] h-[560px] bg-gold-500/10 rounded-full blur-[160px]"></div>
            <div class="absolute -bottom-24 -left-24 w-[560px] h-[560px] bg-accent-500/10 rounded-full blur-[170px]"></div>
            <div class="absolute inset-0 opacity-[0.07]"
                 style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 38px 38px;"></div>

            <div class="relative z-10 px-6 md:px-10 py-10 md:py-12">
                <p class="text-gold-300 font-bold tracking-widest uppercase text-xs font-heading">
                    Transaksi Membership
                </p>

                <h1 class="mt-3 font-display font-extrabold leading-[0.95] tracking-tight
                           text-[clamp(30px,4vw,54px)] text-white">
                    {{ $trx->no_nota ?? ('TM-' . $trx->id) }}
                </h1>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-white border border-white/15 backdrop-blur">
                        {{ $p->nama ?? 'Paket' }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-white border border-white/15 backdrop-blur">
                        Total: Rp {{ $fmt($trx->total ?? 0) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-white border border-white/15 backdrop-blur uppercase">
                        {{ $trx->metode_pembayaran ?? '-' }}
                    </span>
                </div>

                <div class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-2xl border {{ $badgeClass($trx->payment_status) }}">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span class="text-sm font-bold">
                        {{ $payLabel[$trx->payment_status] ?? $trx->payment_status }}
                    </span>
                </div>

                @if($trx->expires_at && $canPay)
                    <p class="mt-3 text-white/70 text-sm">
                        Batas waktu: <span class="font-bold">{{ $trx->expires_at->translatedFormat('d M Y, H:i') }}</span>
                    </p>
                @endif
            </div>

            <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold-500/30 to-transparent"></div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            {{-- Payment gateway --}}
            <div class="rounded-3xl bg-brand-card border border-brand-borderSoft shadow-card-soft p-6">
                <h3 class="text-lg font-display font-bold text-brand-text">Payment Gateway</h3>
                <p class="mt-2 text-sm text-brand-textSoft leading-relaxed">
                    Buka rekening / QRIS dan instruksi pembayaran. Klik di luar modal atau tekan ESC untuk menutup.
                </p>

                <button
                    type="button"
                    onclick="
                        window.dispatchEvent(new CustomEvent('payment:open', {
                            detail: {
                                total: {{ (int)($trx->total ?? 0) }},
                                orderId: @js($orderId),
                                waAdmin: @js($waAdmin),
                                duration: {{ (int)($remainingSeconds ?? 0) }}
                            }
                        }));
                    "
                    class="mt-5 w-full py-4 rounded-2xl font-bold font-heading
                           text-brand-nav transition flex items-center justify-center gap-2
                           bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700
                           border border-gold-500/20 shadow-gold-glow hover:-translate-y-[1px]
                           {{ $canPay ? '' : 'opacity-60 cursor-not-allowed' }}"
                    {{ $canPay ? '' : 'disabled' }}
                >
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    Bayar Sekarang
                </button>

                <p class="mt-4 text-xs text-brand-textSoft leading-relaxed">
                    Tombol “Saya Sudah Bayar” hanya mengarahkan ke WhatsApp admin (tidak otomatis mengubah status).
                </p>
            </div>

            {{-- Upload bukti --}}
            <div class="rounded-3xl bg-brand-card border border-brand-borderSoft shadow-card-soft p-6">
                <h3 class="text-lg font-display font-bold text-brand-text">Upload Bukti Pembayaran</h3>
                <p class="mt-2 text-sm text-brand-textSoft leading-relaxed">
                    Setelah upload, status menjadi “submitted” dan menunggu admin verifikasi.
                </p>

                @if($canPay)
                    <form method="POST"
                          action="{{ route('member.membership.transaksi.upload_bukti', $trx->id) }}"
                          enctype="multipart/form-data"
                          class="mt-5 space-y-4">
                        @csrf

                        <input
                            type="file"
                            name="bukti_bayar"
                            class="w-full px-4 py-4 rounded-2xl bg-brand-shell border border-brand-borderSoft
                                   text-brand-text
                                   focus:outline-none focus:ring-2 focus:ring-gold-500/25 focus:border-gold-500/30 transition"
                        >

                        @error('bukti_bayar')
                            <div class="text-sm text-red-500 font-semibold">{{ $message }}</div>
                        @enderror

                        <button
                            class="w-full py-4 rounded-2xl font-bold font-heading
                                   bg-brand-shell hover:bg-brand-surface-100 text-brand-text transition
                                   border border-brand-borderSoft flex items-center justify-center gap-2"
                        >
                            <i data-lucide="upload" class="w-5 h-5"></i>
                            Kirim Bukti
                        </button>
                    </form>

                    @if($trx->payment_proof_path)
                        <div class="mt-4 text-xs text-brand-textSoft">
                            Bukti terakhir: <span class="font-semibold">{{ $trx->payment_proof_original }}</span>
                        </div>
                    @endif
                @else
                    <div class="mt-5 rounded-2xl border border-brand-borderSoft bg-brand-shell p-4 text-sm text-brand-textSoft">
                        Transaksi tidak bisa diproses (sudah dikonfirmasi/ditolak/kedaluwarsa).
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal Payment --}}
        @include('member.membership_transaksi.partials.payment_modal', [
            'rekenings' => $rekenings,
            'qris'      => $qris,
            'qrisImg'   => $qrisImg,
            'orderId'   => $orderId,
            'waAdmin'   => $waAdmin,
        ])
    </div>
</x-layouts.member>

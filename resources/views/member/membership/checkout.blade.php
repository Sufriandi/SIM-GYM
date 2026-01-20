@php
    $pageTitle = 'Checkout Membership';
    $pageSubtitle = 'Selesaikan pembayaran untuk mengaktifkan membership.';
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-5xl mx-auto pt-2 space-y-5">

        <a href="{{ route('member.membership.show', $paketMembership->id) }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-text-muted hover:text-text-main transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Detail Paket
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-2xl border border-brand-borderSoft bg-brand-shell/50 p-6">
                    <h1 class="text-2xl font-extrabold text-text-main">{{ $paketMembership->nama }}</h1>
                    <p class="mt-1 text-sm text-text-muted">
                        {{ $paketMembership->deskripsi ?: 'Paket membership.' }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <div class="inline-flex items-center gap-2 rounded-full border border-brand-borderSoft bg-white/60 px-4 py-2">
                            <span class="text-xs text-text-muted">Durasi</span>
                            <span class="text-sm font-semibold text-text-main">{{ (int)$paketMembership->durasi }} hari</span>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-brand-borderSoft bg-white/60 px-4 py-2">
                            <span class="text-xs text-text-muted">Akses</span>
                            <span class="text-sm font-semibold text-text-main">Full Gym</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-brand-borderSoft bg-white/60 p-6">
                    <div class="flex items-end justify-between gap-6">
                        <div>
                            <p class="text-xs tracking-widest text-text-muted uppercase">Total Tagihan</p>
                            <p class="text-2xl font-extrabold text-gold-600">
                                Rp {{ number_format((int)$totalTagihan, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-xs text-text-muted text-right">
                            Order ID<br>
                            <span class="font-mono text-text-main">{{ $orderId }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-brand-borderSoft bg-white/60 p-6 lg:sticky lg:top-6">
                    <h2 class="text-base font-bold text-text-main mb-3">Pembayaran</h2>
                    @include('member.membership.partials.payment_gateway')
                </div>
            </div>

        </div>
    </div>
</x-layouts.member>

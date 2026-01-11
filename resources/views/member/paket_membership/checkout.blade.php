{{-- resources/views/member/paket_membership/checkout.blade.php --}}

@php
    $p = $paketMembership;
    $fmt = fn($v) => number_format((int)$v, 0, ',', '.');
@endphp

<x-layouts.member :pageTitle="'Checkout Membership'">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 pb-10">
        <div class="rounded-3xl bg-brand-card border border-brand-borderSoft shadow-card-soft p-6 md:p-7">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-brand-textSoft">Checkout</p>
                    <h1 class="mt-2 text-2xl md:text-3xl font-display font-black text-brand-text leading-tight">
                        {{ $p->nama ?? 'Paket Membership' }}
                    </h1>
                    <p class="mt-2 text-sm text-brand-textSoft">
                        Buat transaksi terlebih dahulu. Setelah itu Anda bisa membuka payment gateway dan upload bukti.
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-brand-textSoft">Total</p>
                    <p class="mt-1 text-2xl font-display font-extrabold text-gold-500">
                        Rp {{ $fmt($p->harga ?? 0) }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('member.paket_membership.checkout.store', $p->id) }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-brand-text mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="cursor-pointer rounded-2xl border border-brand-borderSoft bg-brand-shell hover:bg-brand-surface-100 transition p-4 flex items-center gap-3">
                            <input type="radio" name="metode_pembayaran" value="transfer" class="hidden" checked>
                            <div class="w-10 h-10 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center">
                                <i data-lucide="landmark" class="w-5 h-5 text-gold-500"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-brand-text font-bold">Transfer</div>
                                <div class="text-xs text-brand-textSoft">Pilih rekening tujuan di payment gateway.</div>
                            </div>
                        </label>

                        <label class="cursor-pointer rounded-2xl border border-brand-borderSoft bg-brand-shell hover:bg-brand-surface-100 transition p-4 flex items-center gap-3">
                            <input type="radio" name="metode_pembayaran" value="qris" class="hidden">
                            <div class="w-10 h-10 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center">
                                <i data-lucide="qr-code" class="w-5 h-5 text-gold-500"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-brand-text font-bold">QRIS</div>
                                <div class="text-xs text-brand-textSoft">Tampilkan QR di payment gateway.</div>
                            </div>
                        </label>
                    </div>
                    @error('metode_pembayaran')
                        <div class="mt-2 text-sm text-red-500 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-brand-text mb-2">Catatan (opsional)</label>
                    <input
                        name="keterangan"
                        value="{{ old('keterangan') }}"
                        class="w-full px-4 py-4 rounded-2xl bg-brand-shell border border-brand-borderSoft
                               text-brand-text placeholder:text-brand-textSoft/70
                               focus:outline-none focus:ring-2 focus:ring-gold-500/25 focus:border-gold-500/30 transition"
                        placeholder="Contoh: Nama pengirim / info tambahan"
                    >
                    @error('keterangan')
                        <div class="mt-2 text-sm text-red-500 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <button
                    class="w-full py-4 rounded-2xl font-bold font-heading
                           text-brand-nav transition flex items-center justify-center gap-2
                           bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700
                           border border-gold-500/20 shadow-gold-glow hover:-translate-y-[1px]"
                >
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    Buat Transaksi
                </button>

                <div class="text-xs text-brand-textSoft leading-relaxed">
                    Setelah transaksi dibuat, Anda akan diarahkan ke halaman status transaksi untuk pembayaran dan upload bukti.
                </div>
            </form>
        </div>
    </div>

</x-layouts.member>

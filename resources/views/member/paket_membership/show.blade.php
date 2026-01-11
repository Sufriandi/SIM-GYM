{{-- resources/views/member/paket_membership/show.blade.php --}}

@php
    $p = $paketMembership;

    $fmt = fn($v) => number_format((int)$v, 0, ',', '.');

    $tipeLabel = fn($t) => match ((string)$t) {
        'double' => 'DOUBLE',
        'triple' => 'TRIPLE',
        default  => 'SINGLE',
    };

    $tipeInfo = fn($t) => match ((string)$t) {
        'double' => '2 Orang (Group)',
        'triple' => '3 Orang (Group)',
        default  => '1 Orang',
    };
@endphp

<x-layouts.member :pageTitle="($p->nama ?? 'Detail Paket')">

    <style>
        .surface-card{
            background: linear-gradient(180deg, rgba(246,239,228,.92), rgba(246,239,228,.78));
            border: 1px solid rgba(212,167,87,.22);
            box-shadow: 0 16px 44px rgba(20, 22, 26, .10);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .surface-card:hover{
            transform: translateY(-2px);
            border-color: rgba(234,179,8,.40);
            box-shadow: 0 26px 70px rgba(20, 22, 26, .14);
        }
        .dark .surface-card{
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(212,167,87,.14);
            box-shadow: 0 18px 50px rgba(0,0,0,.45);
        }
        .dark .surface-card:hover{
            border-color: rgba(212,167,87,.22);
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
        }
        .chip-soft{
            background: rgba(255,255,255,.55);
            border: 1px solid rgba(212,167,87,.20);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .chip-soft{
            background: rgba(0,0,0,.22);
            border: 1px solid rgba(212,167,87,.14);
        }
    </style>

    <section class="relative">
        <div class="absolute inset-0 -z-10 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-b from-brand-shell/60 via-transparent to-transparent dark:from-black/30"></div>
            <div class="absolute top-[-120px] right-[-120px] w-[520px] h-[520px] bg-gold-500/10 rounded-full blur-[120px] opacity-70"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 pb-10">

            {{-- Top breadcrumb --}}
            <div class="flex items-center justify-between gap-4 sm:gap-6 mb-8 pb-4 border-b border-brand-borderSoft/35 dark:border-brand-borderSoft/10 min-w-0">
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.15em] text-brand-nav/55 dark:text-brand-silver/60 min-w-0 flex-1">
                    <a href="{{ route('member.paket_membership.index') }}"
                       class="hover:text-gold-600 dark:hover:text-gold-500 flex items-center gap-1 transition-colors flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                        </svg>
                        Paket Membership
                    </a>
                    <span class="text-brand-borderSoft/55 dark:text-brand-borderSoft/40 flex-shrink-0">/</span>
                    <span class="text-brand-nav/80 dark:text-brand-silver truncate min-w-0">
                        {{ $p->nama ?? 'Paket' }}
                    </span>
                </nav>
            </div>

            <div class="grid lg:grid-cols-12 gap-8 lg:gap-14 items-start">

                {{-- Sticky summary (tanpa gambar) --}}
                <div class="lg:col-span-4 lg:sticky lg:top-24">
                    <div class="rounded-3xl surface-card p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-bold tracking-[0.18em] uppercase
                                             bg-gold-500/90 text-brand-nav border border-gold-500/20 shadow-gold-glow">
                                    {{ $tipeLabel($p->tipe ?? 'single') }}
                                </span>
                                <h1 class="mt-4 text-2xl font-display font-black text-brand-nav dark:text-white leading-[1.05] tracking-tight break-words">
                                    {{ $p->nama ?? 'Paket Membership' }}
                                </h1>
                                <p class="mt-2 text-[11px] text-brand-nav/55 dark:text-brand-silver/60">
                                    {{ $tipeInfo($p->tipe ?? 'single') }} • {{ (int)($p->durasi ?? 0) }} hari
                                </p>
                            </div>

                            <div class="w-11 h-11 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center shrink-0">
                                <i data-lucide="badge-check" class="w-5 h-5 text-gold-600 dark:text-gold-500"></i>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl chip-soft p-5">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-brand-nav/55 dark:text-brand-silver/70">Harga</p>
                            <p class="mt-1 text-3xl font-display font-extrabold text-brand-nav dark:text-gold-500">
                                Rp {{ $fmt($p->harga ?? 0) }}
                            </p>
                            <p class="mt-2 text-[11px] text-brand-nav/55 dark:text-brand-silver/70 leading-relaxed">
                                Membership aktif setelah admin memverifikasi pembayaran.
                            </p>
                        </div>

                        <div class="mt-6 hidden lg:block">
                            <a href="{{ route('member.paket_membership.checkout', $p->id) }}"
                               class="w-full py-4 bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold text-sm uppercase tracking-widest
                                      rounded-full transition-all shadow-[0_4px_20px_-5px_rgba(234,179,8,0.35)]
                                      hover:shadow-[0_8px_30px_-5px_rgba(234,179,8,0.45)] text-center block">
                                Checkout
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="lg:col-span-8 min-w-0">
                    <div class="mb-9">
                        <h3 class="text-xs font-bold text-brand-nav dark:text-white uppercase tracking-[0.2em] mb-4">
                            Paket Details
                        </h3>
                        <div class="text-brand-nav/70 dark:text-brand-silver/80 text-sm leading-relaxed text-justify">
                            {{ $p->deskripsi ?: 'Paket membership ini tersedia untuk pembelian. Buat transaksi, lakukan pembayaran, lalu upload bukti untuk verifikasi admin.' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-10">
                        <div class="p-3 rounded-2xl chip-soft text-center">
                            <p class="text-gold-600 dark:text-gold-500 text-xs font-bold uppercase mb-1">Secure</p>
                            <p class="text-[10px] text-brand-nav/60 dark:text-brand-silver">Verifikasi Admin</p>
                        </div>
                        <div class="p-3 rounded-2xl chip-soft text-center">
                            <p class="text-gold-600 dark:text-gold-500 text-xs font-bold uppercase mb-1">Duration</p>
                            <p class="text-[10px] text-brand-nav/60 dark:text-brand-silver">{{ (int)($p->durasi ?? 0) }} Hari</p>
                        </div>
                        <div class="p-3 rounded-2xl chip-soft text-center">
                            <p class="text-gold-600 dark:text-gold-500 text-xs font-bold uppercase mb-1">Pay</p>
                            <p class="text-[10px] text-brand-nav/60 dark:text-brand-silver">Transfer / QRIS</p>
                        </div>
                    </div>

                    {{-- Mobile fixed CTA --}}
                    <div class="fixed bottom-0 left-0 right-0 z-[90] lg:hidden
                                bg-brand-shell/92 dark:bg-black/70 backdrop-blur-md
                                border-t border-brand-borderSoft/35 dark:border-brand-borderSoft/15 px-4 py-4">
                        <a href="{{ route('member.paket_membership.checkout', $p->id) }}"
                           class="w-full py-4 bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold text-sm uppercase tracking-widest
                                  rounded-full transition-all shadow-[0_4px_20px_-5px_rgba(234,179,8,0.40)]
                                  hover:shadow-[0_8px_30px_-5px_rgba(234,179,8,0.50)] text-center block">
                            Checkout
                        </a>
                    </div>

                    <div class="h-24 lg:hidden"></div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.member>

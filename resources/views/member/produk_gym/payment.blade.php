{{-- resources/views/member/produk_gym/payment.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $fmt = fn($v) => number_format((float) $v, 0, ',', '.');

    $totalTagihan = (int) ($total ?? 0);
    $subtotalVal  = (int) ($subtotal ?? 0);
    $adminFeeVal  = (int) ($adminFee ?? 0);
    $orderId      = $orderId ?? ('TP-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)));

    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') return 'https://placehold.co/600x600/151515/cca43b?text=' . urlencode($fallbackText ?: 'PRODUK') . '&font=raleway';
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/')) $path = Str::after($path, 'public/');
        return Storage::url($path);
    };

    $qrisImg = !empty($qris?->path_gambar) ? Storage::url($qris->path_gambar) : '';
    $qrisNama = (string) ($qris?->nama_qris ?? 'BETA GYM');
    $firstBank = (!empty($rekenings) && count($rekenings) > 0) ? $rekenings->first() : null;

    $memberUser = Auth::user();
    $memberName = $memberUser?->name ?? 'Member BETA GYM';

    // Rincian item string untuk template WhatsApp
    $itemSummaryList = [];
    foreach (($cart ?? []) as $it) {
        $itemSummaryList[] = '• ' . ($it['name'] ?? 'Produk') . ' (' . ($it['quantity'] ?? 1) . 'x)';
    }
    $itemsSummaryText = implode("\n", $itemSummaryList);
@endphp

<x-layouts.member :pageTitle="'Payment Gateway – ' . $orderId" :pageSubtitle="'Selesaikan pembayaran pesanan produk Anda.'">

    <div class="max-w-6xl mx-auto px-2 sm:px-4 pb-20"
         x-data="paymentGatewayPage({
             total: {{ $totalTagihan }},
             orderId: @js($orderId),
             memberName: @js($memberName),
             waAdmin: @js($waAdmin),
             itemsSummary: @js($itemsSummaryText),
             initialBank: {
                 bank: @js($firstBank?->nama_bank ?? ''),
                 noRek: @js($firstBank?->nomor_rekening ?? ''),
                 atasNama: @js($firstBank?->nama_pemilik ?? '')
             },
             qris: {
                 img: @js($qrisImg),
                 nama: @js($qrisNama)
             }
         })"
         x-init="init()">

        {{-- Top Navigation & Breadcrumb --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-brand-borderSoft/40">
            <nav class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-text-muted">
                <a href="{{ route('member.produk_gym.index') }}" class="hover:text-gold-600 transition-colors">
                    Marketplace
                </a>
                <span>/</span>
                @if(!empty($isDirect))
                    <a href="{{ $backUrl ?? route('member.produk_gym.index') }}" class="hover:text-gold-600 transition-colors truncate max-w-[150px] sm:max-w-xs">
                        Detail Produk
                    </a>
                @else
                    <a href="{{ route('member.produk_gym.cart') }}" class="hover:text-gold-600 transition-colors">
                        Keranjang
                    </a>
                @endif
                <span>/</span>
                <span class="text-gold-600 dark:text-gold-400">Payment Gateway</span>
            </nav>

            <a href="{{ $backUrl ?? route('member.produk_gym.cart') }}"
               class="inline-flex items-center gap-2 text-xs font-bold text-text-muted hover:text-text-main transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                {{ $backText ?? 'Kembali ke Keranjang' }}
            </a>
        </div>

        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-2.5 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full bg-gold-500/10 text-gold-600 dark:text-gold-400 border border-gold-500/20 text-[10px] font-black uppercase tracking-widest">
                        {{ !empty($isDirect) ? 'BetaPay • Beli Langsung' : 'BetaPay Gateway' }}
                    </span>
                    <span class="text-xs font-mono text-text-muted">
                        No. Pesanan: <strong class="text-text-main">{{ $orderId }}</strong>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-black text-text-main uppercase tracking-tight">
                    Laman Pembayaran
                </h1>
                <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                    Pilih metode pembayaran (Transfer Bank atau QRIS) dan selesaikan transaksi sebelum batas waktu berakhir.
                </p>
            </div>

            {{-- Timer Pill --}}
            <div class="flex items-center gap-3 self-start md:self-auto">
                <div class="flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-amber-500/10 border border-amber-500/25 text-amber-700 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <div>
                        <span class="text-[9px] uppercase tracking-wider block leading-none font-bold">Batas Waktu Bayar</span>
                        <span class="text-sm font-mono font-black leading-none mt-0.5 block" x-text="timerDisplay">01:00:00</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Grid: Payment Gateway (Left) & Order Summary (Right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- LEFT: Payment Gateway Section --}}
            <div class="lg:col-span-7 space-y-6">

                <div class="surface-card rounded-3xl p-6 sm:p-7 border border-brand-borderSoft/80 shadow-xl overflow-hidden relative">
                    {{-- Ambient Background Glow --}}
                    <div class="absolute -top-16 -right-16 w-36 h-36 bg-gold-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    {{-- Merchant Brand Header --}}
                    <div class="flex items-center justify-between gap-4 pb-5 border-b border-brand-borderSoft/40 mb-6">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl overflow-hidden bg-black flex items-center justify-center flex-shrink-0 border border-brand-borderSoft/40 shadow-sm">
                                @if(!empty($merchantLogo))
                                    <img src="{{ $merchantLogo }}" alt="{{ $merchantName }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-gold-500 font-bold text-lg">B</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-text-main uppercase tracking-wider">{{ $merchantName }}</h3>
                                <p class="text-xs text-text-muted">Payment Gateway &bull; Order <span class="font-mono font-semibold">{{ $orderId }}</span></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            Secured
                        </div>
                    </div>

                    {{-- Total Tagihan Box --}}
                    <div class="rounded-2xl bg-gold-500/5 dark:bg-white/5 border border-gold-500/20 p-5 mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400 block mb-0.5">
                                Total Tagihan Pembayaran
                            </span>
                            <div class="text-2xl sm:text-3xl font-display font-black text-brand-nav dark:text-white tracking-tight">
                                Rp {{ $fmt($totalTagihan) }}
                            </div>
                            <span class="text-[10px] text-text-muted mt-0.5 block">
                                Estimasi bersih &bull; Tanpa biaya admin tambahan
                            </span>
                        </div>

                        <div class="text-right">
                            <button type="button" @click="copyText('{{ $totalTagihan }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gold-500/30 bg-white/70 dark:bg-black/40 hover:bg-gold-500/10 text-gold-700 dark:text-gold-400 text-xs font-bold transition-all shadow-sm active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                </svg>
                                Salin Jumlah
                            </button>
                        </div>
                    </div>

                    {{-- Metode Pembayaran Selector --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-text-muted">
                                Pilih Metode Pembayaran
                            </h4>
                            <span class="text-[11px] text-text-muted">
                                Metode aktif: <strong class="text-gold-600 dark:text-gold-400" x-text="activeMethod === 'bank' ? 'Transfer Bank' : 'QRIS'"></strong>
                            </span>
                        </div>

                        {{-- Dual Method Buttons --}}
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                    @click="switchMethod('bank')"
                                    :class="activeMethod === 'bank'
                                        ? 'border-gold-500 bg-gold-500/10 shadow-sm'
                                        : 'border-brand-borderSoft/70 hover:border-gold-500/40 bg-white/50 dark:bg-white/5'"
                                    class="p-4 rounded-2xl border-2 text-left transition-all duration-200 flex items-center gap-3">
                                <div :class="activeMethod === 'bank' ? 'bg-gold-500 text-brand-nav' : 'bg-brand-surface-50 text-text-muted'"
                                     class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="block text-xs font-black uppercase tracking-wider text-text-main">Transfer Bank</span>
                                    <span class="block text-[10px] text-text-muted truncate">Manual ke Rekening</span>
                                </div>
                            </button>

                            <button type="button"
                                    @click="switchMethod('qris')"
                                    :class="activeMethod === 'qris'
                                        ? 'border-gold-500 bg-gold-500/10 shadow-sm'
                                        : 'border-brand-borderSoft/70 hover:border-gold-500/40 bg-white/50 dark:bg-white/5'"
                                    class="p-4 rounded-2xl border-2 text-left transition-all duration-200 flex items-center gap-3">
                                <div :class="activeMethod === 'qris' ? 'bg-gold-500 text-brand-nav' : 'bg-brand-surface-50 text-text-muted'"
                                     class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="7" height="7" x="3" y="3"/><rect width="7" height="7" x="14" y="3"/><rect width="7" height="7" x="14" y="14"/><rect width="7" height="7" x="3" y="14"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="block text-xs font-black uppercase tracking-wider text-text-main">QRIS</span>
                                    <span class="block text-[10px] text-text-muted truncate">E-Wallet & Mobile</span>
                                </div>
                            </button>
                        </div>

                        {{-- CONTENT 1: TRANSFER BANK --}}
                        <div x-show="activeMethod === 'bank'" x-transition.opacity class="space-y-4 pt-2">
                            @if (!empty($rekenings) && count($rekenings) > 0)
                                <div class="space-y-3">
                                    <label class="block text-[11px] font-bold text-text-muted uppercase tracking-wider">
                                        Pilih Rekening Tujuan:
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach ($rekenings as $bank)
                                            <button type="button"
                                                    @click="selectBank(@js($bank->nama_bank), @js($bank->nomor_rekening), @js($bank->nama_pemilik))"
                                                    :class="selectedBank.bank === @js($bank->nama_bank)
                                                        ? 'border-gold-500 bg-gold-500/10'
                                                        : 'border-brand-borderSoft/60 hover:border-gold-500/30 bg-brand-surface-50/50'"
                                                    class="p-3.5 rounded-2xl border text-left flex items-center justify-between gap-3 transition-all">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="h-9 w-12 bg-white dark:bg-black/50 border border-brand-borderSoft/60 rounded-xl flex items-center justify-center text-[10px] font-black text-brand-nav dark:text-white uppercase flex-shrink-0">
                                                        {{ Str::upper(Str::substr($bank->nama_bank, 0, 4)) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold text-text-main truncate">{{ $bank->nama_bank }}</p>
                                                        <p class="text-[10px] text-text-muted font-mono truncate">{{ $bank->nomor_rekening }}</p>
                                                    </div>
                                                </div>
                                                <div :class="selectedBank.bank === @js($bank->nama_bank) ? 'border-gold-500 bg-gold-500 text-brand-nav' : 'border-brand-borderSoft text-transparent'"
                                                     class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Active Bank Details Box --}}
                                <div class="p-5 rounded-2xl bg-white/70 dark:bg-black/40 border border-brand-borderSoft/80 shadow-sm space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block">Bank Tujuan</span>
                                            <span class="text-base font-bold text-text-main" x-text="selectedBank.bank"></span>
                                        </div>
                                        <div class="px-2.5 py-1 rounded-lg bg-gold-500/10 text-gold-600 dark:text-gold-400 font-bold text-xs uppercase"
                                             x-text="(selectedBank.bank || 'BANK').substring(0, 6)"></div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl bg-brand-surface-50 dark:bg-white/5 border border-brand-borderSoft/60">
                                        <div>
                                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block">Nomor Rekening</span>
                                            <span class="font-mono text-xl font-black text-brand-nav dark:text-white tracking-wider select-all"
                                                  x-text="selectedBank.noRek"></span>
                                        </div>
                                        <button type="button" @click="copyText(selectedBank.noRek)"
                                                class="px-4 py-2 rounded-xl bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold text-xs uppercase tracking-wider transition-all active:scale-95 shadow-sm">
                                            Salin Rekening
                                        </button>
                                    </div>

                                    <div>
                                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block">Atas Nama Penerima</span>
                                        <span class="text-sm font-bold text-text-main" x-text="selectedBank.atasNama || 'BETA GYM'"></span>
                                    </div>
                                </div>

                                {{-- Step-by-step Instructions --}}
                                <div class="p-4 rounded-2xl bg-brand-surface-50/70 dark:bg-white/5 border border-brand-borderSoft/60">
                                    <h5 class="text-xs font-bold text-text-main uppercase tracking-wider mb-2">Petunjuk Pembayaran Transfer:</h5>
                                    <ol class="text-xs text-text-muted space-y-1.5 list-decimal pl-4 leading-relaxed">
                                        <li>Buka m-Banking atau kunjungi ATM bank Anda.</li>
                                        <li>Pilih menu transfer, masukkan nomor rekening tujuan di atas.</li>
                                        <li>Masukkan nominal <strong>tepat Rp {{ $fmt($totalTagihan) }}</strong> (agar verifikasi instan).</li>
                                        <li>Simpan bukti transfer Anda, lalu klik tombol konfirmasi hijau di bawah.</li>
                                    </ol>
                                </div>
                            @else
                                <div class="p-5 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-500 text-xs">
                                    Rekening bank belum diatur oleh admin. Silakan gunakan metode QRIS.
                                </div>
                            @endif
                        </div>

                        {{-- CONTENT 2: QRIS --}}
                        <div x-show="activeMethod === 'qris'" x-transition.opacity class="space-y-4 pt-2">
                            <div class="p-6 rounded-2xl bg-white dark:bg-[#15171e] border border-brand-borderSoft/80 text-center space-y-4 shadow-sm">
                                <template x-if="qris.img">
                                    <div>
                                        <div class="mx-auto w-64 h-64 bg-white p-4 rounded-2xl border-2 border-brand-borderSoft flex items-center justify-center shadow-md">
                                            <img :src="qris.img" :alt="qris.nama || 'QRIS'" class="w-full h-full object-contain">
                                        </div>

                                        <p class="mt-4 text-sm font-black text-brand-nav dark:text-white uppercase tracking-wider" x-text="qris.nama || 'BETA GYM'"></p>
                                        <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">
                                            Scan kode QRIS ini menggunakan aplikasi m-Banking (BCA Mobile, Livin Mandiri, BRImo, dll) atau E-Wallet (GoPay, OVO, DANA, ShopeePay, LinkAja).
                                        </p>

                                        <div class="mt-4 flex justify-center gap-3">
                                            <a :href="qris.img" download="QRIS-BETAGYM.png" target="_blank"
                                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gold-500/40 bg-gold-500/10 hover:bg-gold-500/20 text-gold-700 dark:text-gold-400 font-bold text-xs uppercase tracking-wider transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
                                                </svg>
                                                Unduh Gambar QRIS
                                            </a>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!qris.img">
                                    <div class="p-6 text-center text-text-muted text-xs">
                                        <svg class="w-10 h-10 mx-auto mb-2 text-text-muted/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <rect width="7" height="7" x="3" y="3"/><rect width="7" height="7" x="14" y="3"/><rect width="7" height="7" x="14" y="14"/><rect width="7" height="7" x="3" y="14"/>
                                        </svg>
                                        Gambar QRIS belum diatur oleh staf gym. Silakan gunakan Transfer Bank.
                                    </div>
                                </template>
                            </div>

                            <div class="p-4 rounded-2xl bg-brand-surface-50/70 dark:bg-white/5 border border-brand-borderSoft/60">
                                <h5 class="text-xs font-bold text-text-main uppercase tracking-wider mb-2">Petunjuk Pembayaran QRIS:</h5>
                                <ol class="text-xs text-text-muted space-y-1.5 list-decimal pl-4 leading-relaxed">
                                    <li>Buka aplikasi m-Banking atau E-Wallet pilihan Anda.</li>
                                    <li>Pilih menu scan QRIS dan arahkan ke kode di atas.</li>
                                    <li>Pastikan nama merchant tertera <strong>{{ $merchantName }}</strong>.</li>
                                    <li>Masukkan nominal <strong>Rp {{ $fmt($totalTagihan) }}</strong> lalu konfirmasi PIN Anda.</li>
                                    <li>Simpan bukti sukses, lalu klik tombol konfirmasi di bawah.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    {{-- Primary Action: Konfirmasi WhatsApp Button --}}
                    <div class="pt-6 mt-6 border-t border-brand-borderSoft/40 space-y-3">
                        <a :href="waLink" target="_blank"
                           class="w-full py-4 px-6 rounded-2xl bg-[#25D366] hover:bg-[#20ba5a] text-white font-black text-xs sm:text-sm uppercase tracking-wider shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/50 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                            </svg>
                            <span>Saya Sudah Bayar (Konfirmasi via WhatsApp)</span>
                        </a>

                        <p class="text-[11px] text-center text-text-muted">
                            Setelah melakukan transfer atau scan QRIS, klik tombol di atas untuk mengirim bukti bayar ke Admin Gym.
                        </p>
                    </div>

                    {{-- Powered by BetaPay Footer --}}
                    <div class="mt-6 pt-4 border-t border-brand-borderSoft/20 text-center">
                        <span class="text-[10px] uppercase tracking-widest font-bold text-text-muted/60">
                            Powered by BetaPay Payment Engine &bull; BETA GYM
                        </span>
                    </div>

                </div>

            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="lg:col-span-5 space-y-6">

                <div class="surface-card rounded-3xl p-6 border border-brand-borderSoft/80 shadow-xl lg:sticky lg:top-24 space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-brand-borderSoft/40">
                        <h3 class="font-heading font-black text-base text-text-main uppercase tracking-wider">
                            {{ !empty($isDirect) ? 'Produk yang Dibeli' : 'Rincian Pesanan' }}
                        </h3>
                        <span class="px-2.5 py-1 rounded-full bg-brand-surface-50 text-text-muted text-[11px] font-bold">
                            {{ $itemsCount }} Item
                        </span>
                    </div>

                    {{-- Item List --}}
                    <div class="space-y-3.5 max-h-80 overflow-y-auto pr-1">
                        @foreach(($cart ?? []) as $id => $item)
                            @php
                                $img = $imgUrl($item['photo'] ?? '', $item['name'] ?? 'ITEM');
                                $qty = (int) ($item['quantity'] ?? 1);
                                $price = (float) ($item['price'] ?? 0);
                                $line = $qty * $price;
                            @endphp
                            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-brand-surface-50/60 dark:bg-white/5 border border-brand-borderSoft/50">
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-black/10 flex-shrink-0 border border-brand-borderSoft/40">
                                    <img src="{{ $img }}" alt="{{ $item['name'] ?? 'Item' }}" class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] text-gold-600 dark:text-gold-400 font-bold uppercase tracking-wider truncate">
                                        {{ $item['category'] ?? 'PRODUK' }}
                                    </p>
                                    <h5 class="text-xs font-bold text-text-main truncate">
                                        {{ $item['name'] ?? 'Produk' }}
                                    </h5>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        {{ $qty }} unit &times; Rp {{ $fmt($price) }}
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="font-mono font-bold text-xs text-text-main block">
                                        Rp {{ $fmt($line) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Cost Calculation --}}
                    <div class="space-y-2.5 pt-4 border-t border-brand-borderSoft/40 text-xs">
                        <div class="flex justify-between text-text-muted">
                            <span>Subtotal Produk</span>
                            <span class="font-mono text-text-main font-semibold">Rp {{ $fmt($subtotalVal) }}</span>
                        </div>
                        <div class="flex justify-between text-text-muted">
                            <span>Biaya Layanan</span>
                            <span class="text-green-600 dark:text-green-400 font-semibold">Gratis (Rp 0)</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-3 border-t border-brand-borderSoft/60">
                            <span class="font-heading font-black text-sm text-text-main uppercase tracking-wider">Total Tagihan</span>
                            <span class="font-display font-black text-2xl text-gold-600 dark:text-gold-400">
                                Rp {{ $fmt($totalTagihan) }}
                            </span>
                        </div>
                    </div>

                    {{-- Reassurance & Pickup Highlights --}}
                    <div class="p-4 rounded-2xl bg-brand-surface-50/80 dark:bg-white/5 border border-brand-borderSoft/60 space-y-3 text-[11px] text-text-muted">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-gold-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <div>
                                <strong class="text-text-main block">Ambil di Kasir Gym</strong>
                                <span>Tunjukkan bukti pembayaran ke resepsionis BETA GYM saat mengambil produk.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            <div>
                                <strong class="text-text-main block">Jaminan 100% Asli</strong>
                                <span>Seluruh suplemen dan perlengkapan dijamin orisinalitas dan kualitasnya.</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- Toast Popup --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 pointer-events-none"
             x-show="toast.show"
             x-cloak
             x-transition.opacity>
            <div class="bg-black/90 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-2xl border border-white/10 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                <span x-text="toast.text"></span>
            </div>
        </div>

    </div>

    <script>
        function paymentGatewayPage(config) {
            return {
                total: config.total || 0,
                orderId: config.orderId || '',
                memberName: config.memberName || 'Member',
                waAdmin: config.waAdmin || '6281234567890',
                itemsSummary: config.itemsSummary || '',

                activeMethod: 'bank', // 'bank' or 'qris'
                selectedBank: {
                    bank: config.initialBank?.bank || 'BCA',
                    noRek: config.initialBank?.noRek || '',
                    atasNama: config.initialBank?.atasNama || 'BETA GYM'
                },
                qris: config.qris || {},

                timerDisplay: '01:00:00',
                timerInterval: null,
                duration: 3600,

                toast: {
                    show: false,
                    text: ''
                },
                toastTimer: null,

                init() {
                    this.startTimer();
                },

                switchMethod(m) {
                    this.activeMethod = m;
                },

                selectBank(b, n, a) {
                    this.selectedBank = {
                        bank: b || '',
                        noRek: n || '',
                        atasNama: a || 'BETA GYM'
                    };
                },

                startTimer() {
                    if (this.timerInterval) return;
                    this.timerInterval = setInterval(() => {
                        const h = Math.floor(this.duration / 3600);
                        const m = Math.floor((this.duration % 3600) / 60);
                        const s = this.duration % 60;

                        this.timerDisplay =
                            (h < 10 ? "0" + h : h) + ":" +
                            (m < 10 ? "0" + m : m) + ":" +
                            (s < 10 ? "0" + s : s);

                        this.duration--;

                        if (this.duration < 0) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                            this.timerDisplay = "EXPIRED";
                            this.showToast('Waktu pembayaran habis');
                        }
                    }, 1000);
                },

                idr(n) {
                    const num = Number(n || 0);
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                },

                get waLink() {
                    const methodText = this.activeMethod === 'bank'
                        ? ('Transfer Bank (' + (this.selectedBank.bank || 'Bank') + ')')
                        : 'QRIS';

                    const msg =
`Halo Admin BETA GYM,
Saya ingin konfirmasi pembayaran produk gym:
• No. Order: ${this.orderId}
• Nama: ${this.memberName}
• Total: ${this.idr(this.total)}
• Metode: ${methodText}
• Rincian Item:
${this.itemsSummary}

Bukti transfer telah saya lampirkan. Mohon diverifikasi. Terima kasih!`;

                    return `https://wa.me/${this.waAdmin}?text=${encodeURIComponent(msg)}`;
                },

                copyText(text) {
                    if (!text) return;
                    navigator.clipboard.writeText(String(text));
                    this.showToast('Berhasil disalin ke clipboard');
                },

                showToast(t) {
                    this.toast.text = t;
                    this.toast.show = true;
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 1800);
                }
            };
        }
    </script>

</x-layouts.member>

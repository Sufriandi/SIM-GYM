{{-- resources/views/member/produk_gym/cart.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $fmt = fn($v) => number_format((float)$v, 0, ',', '.');

    $totalTagihan = $total ?? 0;
    $orderId      = $orderId ?? 'ORD-' . strtoupper(Str::random(9));

    $qrisImg = !empty($qris?->path_gambar) ? Storage::url($qris->path_gambar) : '';

    $waAdmin = $waAdmin ?? null;
    $merchantName = $merchantName ?? 'BETA GYM';
    $merchantLogo = $merchantLogo ?? asset('images/logo.png');
@endphp

<x-layouts.member :pageTitle="'Keranjang & Checkout – BETA GYM'" :pageSubtitle="''">

    <div class="fixed inset-0 pointer-events-none z-0 bg-[#0a0a0a]">
        <div class="absolute -top-24 -right-24 w-[520px] h-[520px] bg-gold-500/10 rounded-full blur-[160px]"></div>
        <div class="absolute -bottom-24 -left-24 w-[520px] h-[520px] bg-white/5 rounded-full blur-[170px]"></div>
    </div>

    <section class="relative z-10 pb-16"
             x-data="cartPage()"
             x-init="init()"
             @keydown.escape.window="closeAll()">

        <div class="container mx-auto px-4 sm:px-6 max-w-6xl">

            <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8">
                <a href="{{ route('member.produk_gym.index') }}"
                   class="p-2 rounded-full border border-brand-borderSoft/20 text-brand-silver hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </a>

                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-display font-bold text-white truncate">Keranjang Belanja</h1>
                    <p class="text-xs sm:text-sm text-brand-silver/80">
                        Review item, lalu pilih metode pembayaran
                        <span class="text-brand-silver/60" x-show="hasItems" x-text="'• ' + itemsCount + ' item'"></span>
                    </p>
                </div>
            </div>

            <div x-show="hasItems" x-transition.opacity>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

                    <div class="lg:col-span-8 space-y-4">
                        @foreach(($cart ?? []) as $id => $details)
                            @php
                                $img = trim((string)($details['photo'] ?? ''));
                                if ($img !== '' && !Str::startsWith($img, ['http://','https://'])) $img = Storage::url($img);
                                if ($img === '') $img = 'https://placehold.co/600x600/111827/FACC15?text=ITEM';

                                $qty   = (int)($details['quantity'] ?? 1);
                                $price = (float)($details['price'] ?? 0);
                                $name  = (string)($details['name'] ?? 'Produk');
                                $cat   = (string)($details['category'] ?? 'ITEM');
                            @endphp

                            <div class="bg-[#151515] border border-brand-borderSoft/12 rounded-2xl p-4 sm:p-5"
                                 x-show="items['{{ (string)$id }}']"
                                 x-cloak
                                 x-transition.opacity>

                                <div class="flex gap-4">
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-black flex-shrink-0 border border-brand-borderSoft/10">
                                        <img src="{{ $img }}" class="w-full h-full object-cover" alt="Item">
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[10px] text-brand-silver uppercase tracking-wider mb-1">
                                                    {{ $cat }}
                                                </p>

                                                <h4 class="font-bold text-white text-base leading-tight truncate">
                                                    {{ $name }}
                                                </h4>

                                                <p class="text-xs text-brand-silver/70 mt-1">
                                                    Harga: <span class="text-white font-semibold">Rp {{ $fmt($price) }}</span>
                                                </p>
                                            </div>

                                            <button type="button"
                                                    @click="removeItem('{{ (string)$id }}')"
                                                    :disabled="loading['{{ (string)$id }}']"
                                                    class="p-2 rounded-lg text-brand-silver/50 hover:text-red-500 hover:bg-white/5 transition disabled:opacity-40"
                                                    aria-label="Hapus item">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                        @click="changeQty('{{ (string)$id }}','dec')"
                                                        :disabled="loading['{{ (string)$id }}']"
                                                        class="w-9 h-9 rounded-xl bg-white/5 border border-brand-borderSoft/15 text-white hover:bg-white/10 transition disabled:opacity-40">
                                                    -
                                                </button>

                                                <input type="number" min="1"
                                                       class="w-16 h-9 rounded-xl bg-black/30 border border-brand-borderSoft/15 text-white text-center"
                                                       :value="items['{{ (string)$id }}'] ? items['{{ (string)$id }}'].quantity : {{ $qty }}"
                                                       @input.debounce.450ms="setQty('{{ (string)$id }}', $event.target.value)"
                                                       :disabled="loading['{{ (string)$id }}']">

                                                <button type="button"
                                                        @click="changeQty('{{ (string)$id }}','inc')"
                                                        :disabled="loading['{{ (string)$id }}']"
                                                        class="w-9 h-9 rounded-xl bg-white/5 border border-brand-borderSoft/15 text-white hover:bg-white/10 transition disabled:opacity-40">
                                                    +
                                                </button>

                                                <span class="text-xs text-brand-silver/70 hidden sm:inline">
                                                    Update subtotal otomatis
                                                </span>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-[11px] text-brand-silver/70">Subtotal</p>
                                                <p class="text-gold-500 font-bold text-base" x-text="idr(lineTotal('{{ (string)$id }}'))"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <div class="lg:col-span-4">
                        <div class="bg-[#151515] border border-brand-borderSoft/20 p-5 sm:p-6 rounded-3xl lg:sticky lg:top-28">
                            <h3 class="font-bold text-white mb-5 text-lg">Rincian Biaya</h3>

                            <div class="space-y-3 mb-6 pb-6 border-b border-brand-borderSoft/10">
                                <div class="flex justify-between text-brand-silver text-sm">
                                    <span>Total Harga</span>
                                    <span class="text-white" x-text="idr(subtotal)"></span>
                                </div>
                                <div class="flex justify-between text-brand-silver text-sm">
                                    <span>Biaya Layanan</span>
                                    <span class="text-white" x-text="idr(adminFee)"></span>
                                </div>
                            </div>

                            <div class="flex justify-between mb-6 items-end">
                                <span class="text-brand-silver font-bold">Total Tagihan</span>
                                <span class="text-2xl font-display font-bold text-gold-500" x-text="idr(total)"></span>
                            </div>

                            <button type="button"
                                    @click="openGateway()"
                                    :disabled="!hasItems"
                                    class="w-full py-4 bg-gold-500 hover:bg-gold-400 disabled:bg-gold-500/50 disabled:cursor-not-allowed
                                           text-brand-nav font-bold rounded-xl transition shadow-lg flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                                </svg>
                                Pilih Pembayaran
                            </button>

                            <p class="text-[11px] text-brand-silver/70 mt-3">
                                Setelah bayar, klik “Saya Sudah Bayar” untuk konfirmasi.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <div x-show="!hasItems" x-transition.opacity x-cloak
                 class="text-center py-24 border border-dashed border-brand-borderSoft/20 rounded-3xl opacity-80">
                <p class="text-brand-silver">Keranjang kosong</p>
                <a href="{{ route('member.produk_gym.index') }}" class="text-gold-500 underline mt-2 inline-block">Belanja Dulu</a>
            </div>

        </div>

        {{-- PAYMENT MODAL --}}
        <template x-teleport="body">
            <div x-show="gatewayOpen" x-cloak class="fixed inset-0 z-[2147483647] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" x-transition.opacity @click="closeAll()"></div>

                <div class="relative w-full max-w-md sm:max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                     style="max-height: calc(100vh - 2rem);"
                     x-show="gatewayOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-2">

                    <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-b from-white to-gray-50">
    <div class="flex items-start justify-between gap-3">

        {{-- Brand --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl overflow-hidden bg-black flex items-center justify-center flex-shrink-0">
                @if (!empty($merchantLogo))
                    <img src="{{ $merchantLogo }}" alt="{{ $merchantName }}" class="w-full h-full object-cover">
                @else
                    <span class="text-gold-500 font-bold">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($merchantName, 0, 1)) }}
                    </span>
                @endif
            </div>

            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 truncate">
                    {{ $merchantName }}
                </p>

                <p class="text-[11px] text-gray-500 truncate">
                    <span x-text="step === 'menu' ? 'Payment Gateway' : 'Payment Instructions'"></span>
                    • Order <span class="font-mono">{{ $orderId }}</span>
                </p>
            </div>
        </div>

        {{-- Close --}}
        <button type="button" @click="closeAll()" class="text-gray-400 hover:text-red-500" aria-label="Tutup">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

    </div>

                        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Total Tagihan</p>
                                <p class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight" x-text="idr(total)"></p>

                                <p class="text-[11px] text-gray-500" x-show="step === 'inst'">
                                    Metode:
                                    <span class="font-semibold text-gray-800" x-text="selectedType === 'bank' ? 'Transfer Bank' : 'QRIS'"></span>
                                    <span class="text-gray-400">•</span>
                                    <span class="font-semibold text-gray-800" x-text="selectedTitle"></span>
                                </p>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-lg">
                                Secured
                            </span>
                        </div>

                        <div class="mt-3 flex items-center justify-between bg-blue-50 border border-blue-100 rounded-xl px-4 py-2" x-show="step === 'inst'">
                            <span class="text-xs text-blue-700 font-medium">Selesaikan dalam</span>
                            <span class="text-xs font-bold text-blue-800 font-mono" x-text="timerDisplay"></span>
                        </div>
                    </div>

                    <div class="p-4 overflow-y-auto custom-scrollbar-light flex-1">
                        <div x-show="step === 'menu'" x-transition.opacity>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 px-1">Metode Pembayaran</p>

                            <div class="bg-white border border-gray-200 rounded-xl mb-3 overflow-hidden">
                                <button type="button" @click="accordion = (accordion === 'bank' ? null : 'bank')"
                                        class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                                            </svg>
                                        </div>
                                        <div class="text-left">
                                            <p class="font-bold text-gray-900 text-sm">Transfer Bank</p>
                                            <p class="text-xs text-gray-500">Pilih rekening tujuan</p>
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="text-gray-400 transition-transform" :class="accordion === 'bank' ? 'rotate-180' : ''">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                </button>

                                <div x-show="accordion === 'bank'" x-transition.opacity class="border-t border-gray-100">
                                    @if(!empty($rekenings) && count($rekenings) > 0)
                                        @foreach($rekenings as $bank)
                                            <button type="button"
                                                    @click="selectMethod('bank', @js($bank->nama_bank), @js($bank->nomor_rekening), @js($bank->nama_pemilik))"
                                                    class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition border-b border-gray-100 last:border-0">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-8 w-12 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-[9px] font-bold text-gray-600 uppercase">
                                                        {{ Str::upper(Str::substr($bank->nama_bank, 0, 4)) }}
                                                    </div>
                                                    <div class="text-left">
                                                        <p class="text-sm font-semibold text-gray-800">{{ $bank->nama_bank }}</p>
                                                        <p class="text-xs text-gray-500">Transfer manual</p>
                                                    </div>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300">
                                                    <path d="m9 18 6-6-6-6"/>
                                                </svg>
                                            </button>
                                        @endforeach
                                    @else
                                        <div class="p-4 text-xs text-gray-500">Rekening bank belum tersedia.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                                <button type="button" @click="accordion = (accordion === 'qris' ? null : 'qris')"
                                        class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                                            </svg>
                                        </div>
                                        <div class="text-left">
                                            <p class="font-bold text-gray-900 text-sm">QRIS</p>
                                            <p class="text-xs text-gray-500">Tampilkan kode QR pembayaran</p>
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="text-gray-400 transition-transform" :class="accordion === 'qris' ? 'rotate-180' : ''">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                </button>

                                <div x-show="accordion === 'qris'" x-transition.opacity class="border-t border-gray-100">
                                    <button type="button"
                                            @click="selectMethod('qris', 'QRIS', @js($qrisImg), @js($qris->nama_qris ?? 'BETA GYM'))"
                                            class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-12 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-[9px] font-bold text-gray-600">QRIS</div>
                                            <div class="text-left">
                                                <p class="text-sm font-semibold text-gray-800">Tampilkan QR</p>
                                                <p class="text-xs text-gray-500">Bayar via e-wallet / m-banking</p>
                                            </div>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300">
                                            <path d="m9 18 6-6-6-6"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 'inst'" x-transition.opacity>
                            <div x-show="selectedType === 'bank'" x-transition.opacity>
                                <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs text-gray-600 font-bold uppercase">
                                            Rekening <span class="text-gray-900" x-text="selectedTitle"></span>
                                        </p>
                                        <div class="h-8 w-12 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-[9px] font-bold text-gray-600 uppercase"
                                             x-text="(selectedTitle || 'BANK').substring(0,4)"></div>
                                    </div>

                                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-100">
                                        <span class="font-mono text-lg sm:text-xl font-bold text-blue-700 tracking-wider" x-text="selectedValue"></span>
                                        <button type="button" @click="copyText(selectedValue)" class="text-xs font-bold text-gray-500 hover:text-blue-700 uppercase">Salin</button>
                                    </div>

                                    <p class="text-[11px] text-gray-600 mt-2">
                                        A.N <span class="text-gray-900 font-semibold" x-text="selectedExtra"></span>
                                    </p>
                                </div>

                                <div class="bg-white border border-gray-200 rounded-xl p-4">
                                    <p class="text-sm font-bold text-gray-900 mb-2">Petunjuk Transfer</p>
                                    <ol class="text-xs text-gray-600 leading-relaxed list-decimal pl-5 space-y-1">
                                        
                                        <li>Masukkan nomor rekening tujuan.</li>
                                        <li>Masukkan nominal sesuai total tagihan.</li>
                                        <li>Simpan bukti transfer untuk konfirmasi.</li>
                                    </ol>
                                </div>
                            </div>

                            <div x-show="selectedType === 'qris'" x-transition.opacity class="text-center">
                                <template x-if="selectedValue">
                                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                                        <div class="mx-auto w-56 h-56 bg-white border border-gray-200 rounded-2xl flex items-center justify-center overflow-hidden">
                                            <img :src="selectedValue" alt="QRIS" class="w-full h-full object-contain">
                                        </div>
                                        <p class="mt-4 text-sm font-bold text-gray-900" x-text="selectedExtra"></p>
                                        <p class="text-xs text-gray-600">Gunakan QR ini di e-wallet atau mobile banking Anda.</p>
                                    </div>
                                </template>

                                <template x-if="!selectedValue">
                                    <div class="bg-white border border-gray-200 rounded-xl p-5 text-left">
                                        <p class="text-sm font-bold text-gray-900 mb-1">QRIS belum tersedia</p>
                                        <p class="text-xs text-gray-600">Gambar QRIS belum diatur. Silakan gunakan Transfer Bank atau set QRIS dulu di admin.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <div class="p-4 bg-white border-t border-gray-200">
                        <a x-show="step === 'inst'" :href="waLink" target="_blank"
                           class="w-full py-3 bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold rounded-xl text-center shadow-lg transition flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                            Saya Sudah Bayar
                        </a>

                        <p class="text-[10px] text-gray-400 text-center mt-3" x-show="step === 'menu'">
                            Powered by BetaPay
                        </p>
                    </div>

                    <div class="absolute left-1/2 -translate-x-1/2 top-3 z-50" x-show="toast.show" x-transition.opacity>
                        <div class="bg-black/85 text-white text-xs px-3 py-2 rounded-lg shadow-lg">
                            <span x-text="toast.text"></span>
                        </div>
                    </div>

                </div>
            </div>
        </template>

        <style>
            .custom-scrollbar-light::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar-light::-webkit-scrollbar-track { background: #f1f1f1; }
            .custom-scrollbar-light::-webkit-scrollbar-thumb { background: #d7d7d7; border-radius: 999px; }
            [x-cloak] { display: none !important; }
        </style>

        <script>
            function cartPage() {
                const qtyUrlTpl = @js(route('member.produk_gym.cart.qty', ['id' => '__ID__']));
                const rmUrlTpl  = @js(route('member.produk_gym.cart.remove', ['id' => '__ID__']));

                return {
                    items: @js($cart ?? []),
                    subtotal: Number(@js((int)($subtotal ?? 0))),
                    adminFee: Number(@js((int)($adminFee ?? 0))), // selalu 0
                    total: Number(@js((int)($total ?? 0))),
                    loading: {},
                    csrf: @js(csrf_token()),

                    get hasItems() {
                        return Object.keys(this.items || {}).length > 0;
                    },
                    get itemsCount() {
                        let c = 0;
                        for (const k in (this.items || {})) c += Number(this.items[k]?.quantity || 0);
                        return c;
                    },
                    idr(n) {
                        const num = Number(n || 0);
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                    },
                    lineTotal(id) {
                        const it = (this.items || {})[id];
                        if (!it) return 0;
                        return Number(it.price || 0) * Number(it.quantity || 0);
                    },
                    syncTotals(payload) {
                        this.subtotal = Number(payload.subtotal || 0);
                        this.adminFee = Number(payload.admin_fee || 0);
                        this.total    = Number(payload.total || 0);
                    },
                    async postJson(url, body) {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(body || {})
                        });

                        let data = null;
                        try { data = await res.json(); } catch(e) {}

                        if (!res.ok || !data) throw (data || { message: 'Request gagal.' });
                        return data;
                    },

                    async changeQty(id, op) {
                        if (!this.items[id]) return;

                        this.loading[id] = true;
                        try {
                            const url = qtyUrlTpl.replace('__ID__', id);
                            const data = await this.postJson(url, { op });

                            if (!data.ok) { this.showToast(data.message || 'Gagal update qty'); return; }

                            if (data.removed) delete this.items[id];
                            else this.items[id].quantity = Number(data.quantity || this.items[id].quantity || 1);

                            this.syncTotals(data);
                            if (data.message) this.showToast(data.message);
                            if (!this.hasItems) this.closeAll();
                        } catch (e) {
                            this.showToast(e?.message || 'Gagal update qty');
                        } finally {
                            this.loading[id] = false;
                        }
                    },

                    async setQty(id, qty) {
                        if (!this.items[id]) return;

                        const val = Math.max(1, parseInt(qty || 1, 10));
                        this.loading[id] = true;

                        try {
                            const url = qtyUrlTpl.replace('__ID__', id);
                            const data = await this.postJson(url, { qty: val });

                            if (!data.ok) { this.showToast(data.message || 'Gagal update qty'); return; }

                            if (data.removed) delete this.items[id];
                            else this.items[id].quantity = Number(data.quantity || val);

                            this.syncTotals(data);
                            if (data.message) this.showToast(data.message);
                            if (!this.hasItems) this.closeAll();
                        } catch (e) {
                            this.showToast(e?.message || 'Gagal update qty');
                        } finally {
                            this.loading[id] = false;
                        }
                    },

                    async removeItem(id) {
                        if (!this.items[id]) return;

                        this.loading[id] = true;
                        try {
                            const url = rmUrlTpl.replace('__ID__', id);
                            const data = await this.postJson(url, {});

                            if (!data.ok) { this.showToast(data.message || 'Gagal hapus item'); return; }

                            delete this.items[id];
                            this.syncTotals(data);
                            if (data.message) this.showToast(data.message);
                            if (!this.hasItems) this.closeAll();
                        } catch (e) {
                            this.showToast(e?.message || 'Gagal hapus item');
                        } finally {
                            this.loading[id] = false;
                        }
                    },

                    gatewayOpen: false,
                    step: 'menu',
                    accordion: null,
                    selectedType: '',
                    selectedTitle: '',
                    selectedValue: '',
                    selectedExtra: '',
                    timerDisplay: '01:00:00',
                    timerInterval: null,
                    duration: 3600,
                    toast: { show: false, text: '' },
                    toastTimer: null,

                    init() {
                        this.$watch('gatewayOpen', () => {
                            const lock = this.gatewayOpen;
                            document.documentElement.classList.toggle('overflow-hidden', lock);
                            document.body.classList.toggle('overflow-hidden', lock);
                        });
                    },

                    openGateway() {
                        if (!this.hasItems) return;
                        this.gatewayOpen = true;
                        this.step = 'menu';
                        this.accordion = null;
                        this.clearSelected();
                        this.resetTimer();
                    },

                    selectMethod(type, title, value, extra) {
                        this.selectedType  = type;
                        this.selectedTitle = title || '';
                        this.selectedValue = value || '';
                        this.selectedExtra = extra || '';
                        this.step = 'inst';
                        this.accordion = null;
                        this.resetTimer();
                        this.startTimer();
                    },

                    clearSelected() {
                        this.selectedType = '';
                        this.selectedTitle = '';
                        this.selectedValue = '';
                        this.selectedExtra = '';
                    },

                    closeAll() {
                        this.gatewayOpen = false;
                        this.step = 'menu';
                        this.accordion = null;
                        this.clearSelected();
                        this.resetTimer();
                    },

                    resetTimer() {
                        if (this.timerInterval) clearInterval(this.timerInterval);
                        this.timerInterval = null;
                        this.duration = 3600;
                        this.timerDisplay = '01:00:00';
                    },

                    startTimer() {
                        if (this.timerInterval) return;

                        this.timerInterval = setInterval(() => {
                            const h = Math.floor(this.duration / 3600);
                            const m = Math.floor((this.duration % 3600) / 60);
                            const s = this.duration % 60;

                            this.timerDisplay =
                                (h < 10 ? "0"+h : h) + ":" +
                                (m < 10 ? "0"+m : m) + ":" +
                                (s < 10 ? "0"+s : s);

                            this.duration--;

                            if (this.duration < 0) {
                                clearInterval(this.timerInterval);
                                this.timerInterval = null;
                                this.timerDisplay = "EXPIRED";
                                this.showToast('Waktu pembayaran habis');
                                this.closeAll();
                            }
                        }, 1000);
                    },

                    get waLink() {
                        const typeText = this.selectedType === 'bank' ? 'Transfer Bank' : 'QRIS';
                        const msg = `Halo Admin BETA GYM, saya sudah bayar ${typeText} senilai ${this.idr(this.total)} untuk Order {{ $orderId }}.`;
                        return `https://wa.me/{{ $waAdmin }}?text=${encodeURIComponent(msg)}`;
                    },

                    copyText(text) {
                        navigator.clipboard.writeText(text || '');
                        this.showToast('Disalin ke clipboard');
                    },

                    showToast(t) {
                        this.toast.text = t;
                        this.toast.show = true;
                        if (this.toastTimer) clearTimeout(this.toastTimer);
                        this.toastTimer = setTimeout(() => { this.toast.show = false }, 1200);
                    },
                }
            }
        </script>

    </section>

</x-layouts.member>

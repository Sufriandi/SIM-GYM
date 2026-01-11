{{-- resources/views/member/membership_transaksi/partials/payment_modal.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<template x-teleport="body">
    <div x-data="paymentGateway()" x-init="init()"
         x-show="open" x-cloak
         class="fixed inset-0 z-[2147483647] flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="close()">

        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" x-transition.opacity @click="close()"></div>

        <div class="relative flex w-full max-w-md flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:max-w-lg
                    dark:bg-neutral-900"
             style="max-height: calc(100vh - 2rem);"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">

            {{-- Header --}}
            <div class="border-b border-gray-200 bg-gradient-to-b from-white to-gray-50 px-5 py-4 dark:border-white/10 dark:from-neutral-900 dark:to-neutral-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-black font-bold text-gold-500">B</div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold text-gray-900 dark:text-white">BETA GYM</p>
                            <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                <span x-text="step === 'menu' ? 'Payment Gateway' : 'Payment Instructions'"></span>
                                • Order <span class="font-mono" x-text="orderId"></span>
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="close()"
                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                            aria-label="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Tagihan</p>
                        <p class="text-xl font-extrabold leading-tight text-gray-900 sm:text-2xl dark:text-white" x-text="idr(total)"></p>

                        <p class="text-[11px] text-gray-500 dark:text-gray-400" x-show="step === 'inst'">
                            Metode:
                            <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedType === 'bank' ? 'Transfer Bank' : 'QRIS'"></span>
                            <span class="text-gray-400">•</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedTitle"></span>
                        </p>
                    </div>
                    <span class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700
                                 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300">
                        Secured
                    </span>
                </div>

                <div class="mt-3 flex items-center justify-between rounded-xl border border-blue-100 bg-blue-50 px-4 py-2
                            dark:border-blue-900/30 dark:bg-blue-900/15"
                     x-show="step === 'inst'">
                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Selesaikan dalam</span>
                    <span class="font-mono text-xs font-bold text-blue-800 dark:text-blue-200" x-text="timerDisplay"></span>
                </div>
            </div>

            {{-- Body --}}
            <div class="custom-scrollbar flex-1 overflow-y-auto p-4">

                {{-- MENU --}}
                <div x-show="step === 'menu'" x-transition.opacity>
                    <p class="mb-3 px-1 text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-400">
                        Metode Pembayaran
                    </p>

                    {{-- BANK --}}
                    <div class="mb-3 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/5">
                        <button type="button" @click="accordion = (accordion === 'bank' ? null : 'bank')"
                                class="flex w-full items-center justify-between p-4 hover:bg-gray-50 transition dark:hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                                        <line x1="2" x2="22" y1="10" y2="10"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Transfer Bank</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih rekening tujuan</p>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="text-gray-400 transition-transform" :class="accordion === 'bank' ? 'rotate-180' : ''">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <div x-show="accordion === 'bank'" x-transition.opacity class="border-t border-gray-100 dark:border-white/10">
                            @if(!empty($rekenings) && count($rekenings) > 0)
                                @foreach($rekenings as $bank)
                                    <button type="button"
                                            @click="selectMethod('bank', @js($bank->nama_bank), @js($bank->nomor_rekening), @js($bank->nama_pemilik))"
                                            class="flex w-full items-center justify-between border-b border-gray-100 p-4 hover:bg-gray-50 transition last:border-0
                                                   dark:border-white/10 dark:hover:bg-white/10">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-12 items-center justify-center rounded-lg border border-gray-200 bg-white text-[9px] font-bold uppercase text-gray-600
                                                        dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                                                {{ Str::upper(Str::substr($bank->nama_bank, 0, 4)) }}
                                            </div>
                                            <div class="text-left">
                                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $bank->nama_bank }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Transfer manual</p>
                                            </div>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                             class="text-gray-300 dark:text-gray-500">
                                            <path d="m9 18 6-6-6-6"/>
                                        </svg>
                                    </button>
                                @endforeach
                            @else
                                <div class="p-4 text-xs text-gray-500 dark:text-gray-400">Rekening bank belum tersedia.</div>
                            @endif
                        </div>
                    </div>

                    {{-- QRIS --}}
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/5">
                        <button type="button" @click="accordion = (accordion === 'qris' ? null : 'qris')"
                                class="flex w-full items-center justify-between p-4 hover:bg-gray-50 transition dark:hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">QRIS</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tampilkan kode QR pembayaran</p>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="text-gray-400 transition-transform" :class="accordion === 'qris' ? 'rotate-180' : ''">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <div x-show="accordion === 'qris'" x-transition.opacity class="border-t border-gray-100 dark:border-white/10">
                            <button type="button"
                                    @click="selectMethod('qris', 'QRIS', @js($qrisImg), @js($qris->nama_qris ?? 'BETA GYM'))"
                                    class="flex w-full items-center justify-between p-4 hover:bg-gray-50 transition dark:hover:bg-white/10">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-12 items-center justify-center rounded-lg border border-gray-200 bg-white text-[9px] font-bold text-gray-600
                                                dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                                        QRIS
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Tampilkan QR</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Bayar via e-wallet / m-banking</p>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="text-gray-300 dark:text-gray-500">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- INSTRUCTIONS --}}
                <div x-show="step === 'inst'" x-transition.opacity>
                    {{-- BANK --}}
                    <div x-show="selectedType === 'bank'" x-transition.opacity>
                        <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase text-gray-600 dark:text-gray-300">
                                    Rekening <span class="text-gray-900 dark:text-white" x-text="selectedTitle"></span>
                                </p>
                                <div class="flex h-8 w-12 items-center justify-center rounded-lg border border-gray-200 bg-white text-[9px] font-bold uppercase text-gray-600
                                            dark:border-white/10 dark:bg-white/5 dark:text-gray-200"
                                     x-text="(selectedTitle || 'BANK').substring(0,4)"></div>
                            </div>

                            <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <span class="font-mono text-lg font-bold tracking-wider text-blue-700 sm:text-xl dark:text-blue-300" x-text="selectedValue"></span>
                                <button type="button" @click="copyText(selectedValue)"
                                        class="text-xs font-bold uppercase text-gray-500 hover:text-blue-700 dark:text-gray-300 dark:hover:text-blue-300">
                                    Salin
                                </button>
                            </div>

                            <p class="mt-2 text-[11px] text-gray-600 dark:text-gray-300/80">
                                A.N <span class="font-semibold text-gray-900 dark:text-white" x-text="selectedExtra"></span>
                            </p>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                            <p class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Petunjuk Transfer</p>
                            <ol class="list-decimal space-y-1 pl-5 text-xs leading-relaxed text-gray-600 dark:text-gray-300/80">
                                <li>Buka ATM / M-Banking.</li>
                                <li>Pilih menu transfer.</li>
                                <li>Masukkan nomor rekening tujuan di atas.</li>
                                <li>Masukkan nominal sesuai total tagihan.</li>
                                <li>Simpan bukti transfer untuk konfirmasi.</li>
                            </ol>
                        </div>
                    </div>

                    {{-- QRIS --}}
                    <div x-show="selectedType === 'qris'" x-transition.opacity class="text-center">
                        <template x-if="selectedValue">
                            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
                                <div class="mx-auto flex h-56 w-56 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white">
                                    <img :src="selectedValue" alt="QRIS" class="h-full w-full object-contain">
                                </div>
                                <p class="mt-4 text-sm font-bold text-gray-900 dark:text-white" x-text="selectedExtra"></p>
                                <p class="text-xs text-gray-600 dark:text-gray-300/80">Gunakan QR ini di e-wallet atau mobile banking Anda.</p>
                            </div>
                        </template>

                        <template x-if="!selectedValue">
                            <div class="rounded-xl border border-gray-200 bg-white p-5 text-left dark:border-white/10 dark:bg-white/5">
                                <p class="mb-1 text-sm font-bold text-gray-900 dark:text-white">QRIS belum tersedia</p>
                                <p class="text-xs text-gray-600 dark:text-gray-300/80">
                                    Gambar QRIS belum diatur. Silakan gunakan Transfer Bank atau set QRIS dulu di admin.
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="border-t border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-neutral-900">
                <a x-show="step === 'inst'" :href="waLink" target="_blank" rel="noopener"
                   class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] py-3 text-center font-bold text-white shadow-lg transition hover:brightness-95">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                    Saya Sudah Bayar
                </a>

                <p class="mt-3 text-center text-[10px] text-gray-400" x-show="step === 'menu'">
                    Powered by BetaPay
                </p>
            </div>

        </div>
    </div>
</template>

@once
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148,163,184,.55); border-radius: 999px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); }
    </style>

    <script>
        function paymentGateway() {
            return {
                open: false,

                total: 0,
                orderId: @js($orderId ?? ''),
                waAdmin: @js($waAdmin ?? ''),

                step: 'menu',
                accordion: null,
                selectedType: '',
                selectedTitle: '',
                selectedValue: '',
                selectedExtra: '',

                timerDisplay: '00:00:00',
                timerInterval: null,
                duration: 0,

                init() {
                    window.addEventListener('payment:open', (e) => {
                        const d = e?.detail || {};
                        this.total   = Number(d.total || 0);
                        this.orderId = String(d.orderId || this.orderId || '');
                        this.waAdmin = String(d.waAdmin || this.waAdmin || '');

                        const dur = Number(d.duration || 0);
                        this.duration = (dur > 0 ? dur : 3600);

                        this.openModal();
                    });

                    window.addEventListener('payment:close', () => this.close());

                    this.$watch?.('open', (val) => {
                        document.documentElement.classList.toggle('overflow-hidden', !!val);
                        document.body.classList.toggle('overflow-hidden', !!val);
                    });
                },

                openModal() {
                    this.open = true;
                    this.step = 'menu';
                    this.accordion = null;
                    this.clearSelected();
                    this.resetTimer();
                },

                close() {
                    this.open = false;
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

                resetTimer() {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timerInterval = null;
                    this.timerDisplay = this.formatTime(this.duration);
                },

                startTimer() {
                    if (this.timerInterval) return;

                    this.timerInterval = setInterval(() => {
                        this.timerDisplay = this.formatTime(this.duration);
                        this.duration--;

                        if (this.duration < 0) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                            this.timerDisplay = "EXPIRED";
                            this.close();
                        }
                    }, 1000);
                },

                formatTime(sec) {
                    sec = Math.max(0, Number(sec || 0));
                    const h = Math.floor(sec / 3600);
                    const m = Math.floor((sec % 3600) / 60);
                    const s = sec % 60;
                    return (h < 10 ? "0"+h : h) + ":" + (m < 10 ? "0"+m : m) + ":" + (s < 10 ? "0"+s : s);
                },

                idr(n) {
                    const num = Number(n || 0);
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                },

                get waLink() {
                    const typeText = this.selectedType === 'bank' ? 'Transfer Bank' : 'QRIS';
                    const msg = `Halo Admin BETA GYM, saya sudah bayar ${typeText} senilai ${this.idr(this.total)} untuk Order ${this.orderId}.`;
                    return `https://wa.me/${this.waAdmin}?text=${encodeURIComponent(msg)}`;
                },

                copyText(text) {
                    if (!text) return;
                    navigator.clipboard.writeText(text);
                },
            }
        }
    </script>
@endonce

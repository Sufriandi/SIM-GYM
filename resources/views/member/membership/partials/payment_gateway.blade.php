@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $totalTagihan  = (int) ($totalTagihan ?? 0);
    $orderId       = $orderId ?? ('MBR-' . strtoupper(Str::random(9)));
    $paketNama     = $paketNama ?? ($paketMembership->nama ?? 'Paket Membership');

    $rekenings = $rekenings ?? collect();
    $qris      = $qris ?? null;

    $qrisImg  = !empty($qris?->path_gambar) ? Storage::url($qris->path_gambar) : '';
    $qrisNama = (string) ($qris?->nama_qris ?? 'BETA GYM');

    $waAdmin      = $waAdmin ?? '6281234567890';
    $merchantName = $merchantName ?? 'BETA GYM';
    $merchantLogo = $merchantLogo ?? null;
@endphp

<div x-data="membershipPaymentGateway({
        total: {{ $totalTagihan }},
        orderId: @js($orderId),
        paketNama: @js($paketNama),
        waAdmin: @js($waAdmin),
        merchantName: @js($merchantName),
    })"
    x-init="init()"
    @keydown.escape.window="closeAll()"
    class="space-y-3">

    <button type="button"
            @click="openGateway()"
            class="w-full inline-flex justify-center items-center gap-2
                   rounded-full px-5 py-3
                   bg-gold-600 text-white text-sm font-semibold
                   hover:bg-gold-500 transition">
        <i data-lucide="credit-card" class="w-4 h-4"></i>
        Pilih Pembayaran
    </button>

    <p class="text-[11px] text-text-muted leading-relaxed">
        Setelah pembayaran selesai, klik “Saya Sudah Bayar” untuk konfirmasi ke admin.
    </p>

    <template x-teleport="body">
        <div x-show="gatewayOpen" x-cloak class="fixed inset-0 z-[2147483647] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-black/65 backdrop-blur-sm" x-transition.opacity @click="closeAll()"></div>

            <div class="relative w-full max-w-md sm:max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                 style="max-height: calc(100vh - 2rem);"
                 x-show="gatewayOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2">

                {{-- HEADER --}}
                <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-b from-white to-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-black flex items-center justify-center">
        @if(!empty($merchantLogo))
            <img src="{{ $merchantLogo }}" alt="{{ $merchantName }}" class="w-full h-full object-cover">
        @else
            <span class="text-gold-500 font-bold">
                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($merchantName, 0, 1)) }}
            </span>
        @endif
    </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 truncate" x-text="merchantName"></p>
                                <p class="text-[11px] text-gray-500 truncate">
                                    <span x-text="step === 'menu' ? 'Payment Gateway' : 'Payment Instructions'"></span>
                                    • Order <span class="font-mono" x-text="orderId"></span>
                                </p>
                            </div>
                        </div>

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

                {{-- BODY --}}
                <div class="p-4 overflow-y-auto custom-scrollbar-light flex-1">

                    {{-- MENU --}}
                    <div x-show="step === 'menu'" x-transition.opacity>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 px-1">
                            Metode Pembayaran
                        </p>

                        {{-- BANK --}}
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
                                @if($rekenings->count() > 0)
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

                        {{-- QRIS --}}
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
                                        @click="selectMethod('qris', 'QRIS', @js($qrisImg), @js($qrisNama))"
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

                    {{-- INSTRUCTIONS --}}
                    <div x-show="step === 'inst'" x-transition.opacity>

                        <div x-show="selectedType === 'bank'" x-transition.opacity>
                            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs text-gray-600 font-bold uppercase">
                                        Rekening <span class="text-gray-900" x-text="selectedTitle"></span>
                                    </p>
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
                                    <p class="text-xs text-gray-600">Gunakan QR ini di e-wallet atau mobile banking.</p>
                                </div>
                            </template>

                            <template x-if="!selectedValue">
                                <div class="bg-white border border-gray-200 rounded-xl p-5 text-left">
                                    <p class="text-sm font-bold text-gray-900 mb-1">QRIS belum tersedia</p>
                                    <p class="text-xs text-gray-600">Silakan gunakan Transfer Bank atau set QRIS di admin.</p>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="p-4 bg-white border-t border-gray-200">
                    <a x-show="step === 'inst'" :href="waLink" target="_blank"
                       class="w-full py-3 bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold rounded-xl text-center shadow-lg transition flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                        Saya Sudah Bayar
                    </a>
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
        function membershipPaymentGateway(cfg) {
            return {
                total: Number(cfg?.total || 0),
                orderId: String(cfg?.orderId || ''),
                paketNama: String(cfg?.paketNama || 'Membership'),
                waAdmin: String(cfg?.waAdmin || ''),
                merchantName: String(cfg?.merchantName || 'BETA GYM'),

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

                idr(n) {
                    const num = Number(n || 0);
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                },

                openGateway() {
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
                    const msg =
                        `Halo Admin ${this.merchantName}, saya sudah bayar untuk ${this.paketNama} ` +
                        `(${typeText}) senilai ${this.idr(this.total)}. ` +
                        `Order: ${this.orderId}. Terima kasih.`;

                    return `https://wa.me/${this.waAdmin}?text=${encodeURIComponent(msg)}`;
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
</div>

{{-- resources/views/marketplace/cart.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    // Helper Format Rupiah
    $fmt = fn($v) => number_format($v, 0, ',', '.');
    
    // Generate Order ID Dummy jika belum ada (Simulasi)
    $orderId = 'ORD-' . strtoupper(Str::random(8)) . '-' . date('dm');
    
    // Simulasi Biaya Admin (Standar Gateway)
    $adminFee = 2500;
    $grandTotal = $total + $adminFee;
@endphp

<x-layouts.guest title="Keranjang & Checkout – BETA GYM">

    <section class="relative min-h-screen pt-32 pb-20 bg-[#0a0a0a]">
        <div class="container mx-auto px-6 max-w-6xl">
            
            {{-- Header --}}
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('guest.marketplace.index') }}" class="p-2 rounded-full border border-brand-borderSoft/20 text-brand-silver hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <h1 class="text-2xl font-display font-bold text-white">Keranjang Belanja</h1>
            </div>

            @if(count($cart) > 0)
                <div class="grid lg:grid-cols-12 gap-8">
                    
                    {{-- LEFT: ITEM LIST (8 Cols) --}}
                    <div class="lg:col-span-8 space-y-4">
                        @foreach($cart as $id => $details)
                            <div class="flex gap-5 p-4 bg-[#151515] border border-brand-borderSoft/10 rounded-2xl items-center">
                                {{-- Image --}}
                                <div class="w-20 h-20 rounded-xl overflow-hidden bg-black flex-shrink-0 border border-brand-borderSoft/10">
                                    @php
                                        $img = $details['photo'];
                                        if (!Illuminate\Support\Str::startsWith($img, 'http')) $img = Storage::url($img);
                                    @endphp
                                    <img src="{{ $img }}" class="w-full h-full object-cover">
                                </div>
                                
                                {{-- Details --}}
                                <div class="flex-grow min-w-0">
                                    <p class="text-[10px] text-brand-silver uppercase tracking-wider mb-1">{{ $details['category'] ?? 'PRODUCT' }}</p>
                                    <h4 class="font-bold text-white text-base leading-tight truncate pr-4">{{ $details['name'] }}</h4>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-gold-500 font-bold">Rp {{ $fmt($details['price']) }}</p>
                                        <span class="text-xs text-brand-silver bg-brand-surface-200/20 px-2 py-1 rounded">x{{ $details['quantity'] }}</span>
                                    </div>
                                </div>

                                {{-- Remove --}}
                                <a href="{{ url('/cart/remove/'.$id) }}" class="p-2 text-brand-silver/40 hover:text-red-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- RIGHT: CHECKOUT SUMMARY (4 Cols) --}}
                    <div class="lg:col-span-4">
                        <div class="bg-[#151515] border border-brand-borderSoft/20 p-6 rounded-3xl sticky top-32">
                            <h3 class="font-bold text-white mb-6 text-lg">Rincian Biaya</h3>
                            
                            <div class="space-y-3 mb-6 pb-6 border-b border-brand-borderSoft/10">
                                <div class="flex justify-between text-brand-silver text-sm">
                                    <span>Total Harga ({{ count($cart) }} item)</span>
                                    <span class="text-white">Rp {{ $fmt($total) }}</span>
                                </div>
                                <div class="flex justify-between text-brand-silver text-sm">
                                    <span>Biaya Layanan</span>
                                    <span class="text-white">Rp {{ $fmt($adminFee) }}</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between mb-8 items-center">
                                <span class="text-brand-silver font-bold">Total Tagihan</span>
                                <span class="text-2xl font-display font-bold text-gold-500">Rp {{ $fmt($grandTotal) }}</span>
                            </div>

                            {{-- TOMBOL BAYAR -> MEMBUKA MODAL GATEWAY --}}
                            <button onclick="openPaymentModal()" 
                                    class="w-full py-4 bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold rounded-xl transition shadow-lg hover:shadow-gold-glow flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                Pilih Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-32 border border-dashed border-brand-borderSoft/20 rounded-3xl opacity-50">
                    <p class="text-brand-silver">Keranjang kosong</p>
                    <a href="{{ route('guest.marketplace.index') }}" class="text-gold-500 underline mt-2 inline-block">Belanja Dulu</a>
                </div>
            @endif
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- PAYMENT GATEWAY SIMULATOR (MODAL) --}}
    {{-- ========================================================= --}}
    {{-- Desain ini meniru style Midtrans / Payment Gateway Indo --}}
    
    <div id="paymentModal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity opacity-0" id="paymentBackdrop" onclick="closePaymentModal()"></div>

        {{-- Modal Panel (Slide Up/Fade In) --}}
        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div id="paymentContent" 
                 class="pointer-events-auto w-full max-w-md bg-[#181818] rounded-2xl shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300 border border-brand-borderSoft/20 flex flex-col max-h-[90vh]">
                
                {{-- 1. Gateway Header --}}
                <div class="bg-white px-6 py-4 flex items-center justify-between shadow-md z-10">
                    <div class="flex items-center gap-3">
                        {{-- Logo Gym Kecil --}}
                        <div class="w-8 h-8 bg-black rounded-full flex items-center justify-center text-gold-500 font-bold font-display">B</div>
                        <div>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Total Pembayaran</p>
                            <p class="text-lg font-bold text-gray-900 leading-none">Rp {{ $fmt($grandTotal) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Order ID</p>
                        <p class="text-xs font-mono text-gray-600 font-bold">{{ $orderId }}</p>
                    </div>
                </div>

                {{-- Countdown Timer Bar --}}
                <div class="bg-blue-50 px-6 py-2 flex items-center justify-between border-b border-blue-100">
                    <span class="text-xs text-blue-600 font-medium">Selesaikan dalam</span>
                    <span class="text-xs font-bold text-blue-700 font-mono" id="countdown">23:59:59</span>
                </div>

                {{-- 2. Gateway Body (Scrollable) --}}
                <div class="flex-grow overflow-y-auto bg-[#f8f9fa] p-4" x-data="{ selectedMethod: null }">
                    
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 px-2">Pilih Metode Pembayaran</p>

                    {{-- GROUP: TRANSFER BANK (ACCORDION) --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-4 overflow-hidden">
                        
                        {{-- Header Accordion --}}
                        <button @click="selectedMethod === 'bank' ? selectedMethod = null : selectedMethod = 'bank'" 
                                class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                </div>
                                <span class="font-bold text-gray-700 text-sm">Transfer Bank (Virtual Account)</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform duration-300" :class="selectedMethod === 'bank' ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        {{-- Content Accordion --}}
                        <div x-show="selectedMethod === 'bank'" x-collapse class="border-t border-gray-100 bg-gray-50">
                            @foreach($rekenings as $bank)
                                <div class="p-4 border-b border-gray-200 last:border-0 flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        {{-- Logo Bank Placeholder --}}
                                        <div class="w-12 h-8 bg-white border border-gray-200 rounded flex items-center justify-center font-black text-gray-600 text-[10px] uppercase shadow-sm">
                                            {{ strtoupper(substr($bank->nama_bank, 0, 4)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-700">{{ $bank->nama_bank }}</p>
                                            <p class="text-[10px] text-gray-500">Dicek Otomatis</p>
                                        </div>
                                    </div>
                                    {{-- Radio Custom untuk Memilih Bank --}}
                                    <label class="cursor-pointer">
                                        <input type="radio" name="bank_selection" class="peer sr-only" @click="$dispatch('open-instruction', {type: 'bank', number: '{{ $bank->nomor_rekening }}', name: '{{ $bank->nama_bank }}'})">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-green-500 peer-checked:bg-green-500 transition-all"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- GROUP: QRIS (ACCORDION) --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-4 overflow-hidden">
                        <button @click="selectedMethod === 'qris' ? selectedMethod = null : selectedMethod = 'qris'" 
                                class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-gray-100 text-gray-600 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                </div>
                                <span class="font-bold text-gray-700 text-sm">QRIS (Gopay, OVO, Dana)</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform duration-300" :class="selectedMethod === 'qris' ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <div x-show="selectedMethod === 'qris'" x-collapse class="border-t border-gray-100 bg-gray-50 p-4 text-center">
                            @if($qris)
                                <div class="bg-white p-4 rounded-xl border border-gray-200 inline-block shadow-sm">
                                    <img src="{{ Storage::url($qris->path_gambar) }}" alt="QRIS" class="w-40 h-40 object-contain mx-auto">
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Scan kode di atas dengan aplikasi pembayaran Anda.</p>
                                <p class="text-sm font-bold text-gray-800 mt-1">NMID: {{ $qris->nama_qris }}</p>
                                
                                <div class="mt-4">
                                    <a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20sudah%20bayar%20via%20QRIS%20untuk%20Order%20{{ $orderId }}" target="_blank" class="block w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold text-sm rounded-lg shadow-md transition">
                                        Saya Sudah Bayar
                                    </a>
                                </div>
                            @else
                                <p class="text-xs text-red-500">QRIS Sedang Gangguan</p>
                            @endif
                        </div>
                    </div>

                    {{-- DETAIL INSTRUCTION PANEL (Muncul saat bank dipilih) --}}
                    <div x-data="{ showInst: false, bankNum: '', bankName: '' }" 
                         @open-instruction.window="showInst = true; bankNum = $event.detail.number; bankName = $event.detail.name"
                         x-show="showInst" 
                         x-transition.opacity
                         class="fixed inset-0 z-[60] bg-white flex flex-col"
                         style="display: none;">
                        
                        {{-- Header Instruction --}}
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3 shadow-sm">
                            <button @click="showInst = false" class="text-gray-500 hover:text-gray-800"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>
                            <h3 class="font-bold text-gray-800">Transfer <span x-text="bankName"></span></h3>
                        </div>

                        {{-- Body Instruction --}}
                        <div class="p-6 flex-grow overflow-y-auto bg-gray-50">
                            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm text-center mb-6">
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">Total Tagihan</p>
                                <p class="text-3xl font-bold text-gray-800">Rp {{ $fmt($grandTotal) }}</p>
                            </div>

                            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mb-6">
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Nomor Rekening / VA</p>
                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <span class="font-mono text-xl font-bold text-blue-600 tracking-wider" x-text="bankNum"></span>
                                    <button @click="navigator.clipboard.writeText(bankNum); alert('Disalin!')" class="text-xs font-bold text-gray-500 hover:text-blue-600 uppercase">Salin</button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2">*Hanya menerima dari Bank <span x-text="bankName"></span></p>
                            </div>

                            <div class="text-center">
                                <p class="text-xs text-gray-500 mb-4">Setelah transfer, klik tombol di bawah untuk verifikasi.</p>
                                <a :href="'https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20sudah%20transfer%20ke%20'+bankName+'%20sebesar%20Rp%20{{ $fmt($grandTotal) }}%20untuk%20Order%20{{ $orderId }}'" 
                                   target="_blank" 
                                   class="block w-full py-4 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl shadow-lg transition">
                                    Konfirmasi Pembayaran
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPTS FOR MODAL --}}
    <script>
        function openPaymentModal() {
            const modal = document.getElementById('paymentModal');
            const backdrop = document.getElementById('paymentBackdrop');
            const content = document.getElementById('paymentContent');
            
            modal.classList.remove('hidden');
            // Trigger animation
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-95');
            }, 10);
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            const backdrop = document.getElementById('paymentBackdrop');
            const content = document.getElementById('paymentContent');

            backdrop.classList.add('opacity-0');
            content.classList.add('opacity-0', 'scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Timer Logic
        setInterval(function() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
            // Ini hanya visual dummy agar terlihat jalan
            // document.getElementById('countdown').innerText = "23:59:" + (59 - now.getSeconds()).toString().padStart(2, '0');
        }, 1000);
    </script>

</x-layouts.guest>
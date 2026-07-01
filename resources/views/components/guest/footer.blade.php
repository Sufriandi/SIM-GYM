{{-- resources/views/components/footer.blade.php --}}
@php
    $waNumber = env('WA_ADMIN_NUMBER', '6281234567890');
    $waUrl = "https://wa.me/{$waNumber}?text=" . urlencode('Halo Admin, saya butuh bantuan terkait Beta Gym.');
@endphp

<footer class="bg-[#050505] border-t border-white/10 pt-16 pb-8 text-sm relative">

    <div class="container mx-auto px-6">

        {{-- 
            GRID SYSTEM ENTERPRISE (12 Kolom)
            - Brand: 4 Kolom (Lebar)
            - Menu: 2 Kolom (Sempit, karena isinya pendek)
            - Jam Buka: 3 Kolom (Agak lebar, untuk menampung jam)
            - Ikuti Kami: 3 Kolom (Sisanya)
        --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-y-12 lg:gap-x-8 mb-12">

            {{-- 1. BRAND & CONTACT (Col-Span-4) --}}
            <div class="lg:col-span-4 space-y-5">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group w-fit">
                    <div
                        class="w-9 h-9 flex items-center justify-center transition-transform duration-300 group-hover:scale-105">
                        <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM" class="w-full h-full object-contain">
                    </div>
                    <span
                        class="font-display font-bold text-xl text-white tracking-wide group-hover:text-gold-500 transition-colors">BETA
                        GYM</span>
                </a>

                <p class="text-gray-400 leading-relaxed pr-6 text-xs">
                    Pusat kebugaran premium dengan fasilitas standar internasional dan komunitas yang suportif untuk
                    mencapai target Anda.
                </p>

                <div class="space-y-3 text-xs pt-2">
                    <div class="flex items-start gap-3 text-gray-400 group cursor-default">
                        <div class="mt-0.5 text-gold-500 group-hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <span class="group-hover:text-white transition-colors leading-relaxed">Jl. Jendral Sudirman No.
                            123, Bengkalis, Riau</span>
                    </div>

                    <a href="mailto:support@betagym.id" class="flex items-center gap-3 text-gray-400 group w-fit">
                        <div class="text-gold-500 group-hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                        </div>
                        <span class="text-xs group-hover:text-white transition-colors">support@betagym.id</span>
                    </a>

                    <a href="{{ $waUrl }}" target="_blank"
                        class="flex items-center gap-3 text-gray-400 group w-fit">
                        <div class="text-gold-500 group-hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                        </div>
                        <span class="text-xs group-hover:text-white transition-colors">+62
                            {{ substr($waNumber, 2) }}</span>
                    </a>
                </div>
            </div>

            {{-- 2. MENU UTAMA (Col-Span-2: Lebih Sempit) --}}
            <div class="lg:col-span-2">
                <h3
                    class="font-display font-bold text-white text-sm mb-5 uppercase tracking-widest border-b border-gold-500/30 pb-2 w-max">
                    Menu Utama</h3>
                <ul class="space-y-3 text-xs text-gray-400">
                    <li><a href="{{ url('/') }}"
                            class="hover:text-gold-500 hover:translate-x-1 transition-all duration-300 block">Beranda</a>
                    </li>
                    <li><a href="{{ url('/marketplace') }}"
                            class="hover:text-gold-500 hover:translate-x-1 transition-all duration-300 block">Marketplace</a>
                    </li>
                    <li><a href="{{ url('/coaches') }}"
                            class="hover:text-gold-500 hover:translate-x-1 transition-all duration-300 block">Pelatih</a>
                    </li>
                    <li><a href="{{ url('/dashboard') }}"
                            class="hover:text-gold-500 hover:translate-x-1 transition-all duration-300 block">Member
                            Area</a></li>
                </ul>
            </div>

            {{-- 3. JAM BUKA (Col-Span-3: Lebih Lebar) --}}
            <div class="lg:col-span-3">
                <h3
                    class="font-display font-bold text-white text-sm mb-5 uppercase tracking-widest border-b border-gold-500/30 pb-2 w-max">
                    Jam Buka</h3>
                <ul class="space-y-3 text-xs text-gray-400">
                    <li class="flex justify-between items-center border-b border-white/5 pb-2">
                        <span>Senin - Jumat</span>
                        <span class="text-white font-medium">06:00 - 22:00</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-white/5 pb-2">
                        <span>Sabtu</span>
                        <span class="text-white font-medium">07:00 - 20:00</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-white/5 pb-2">
                        <span>Minggu</span>
                        <span class="text-white font-medium">08:00 - 18:00</span>
                    </li>
                </ul>
            </div>

            {{-- 4. IKUTI KAMI (Col-Span-3) --}}
            <div class="lg:col-span-3">
                <h3
                    class="font-display font-bold text-white text-sm mb-5 uppercase tracking-widest border-b border-gold-500/30 pb-2 w-max">
                    Ikuti Kami</h3>
                <div class="flex gap-3 mb-5">
                    {{-- Instagram --}}
                    <a href="#" target="_blank" aria-label="Kunjungi Instagram BETA GYM"
                        class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-gradient-to-tr hover:from-purple-500 hover:to-pink-500 hover:text-white transition-all duration-300 border border-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                        </svg>
                    </a>

                    {{-- Facebook --}}
                    <a href="#" target="_blank" aria-label="Kunjungi Facebook BETA GYM"
                        class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition-all duration-300 border border-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                    </a>

                    {{-- TikTok --}}
                    <a href="#" target="_blank" aria-label="Kunjungi TikTok BETA GYM"
                        class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-black hover:text-white hover:border-white/20 transition-all duration-300 border border-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" />
                        </svg>
                    </a>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed">
                    Dapatkan tips latihan dan promo membership terbaru di sosial media kami.
                </p>
            </div>
        </div>

        {{-- COPYRIGHT  --}}
        <div class="border-t border-white/10 pt-6 mt-4 flex flex-col md:flex-row justify-between items-center gap-4">

            {{-- Copyright Text --}}
            <p class="text-gray-300 text-[10px] font-medium tracking-wide">
                &copy; {{ date('Y') }} BETA GYM. All rights reserved.
            </p>



        </div>
    </div>
</footer>

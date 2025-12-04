<footer class="bg-brand-footer text-brand-silver border-t border-brand-borderSoft/10 pt-20 pb-10 text-sm font-sans">
    <div class="container mx-auto px-6">

        {{-- TOP SECTION --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">

            {{-- Column 1: Brand --}}
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gold-500 flex items-center justify-center text-brand-nav font-display font-bold text-xl">B</div>
                    <span class="font-display font-bold text-2xl text-brand-white tracking-wide">BETA <span class="text-gold-500">GYM</span></span>
                </div>
                <p class="leading-relaxed text-sm text-brand-silver/80">
                    Premium gym dengan alat standar atlet dan komunitas solid. Tempat terbaik untuk membangun tubuh dan mentalmu.
                </p>
                
                {{-- Social Icons --}}
                <div class="flex gap-4">
                    @foreach(['instagram', 'facebook', 'youtube'] as $social)
                        <a href="#" class="w-10 h-10 rounded-full bg-brand-surface-200/5 border border-brand-borderSoft/5 flex items-center justify-center text-brand-silver hover:bg-gold-500 hover:text-brand-nav hover:border-gold-500 hover:-translate-y-1 transition-all duration-normal">
                            <i data-lucide="{{ $social }}" class="w-4 h-4"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Column 2: Navigasi --}}
            <div>
                <h4 class="font-display font-bold text-lg text-brand-white mb-6 uppercase tracking-wider">Navigasi</h4>
                <ul class="space-y-4">
                    @foreach(['Program Kelas', 'Fasilitas Gym', 'Membership', 'Promo Bulan Ini'] as $link)
                        <li>
                            <a href="#" class="hover:text-gold-500 transition-colors flex items-center gap-2 group text-brand-silver">
                                <i data-lucide="chevron-right" class="w-4 h-4 text-gold-500/50 group-hover:text-gold-500 transition-colors"></i>
                                {{ $link }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Column 3: Kontak --}}
            <div>
                <h4 class="font-display font-bold text-lg text-brand-white mb-6 uppercase tracking-wider">Hubungi Kami</h4>
                <ul class="space-y-5 text-brand-silver">
                    <li class="flex items-start gap-4">
                        <div class="mt-1 p-2 rounded-full bg-brand-surface-200/5 text-gold-500">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        <span class="leading-relaxed">Jl. Senopati Raya No. 88,<br>Jakarta Selatan, 12190</span>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="p-2 rounded-full bg-brand-surface-200/5 text-gold-500">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <span class="font-mono tracking-wide">+62 812 3456 7890</span>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="p-2 rounded-full bg-brand-surface-200/5 text-gold-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <span>hello@betagym.com</span>
                    </li>
                </ul>
            </div>

            {{-- Column 4: Jam Buka --}}
            <div>
                <h4 class="font-display font-bold text-lg text-brand-white mb-6 uppercase tracking-wider">Jam Buka</h4>
                <div class="space-y-4 bg-brand-nav p-6 rounded-2xl border border-brand-borderSoft/5">
                    <div class="flex justify-between border-b border-brand-borderSoft/10 pb-3">
                        <span class="text-brand-silver">Senin - Jumat</span>
                        <span class="text-gold-500 font-bold">06.00 - 23.00</span>
                    </div>
                    <div class="flex justify-between border-b border-brand-borderSoft/10 pb-3">
                        <span class="text-brand-silver">Sabtu</span>
                        <span class="text-gold-500 font-bold">07.00 - 21.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-brand-silver">Minggu</span>
                        <span class="text-gold-500 font-bold">08.00 - 20.00</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- BOTTOM BAR --}}
        <div class="border-t border-brand-borderSoft/10 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs text-brand-silver/60">
                © {{ now()->year }} BETA GYM Indonesia. All rights reserved.
            </p>
            <div class="flex gap-8 text-xs text-brand-silver/60 font-medium">
                <a href="#" class="hover:text-gold-500 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-gold-500 transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-gold-500 transition-colors">Sitemap</a>
            </div>
        </div>
    </div>
</footer>
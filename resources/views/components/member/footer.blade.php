{{-- resources/views/components/member/footer.blade.php --}}

<footer class="w-full border-t border-brand-borderSoft bg-brand-shell/80 backdrop-blur-sm mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-text-muted">
            
            {{-- BAGIAN KIRI: BRAND & COPYRIGHT --}}
            <div class="flex items-center gap-3 order-2 md:order-1">
                <span class="font-black tracking-wider text-text-main uppercase">
                    BETA <span class="text-gold-500">GYM</span>
                </span>

                <span class="w-1 h-1 rounded-full bg-brand-borderSoft"></span>

                <span class="font-medium text-text-main/80">
                    Member Area
                </span>

                <span class="w-1 h-1 rounded-full bg-brand-borderSoft"></span>

                <span class="flex items-center gap-1 opacity-70">
                    &copy; {{ now()->year }}
                </span>
            </div>

            {{-- BAGIAN TENGAH: MOTIVASI (Hidden di HP kecil biar ga penuh) --}}
            <div class="order-1 md:order-2 text-center hidden sm:block">
                <p class="opacity-60 italic">
                    "Fokus latihan & progres Anda, biarkan sistem mencatat sisanya."
                </p>
            </div>

            {{-- BAGIAN KANAN: BANTUAN (CHIP STYLE) --}}
            <div class="order-3 flex items-center">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-bg/50 border border-brand-borderSoft/50 shadow-sm transition-colors hover:border-gold-500/30">
                    <i data-lucide="life-buoy" class="w-3.5 h-3.5 text-gold-500"></i>
                    <span>Butuh bantuan?</span>
                    <span class="w-px h-3 bg-brand-borderSoft mx-1"></span>
                    <span class="font-semibold text-text-main">Hubungi Admin</span>
                </div>
            </div>

        </div>
    </div>
</footer>
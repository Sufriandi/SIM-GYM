{{-- resources/views/components/guest/footer.blade.php --}}

<footer class="bg-brand-footer text-brand-white border-t border-brand-borderStrong mt-10">
    <div class="container py-6 flex flex-col md:flex-row items-center justify-between gap-3 text-[11px]">
        <div>
            © {{ now()->year }} BETA GYM. All rights reserved.
        </div>
        <div class="flex gap-4">
            <a href="#" class="hover:text-gold-400">Kebijakan Privasi</a>
            <a href="#" class="hover:text-gold-400">Syarat & Ketentuan</a>
        </div>
    </div>
</footer>

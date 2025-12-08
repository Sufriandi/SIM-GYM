{{-- resources/views/member/kehadiran/scan_invalid.blade.php --}}
@php
    $pageTitle = 'QR Absensi Tidak Valid';
@endphp

<x-layouts.member :title="$pageTitle . ' – BETA GYM'">
    <div class="max-w-lg mx-auto py-10 px-4">
        <div class="bg-brand-card border border-danger-soft rounded-3xl shadow-card-soft p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 rounded-full bg-danger-soft flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-danger"></i>
                </div>
            </div>

            <h1 class="text-lg font-heading font-semibold text-brand-text mb-2">
                QR Absensi Tidak Dikenali
            </h1>

            <p class="text-sm text-brand-textSoft mb-4">
                Kode QR yang Anda akses tidak terhubung dengan periode absensi yang aktif.
                Silakan pastikan Anda memindai QR absensi terbaru di area gym.
            </p>

            

            <a href="{{ route('member.kehadiran.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-brand-nav text-gold-500 border border-brand-nav hover:bg-gold-500 hover:text-brand-nav transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Kembali ke Riwayat Kehadiran
            </a>
        </div>
    </div>
</x-layouts.member>

<x-layouts.member
    pageTitle="Absensi Latihan"
    pageSubtitle="Konfirmasi kehadiran Anda."
>
    <div class="max-w-lg mx-auto mt-6">
        <x-ui.card class="p-6 text-center">
            @if($status === 'success')
                <div class="text-success mb-2">
                    <i data-lucide="check-circle" class="w-10 h-10 mx-auto"></i>
                </div>
                <h2 class="text-lg font-semibold text-text-main mb-1">Absensi Berhasil</h2>
                <p class="text-sm text-text-muted">{{ $message }}</p>
            @elseif($status === 'already')
                <div class="text-warning mb-2">
                    <i data-lucide="alert-circle" class="w-10 h-10 mx-auto"></i>
                </div>
                <h2 class="text-lg font-semibold text-text-main mb-1">Anda Sudah Absen</h2>
                <p class="text-sm text-text-muted">{{ $message }}</p>
            @else
                <div class="text-danger mb-2">
                    <i data-lucide="x-circle" class="w-10 h-10 mx-auto"></i>
                </div>
                <h2 class="text-lg font-semibold text-text-main mb-1">Kode Tidak Valid</h2>
                <p class="text-sm text-text-muted">{{ $message }}</p>
            @endif

            @if($periode)
                <p class="text-xs text-text-muted mt-4">
                    Periode: {{ $periode->tanggal_mulai->translatedFormat('d M Y') }}
                    – {{ $periode->tanggal_selesai->translatedFormat('d M Y') }}
                </p>
            @endif
        </x-ui.card>
    </div>
</x-layouts.member>

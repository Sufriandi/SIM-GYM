{{-- resources/views/member/izin_latihan/detail.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    // Parsing tanggal
    $m = \Carbon\Carbon::parse($izin->tanggal_mulai);
    $s = \Carbon\Carbon::parse($izin->tanggal_selesai);
    $d = $izin->created_at ? \Carbon\Carbon::parse($izin->created_at) : null;

    // Title & subtitle
    $pageTitle    = $pageTitle    ?? 'Detail Izin Member';
    $pageSubtitle = $pageSubtitle ?? 'Lihat informasi lengkap pengajuan izin latihan.';

    // Cek bukti (file di kolom 'bukti_alasan')
    $buktiUrl = $izin->bukti_alasan ? Storage::url($izin->bukti_alasan) : null;
    $isImage  = false;
    $isPdf    = false;

    if ($izin->bukti_alasan) {
        $ext     = strtolower(pathinfo($izin->bukti_alasan, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
        $isPdf   = $ext === 'pdf';
    }

    // Normalisasi alasan
    $alasanTrimmed = trim($izin->alasan ?? '');
@endphp

<x-layouts.member
    :pageTitle="$pageTitle"
    :pageSubtitle="$pageSubtitle"
>
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- HEADER UTAMA --}}
        <x-ui.section-header
            :title="$pageTitle"
            :subtitle="$pageSubtitle"
        />

        <hr class="border-t border-brand-borderSoft mb-2">

        {{-- TOMBOL KEMBALI --}}
        <div class="flex items-center justify-between mb-2">
            <x-ui.back-button
                href="{{ route('member.izin_latihan.index') }}"
                text="Kembali ke Daftar"
            />
        </div>

        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <x-ui.toast type="success" class="mb-2">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        {{-- MAIN CARD TANPA BAYANG --}}
        <div
            class="relative w-full rounded-3xl border border-brand-borderSoft
                   bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell overflow-hidden"
        >
            {{-- HEADER CARD --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                       px-6 pt-6 pb-4 border-b-2 border-brand-borderSoft/80
                       bg-brand-shell/50 backdrop-blur-sm gap-3"
            >
                <div>
                    <h2 class="text-xl font-semibold text-text-main">
                        Detail Izin Member
                    </h2>
                    <p class="text-sm text-text-muted mt-0.5">
                        Lihat informasi lengkap pengajuan izin latihan.
                    </p>
                </div>

                {{-- Info diajukan pada --}}
                <div class="text-right space-y-1">
                    <p class="text-[10px] uppercase tracking-widest text-text-muted">
                        Diajukan pada
                    </p>
                    <p class="text-sm font-medium text-text-main">
                        {{ $d ? $d->translatedFormat('d F Y, H:i') : '-' }}
                    </p>
                </div>
            </div>

            {{-- ISI CARD --}}
            <div class="px-6 pb-8 pt-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- KOLOM KIRI: DATA & ALASAN --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- DATA PENGAJUAN --}}
                        <div
                            class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft
                                   px-5 py-5"
                        >
                            <h3 class="text-base font-semibold text-text-main mb-1 flex items-center gap-2">
                                <i data-lucide="calendar-range" class="w-4 h-4 text-gold-500"></i>
                                Data Pengajuan Izin
                            </h3>
                            <p
                                class="text-xs text-text-muted mb-4 border-b border-brand-borderSoft/50 pb-2
                                       leading-relaxed"
                            >
                                Detail permintaan izin yang diajukan member.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                {{-- Durasi --}}
                                <div>
                                    <p class="text-xs text-text-muted uppercase tracking-wider mb-1">
                                        Durasi Diajukan
                                    </p>
                                    <p class="text-xl font-heading font-bold text-text-main">
                                        {{ $izin->jumlah_hari }}
                                        <span class="text-sm font-normal text-text-muted">Hari</span>
                                    </p>
                                </div>

                                {{-- Tanggal Mulai --}}
                                <div>
                                    <p class="text-xs text-text-muted uppercase tracking-wider mb-1">
                                        Tanggal Mulai
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="calendar" class="w-4 h-4 text-text-muted"></i>
                                        <p class="text-base font-medium text-text-main">
                                            {{ $m->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Tanggal Selesai --}}
                                <div>
                                    <p class="text-xs text-text-muted uppercase tracking-wider mb-1">
                                        Tanggal Selesai
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="calendar-check" class="w-4 h-4 text-text-muted"></i>
                                        <p class="text-base font-medium text-text-main">
                                            {{ $s->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ALASAN MEMBER --}}
<div
    class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft
           px-5 py-5"
>
    <h3 class="text-base font-semibold text-text-main mb-1 flex items-center gap-2">
        <i data-lucide="align-left" class="w-4 h-4 text-gold-500"></i>
        Alasan Pengajuan Member
    </h3>

    @php
        $alasanText = trim($izin->alasan ?? '');
        if ($alasanText === '') {
            $alasanText = '-';
        }
    @endphp

    <div
        class="rounded-xl bg-brand-surface-50 border border-brand-borderSoft
               px-4 py-2 text-sm text-text-main leading-snug
               break-words whitespace-normal overflow-x-hidden"
    >
        {{ $alasanText }}
    </div>
</div>

                        {{-- CATATAN ADMIN (opsional) --}}
                        @if($izin->keterangan_admin)
                            <div
                                class="rounded-2xl border px-5 py-5
                                       {{ $izin->status === 'ditolak'
                                            ? 'bg-danger/5 border-danger/20'
                                            : 'bg-success/5 border-success/20' }}"
                            >
                                <h3
                                    class="text-base font-semibold mb-2 flex items-center gap-2
                                           {{ $izin->status === 'ditolak' ? 'text-danger' : 'text-success' }}"
                                >
                                    <i data-lucide="message-square" class="w-4 h-4"></i>
                                    Catatan Admin
                                </h3>

                                <p
                                    class="text-sm text-text-main italic px-4 py-2.5 rounded-xl
                                           bg-brand-bg/50 break-words whitespace-pre-line"
                                >
                                    "{{ $izin->keterangan_admin }}"
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- KOLOM KANAN: BUKTI --}}
                    <div class="space-y-6">

                        <div
                            class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft
                                   px-5 py-5"
                        >
                            <h3 class="text-base font-semibold text-text-main mb-3 flex items-center gap-2">
                                <i data-lucide="paperclip" class="w-4 h-4 text-gold-500"></i>
                                Bukti Alasan
                            </h3>

                            @if ($buktiUrl)
                                @if ($isPdf)
                                    <div
                                        class="w-full h-40 rounded-xl overflow-hidden border border-brand-borderSoft
                                               bg-brand-surface-50 mb-4 flex items-center justify-center"
                                    >
                                        <div class="flex flex-col items-center justify-center">
                                            <i data-lucide="file-text" class="w-10 h-10 text-gold-500 mb-2"></i>
                                            <span class="text-xs text-text-muted font-bold">
                                                Dokumen PDF
                                            </span>
                                        </div>
                                    </div>
                                @elseif ($isImage)
                                    <div
                                        class="w-full h-48 rounded-xl overflow-hidden border border-brand-borderSoft
                                               bg-brand-surface-50 mb-4 group relative"
                                    >
                                        <img
                                            src="{{ $buktiUrl }}"
                                            alt="Bukti Izin"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        >
                                        <div
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100
                                                   transition-opacity flex items-center justify-center"
                                        >
                                            <span
                                                class="text-white text-xs font-bold bg-black/50 px-3 py-1 rounded-full
                                                       backdrop-blur-sm"
                                            >
                                                Lihat Gambar
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <a href="{{ $buktiUrl }}" target="_blank" class="block">
                                    <x-ui.button-primary class="w-full justify-center text-sm">
                                        <i data-lucide="external-link" class="w-4 h-4 mr-2"></i>
                                        Buka Bukti di Tab Baru
                                    </x-ui.button-primary>
                                </a>
                            @else
                                <div
                                    class="p-6 rounded-xl border border-dashed border-brand-borderSoft
                                           bg-brand-surface-50 flex flex-col items-center justify-center text-center"
                                >
                                    <i data-lucide="image-off" class="w-8 h-8 text-text-muted/40 mb-2"></i>
                                    <p class="text-xs text-text-muted italic">
                                        Tidak ada bukti dilampirkan.
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Tidak ada blok aksi / pembatalan di sisi member --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.member>

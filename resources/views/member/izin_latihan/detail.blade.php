{{-- resources/views/member/izin_latihan/detail.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    // Parsing tanggal
    $mulai      = Carbon::parse($izin->tanggal_mulai);
    $selesai    = Carbon::parse($izin->tanggal_selesai);
    $diajukanAt = $izin->created_at ? Carbon::parse($izin->created_at) : null;

    // Title & subtitle (penamaan disamakan: Kompensasi)
    $pageTitle    = $pageTitle ?? 'Detail Pengajuan Kompensasi';
    $pageSubtitle = $pageSubtitle ?? 'Lihat informasi lengkap pengajuan kompensasi membership Anda.';

    // Bukti
    $buktiUrl = $izin->bukti_alasan ? Storage::url($izin->bukti_alasan) : null;
    $isImage  = false;
    $isPdf    = false;

    if ($izin->bukti_alasan) {
        $ext     = strtolower(pathinfo($izin->bukti_alasan, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
        $isPdf   = $ext === 'pdf';
    }

    // Status meta (badge)
    $badgeVariant = match ($izin->status) {
        'disetujui' => 'success',
        'ditolak'   => 'danger',
        default     => 'warning',
    };

    $statusLabel = match ($izin->status) {
        'disetujui' => 'DISETUJUI',
        'ditolak'   => 'DITOLAK',
        default     => 'PENDING',
    };

    // Processed
    $processedAt = $izin->tanggal_persetujuan ? Carbon::parse($izin->tanggal_persetujuan) : null;

    $approvedDays = ($izin->status === 'disetujui')
        ? (int) ($izin->durasi_izin_disetujui ?? 0)
        : 0;

    $adminNote = trim((string) ($izin->keterangan_admin ?? ''));

    $showAdminBox =
        $izin->status !== 'pending'
        || $adminNote !== ''
        || $izin->tanggal_persetujuan
        || $izin->durasi_izin_disetujui !== null;

    // Normalisasi alasan
    $alasanRaw = $izin->alasan ?? '';
    $alasanTrimmed = trim($alasanRaw);
    $alasanHtml = $alasanTrimmed !== '' ? nl2br(e($alasanTrimmed)) : '-';

    $from = request('from', 'index'); // index | history
    $backHref = $from === 'history'
        ? route('member.izin_latihan.history')
        : route('member.izin_latihan.index');

    $backText = $from === 'history'
        ? 'Kembali ke Riwayat'
        : 'Kembali ke Daftar';
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
                href="{{ $backHref }}"
    text="{{ $backText }}"
            />
        </div>

        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <x-ui.toast type="success" class="mb-2">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        {{-- MAIN CARD (mengikuti style admin) --}}
        <div
            class="relative w-full rounded-3xl border border-brand-borderSoft
                   bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell overflow-hidden"
        >
            {{-- HEADER CARD (tanpa Member:, tetap ada Diajukan pada) --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                       px-6 pt-6 pb-4 border-b-2 border-brand-borderSoft/80
                       bg-brand-shell/50 backdrop-blur-sm gap-3"
            >
                <div>
                    <h2 class="text-xl font-semibold text-text-main">
                        Detail Pengajuan Kompensasi
                    </h2>
                    <p class="text-sm text-text-muted mt-0.5">
                        Ringkasan detail pengajuan kompensasi membership Anda.
                    </p>
                </div>

                {{-- Info diajukan pada --}}
                <div class="text-right space-y-1">
                    <p class="text-[10px] uppercase tracking-widest text-text-muted">
                        Diajukan pada
                    </p>
                    <p class="text-sm font-medium text-text-main">
                        {{ $diajukanAt ? $diajukanAt->translatedFormat('d F Y, H:i') : '-' }}
                    </p>
                </div>
            </div>

            {{-- ISI CARD --}}
            <div class="px-6 pb-8 pt-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- KIRI: DATA & ALASAN --}}
                    <div class="lg:col-span-2 space-y-4">

                        {{-- DATA PENGAJUAN (tanpa Akhir Membership) --}}
                        <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-text-main mb-1">
                                        Data Pengajuan Kompensasi
                                    </h3>
                                    <p class="text-xs text-text-muted mb-4">
                                        Detail periode kompensasi yang Anda ajukan.
                                    </p>
                                </div>

                                <x-ui.badge :variant="$badgeVariant">
                                    {{ $statusLabel }}
                                </x-ui.badge>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-text-muted">Durasi Diajukan</p>
                                    <p class="text-lg font-semibold text-text-main">
                                        {{ (int) ($izin->jumlah_hari ?? 0) }} Hari
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-text-muted">Tanggal Mulai</p>
                                    <p class="text-lg text-text-main">
                                        {{ $mulai->translatedFormat('d F Y') }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-text-muted">Tanggal Selesai</p>
                                    <p class="text-lg text-text-main">
                                        {{ $selesai->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- ALASAN MEMBER --}}
                        <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                            <h3 class="text-base font-semibold text-text-main mb-2">
                                Alasan Pengajuan Kompensasi
                            </h3>

                            <div class="mt-1 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft text-sm text-text-main min-h-[80px]">
                                {!! $alasanHtml !!}
                            </div>
                        </div>

                        {{-- HASIL PEMROSESAN ADMIN (tampil jika sudah diproses / ada catatan) --}}
                        @if($showAdminBox)
                            <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-base font-semibold text-text-main">
                                        Hasil Pemrosesan Admin
                                    </h3>
                                    <x-ui.badge :variant="$badgeVariant">{{ $statusLabel }}</x-ui.badge>
                                </div>

                                <div class="space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-text-muted">Diproses</span>
                                        <span class="text-text-main font-semibold">
                                            {{ $processedAt ? $processedAt->translatedFormat('d F Y, H:i') : '-' }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-text-muted">Durasi Disetujui</span>
                                        <span class="text-text-main font-semibold">
                                            {{ $approvedDays }} Hari
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft text-sm text-text-main">
                                    {!! nl2br(e($adminNote !== '' ? $adminNote : 'Tidak ada keterangan dari Admin.')) !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- KANAN: BUKTI --}}
                    <div class="space-y-4">
                        <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                            <h3 class="text-base font-semibold text-text-main mb-3">
                                Bukti Pendukung
                            </h3>

                            @if ($buktiUrl)
                                @if ($isPdf)
                                    <div class="w-full h-40 rounded-xl overflow-hidden border border-brand-borderSoft bg-brand-surface-50 mb-3">
                                        <iframe src="{{ $buktiUrl }}" class="w-full h-full" loading="lazy"></iframe>
                                    </div>
                                @elseif ($isImage)
                                    <div class="w-full rounded-xl overflow-hidden border border-brand-borderSoft bg-brand-surface-50 mb-3">
                                        <img src="{{ $buktiUrl }}" alt="Bukti" class="w-full h-40 object-cover">
                                    </div>
                                @else
                                    <div class="p-6 rounded-xl border border-dashed border-brand-borderSoft bg-brand-surface-50 text-center">
                                        <p class="text-xs text-text-muted italic">
                                            File bukti tersedia, tetapi format preview tidak didukung.
                                        </p>
                                    </div>
                                @endif

                                <a href="{{ $buktiUrl }}" target="_blank" class="block">
                                    <x-ui.button-primary class="w-full justify-center">
                                        <i data-lucide="external-link" class="w-4 h-4 mr-2"></i>
                                        Buka Bukti di Tab Baru
                                    </x-ui.button-primary>
                                </a>
                            @else
                                <div class="p-6 rounded-xl border border-dashed border-brand-borderSoft bg-brand-surface-50 text-center">
                                    <i data-lucide="image-off" class="w-8 h-8 text-text-muted/40 mx-auto mb-2"></i>
                                    <p class="text-xs text-text-muted italic">
                                        Tidak ada bukti dilampirkan.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layouts.member>

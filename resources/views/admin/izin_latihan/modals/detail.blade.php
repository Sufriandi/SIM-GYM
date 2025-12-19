{{-- resources/views/admin/izin_latihan/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $memberName = $izin->member?->user?->name ?? '[Member dihapus]';
    $memberUsername = $izin->member?->user?->username; // tanpa '@'

    $akhirMembership = $izin->member?->tanggal_akhir
        ? Carbon::parse($izin->member->tanggal_akhir)
        : null;

    $processedAt = $izin->tanggal_persetujuan ? Carbon::parse($izin->tanggal_persetujuan) : null;

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

    $adminNote = trim((string) ($izin->keterangan_admin ?? ''));

    $showAdminBox =
        $izin->status !== 'pending'
        || $adminNote !== ''
        || $izin->tanggal_persetujuan
        || $izin->durasi_izin_disetujui !== null;
@endphp

<div
    x-show="openDetailId === {{ $izin->id }}"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null"
    @keydown.escape.window="openDetailId = null"
>
    <div
        class="relative w-full max-w-5xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Izin Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Member:
                    <span class="font-semibold text-gold-600">
                        {{ $memberName }}
                        {!! $memberUsername ? ' <span class="text-text-muted">(' . e($memberUsername) . ')</span>' : '' !!}
                    </span>
                </p>
            </div>

            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- KIRI: DATA PENGAJUAN & ALASAN --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- DATA PENGAJUAN --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-1">Data Pengajuan Izin</h3>
                        <p class="text-xs text-text-muted mb-4">Detail permintaan izin yang diajukan member.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-text-muted">Durasi Diajukan</p>
                                <p class="text-lg font-semibold text-text-main">
                                    {{ $izin->jumlah_hari }} Hari
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-text-muted">Tanggal Mulai</p>
                                <p class="text-lg text-text-main">
                                    {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d F Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-text-muted">Tanggal Selesai</p>
                                <p class="text-lg text-text-main">
                                    {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-brand-borderSoft/70">
                            <p class="text-xs text-text-muted">Akhir Membership</p>
                            <p class="text-lg font-semibold text-gold-700">
                                {{ $akhirMembership ? $akhirMembership->translatedFormat('d F Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- ALASAN MEMBER --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-2">Alasan Pengajuan Member</h3>

                        @php
                            $alasanRaw = $izin->alasan ?? '';
                            $alasanTrimmed = trim($alasanRaw);
                            $alasanHtml = $alasanTrimmed !== '' ? nl2br(e($alasanTrimmed)) : '-';
                        @endphp

                        <div class="mt-1 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft text-sm text-text-main min-h-[80px]">
                            {!! $alasanHtml !!}
                        </div>
                    </div>
                </div>

                {{-- KANAN: BUKTI & KET ADMIN / AKSI --}}
                <div class="space-y-4">
                    {{-- BUKTI ALASAN --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-3">Bukti Alasan</h3>

                        @if ($izin->bukti_alasan)
                            @php
                                $url = Storage::url($izin->bukti_alasan);
                                $isPdf = Str::endsWith(strtolower($izin->bukti_alasan), '.pdf');
                            @endphp

                            @if ($isPdf)
                                <div class="w-full h-40 rounded-xl overflow-hidden border border-brand-borderSoft bg-brand-surface-50 mb-3">
                                    <iframe src="{{ $url }}" class="w-full h-full" loading="lazy"></iframe>
                                </div>
                            @else
                                <div class="w-full rounded-xl overflow-hidden border border-brand-borderSoft bg-brand-surface-50 mb-3">
                                    <img src="{{ $url }}" alt="Bukti Izin" class="w-full h-40 object-cover">
                                </div>
                            @endif

                            <a href="{{ $url }}" target="_blank" class="block">
                                <x-ui.button-primary class="w-full justify-center">
                                    Buka Bukti di Tab Baru
                                </x-ui.button-primary>
                            </a>
                        @else
                            <p class="text-center text-text-muted italic py-6">
                                Tidak ada bukti yang dilampirkan.
                            </p>
                        @endif
                    </div>

                    {{-- KETERANGAN ADMIN (khusus processed / history) --}}
                    @if ($showAdminBox)
                        <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-base font-semibold text-text-main">Keterangan Admin</h3>
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
                                    <span class="text-text-muted">Disetujui</span>
                                    <span class="text-text-main font-semibold">
                                        @if ($izin->status === 'disetujui')
                                            {{ (int) ($izin->durasi_izin_disetujui ?? 0) }} Hari
                                        @else
                                            0 Hari
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft text-sm text-text-main">
                                {!! nl2br(e($adminNote !== '' ? $adminNote : 'Tidak ada keterangan.')) !!}
                            </div>
                        </div>
                    @endif

                    {{-- AKSI ADMIN (jika status pending) --}}
                    @if ($izin->status === 'pending')
                        <div class="rounded-2xl border border-warning bg-warning-soft/10 px-5 py-4">
                            <h3 class="text-xs font-semibold tracking-wide text-warning uppercase mb-2">
                                Menunggu Aksi Admin
                            </h3>
                            <p class="text-xs text-text-muted mb-4">
                                Pilih tindakan yang akan dilakukan untuk izin ini.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- SETUJUI --}}
                                <x-ui.button-success
                                    type="button"
                                    class="w-full justify-center gap-2"
                                    @click="
                                        openDetailId = null;
                                        openApproveId = {{ $izin->id }};
                                    "
                                >
                                    <i data-lucide="circle-check" class="w-5 h-5"></i>
                                    Setujui
                                </x-ui.button-success>

                                {{-- TOLAK --}}
                                <x-ui.button-primary
                                    type="button"
                                    class="w-full justify-center gap-2"
                                    @click="
                                        openDetailId = null;
                                        openRejectId = {{ $izin->id }};
                                    "
                                >
                                    <i data-lucide="circle-x" class="w-5 h-5"></i>
                                    Tolak
                                </x-ui.button-primary>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FOOTER MOBILE --}}
            <div class="mt-6 flex justify-end lg:hidden">
                <x-ui.button-secondary type="button" @click="openDetailId = null">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>

{{-- resources/views/admin/izin_latihan/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
@endphp

@props(['izin'])

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
                    Lihat informasi lengkap pengajuan izin latihan.
                </p>
            </div>

            <div class="flex items-center gap-3">
                

                <button
                    type="button"
                    class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                    @click="openDetailId = null"
                >
                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                </button>
            </div>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- KIRI: DATA PENGAJUAN & ALASAN --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- DATA PENGAJUAN --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-1">
                            Data Pengajuan Izin
                        </h3>
                        <p class="text-xs text-text-muted mb-4">
                            Detail permintaan izin yang diajukan member.
                        </p>

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

                        @if ($izin->member)
                            <div class="mt-5 pt-4 border-t border-brand-borderSoft/70">
                                <p class="text-xs text-text-muted">Akhir Membership</p>
                                <p class="text-lg font-semibold text-gold-700">
                                    {{ \Carbon\Carbon::parse($izin->member->tanggal_akhir)->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- ALASAN MEMBER --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-2">
                            Alasan Pengajuan Member
                        </h3>

                        @php
                            // Ambil teks asli
                            $alasanRaw = $izin->alasan ?? '';

                            // Trim spasi & newline di awal/akhir
                            $alasanTrimmed = trim($alasanRaw);

                            // Jika setelah trim masih ada isi, convert newline -> <br>, kalau kosong tampilkan '-'
                            $alasanHtml = $alasanTrimmed !== ''
                                ? nl2br(e($alasanTrimmed))
                                : '-';
                        @endphp

                        <div class="mt-1 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft text-sm text-text-main min-h-[80px]">
                            {!! $alasanHtml !!}
                        </div>
                    </div>
                </div>

                {{-- KANAN: BUKTI & AKSI --}}
                <div class="space-y-4">
                    {{-- BUKTI ALASAN --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-3">
                            Bukti Alasan
                        </h3>

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
                                    <img
                                        src="{{ $url }}"
                                        alt="Bukti Izin"
                                        class="w-full h-40 object-cover"
                                    >
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

                    {{-- KOTAK INFO AKSI (HANYA PENDING) --}}
                    @if ($izin->status === 'pending')
                        <div class="rounded-2xl border border-warning bg-warning-soft/10 px-5 py-4">
                            <h3 class="text-xs font-semibold tracking-wide text-warning uppercase mb-2">
                                Menunggu Aksi Admin
                            </h3>
                            <p class="text-xs text-text-muted mb-4">
                                Aksi persetujuan penuh dilakukan melalui formulir persetujuan.
                            </p>
                            <a href="{{ route('admin.izin_latihan.approve.form', $izin) }}">
                                <x-ui.button-primary class="w-full justify-center">
                                    Proses Persetujuan Sekarang
                                </x-ui.button-primary>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FOOTER MOBILE: TOMBOL TUTUP --}}
            <div class="mt-6 flex justify-end lg:hidden">
                <x-ui.button-secondary type="button" @click="openDetailId = null">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>

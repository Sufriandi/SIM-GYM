{{-- resources/views/admin/izin_latihan/modals/reject_form.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;
@endphp

@props(['izin'])

<div
    x-show="openRejectId === {{ $izin->id }}"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openRejectId = null"
    @keydown.escape.window="openRejectId = null"
>
    <div
        class="relative w-full max-w-5xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Tolak Izin Member
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Member:
                    <span class="font-semibold">
                        {{ $izin->member?->nama ?? '[Member dihapus]' }}
                    </span>
                    &ndash; berikan catatan penolakan yang akan tersimpan di riwayat.
                </p>
            </div>

            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openRejectId = null"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4">
            <form
                method="POST"
                action="{{ route('admin.izin_latihan.reject', $izin->id) }}"
                class="grid grid-cols-1 lg:grid-cols-3 gap-6"
            >
                @csrf

                {{-- KIRI: DATA PENGAJUAN + ALASAN MEMBER + BUKTI --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- DATA PENGAJUAN --}}
                    @php
                        $tanggalMulai   = Carbon::parse($izin->tanggal_mulai);
                        $tanggalSelesai = Carbon::parse($izin->tanggal_selesai);
                        $expiredSaatIni = $izin->member?->tanggal_akhir
                            ? Carbon::parse($izin->member->tanggal_akhir)
                            : null;
                    @endphp

                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-1">
                            Data Pengajuan Izin
                        </h3>
                        <p class="text-xs text-text-muted mb-4">
                            Ringkasan durasi izin dan status membership member saat ini.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {{-- DIAJUKAN --}}
                            <div>
                                <p class="text-[11px] font-semibold text-text-muted uppercase">
                                    Diajukan
                                </p>
                                <p class="mt-1 text-lg font-semibold text-text-main">
                                    {{ $izin->jumlah_hari }} Hari
                                </p>
                            </div>

                            {{-- EXPIRED SAAT INI --}}
                            <div>
                                <p class="text-[11px] font-semibold text-text-muted uppercase">
                                    Expired saat ini
                                </p>
                                <p class="mt-1 text-lg font-semibold text-text-main">
                                    @if ($expiredSaatIni)
                                        {{ $expiredSaatIni->translatedFormat('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>

                            {{-- PERIODE IZIN --}}
                            <div>
                                <p class="text-[11px] font-semibold text-text-muted uppercase">
                                    Periode izin
                                </p>
                                <p class="mt-1 text-sm text-text-main leading-tight">
                                    {{ $tanggalMulai->translatedFormat('d/m/y') }}
                                    &ndash;
                                    {{ $tanggalSelesai->translatedFormat('d/m/y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ALASAN MEMBER --}}
                    @php
                        $alasanRaw = $izin->alasan ?? '';
                        $alasanTrimmed = trim($alasanRaw);
                        $alasanHtml = $alasanTrimmed !== '' ? nl2br(e($alasanTrimmed)) : '-';
                    @endphp

                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-2">
                            Alasan Member
                        </h3>

                        <div class="mt-1 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft text-sm text-text-main min-h-[80px]">
                            {!! $alasanHtml !!}
                        </div>
                    </div>

                    {{-- BUKTI LAMPIRAN --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-base font-semibold text-text-main mb-2">
                            Bukti Lampiran
                        </h3>

                        @if ($izin->bukti_alasan)
                            @php
                                $url = Storage::url($izin->bukti_alasan);
                            @endphp
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                class="text-sm font-medium text-primary-dark hover:underline"
                            >
                                Lihat lampiran
                            </a>
                        @else
                            <p class="text-sm text-text-muted italic">
                                Tidak ada bukti yang dilampirkan.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- KANAN: KONFIRMASI PENOLAKAN --}}
                <div class="space-y-4">
                    <div class="rounded-2xl border border-warning bg-warning-soft/10 px-5 py-4 flex flex-col h-full">
                        <div>
                            <h3 class="text-base font-semibold text-text-main mb-1">
                                Konfirmasi Penolakan
                            </h3>
                            <p class="text-xs text-text-muted mb-4">
                                Tulis alasan penolakan atau catatan khusus. Catatan ini akan muncul di riwayat serta
                                halaman detail izin.
                            </p>

                            <div class="space-y-1.5">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-[11px] font-bold uppercase text-text-muted">
                                        Catatan admin
                                    </span>
                                    <span class="text-[10px] text-text-muted">
                                        (opsional)
                                    </span>
                                </div>

                                <textarea
                                    name="keterangan_admin"
                                    rows="7"
                                    class="w-full rounded-2xl border border-brand-borderSoft bg-brand-shell text-sm text-text-main px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                    placeholder="Tulis alasan penolakan atau catatan khusus..."
                                >{{ old('keterangan_admin') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-2">
                            <x-ui.button-secondary
                                type="button"
                                @click="openRejectId = null"
                            >
                                Batal
                            </x-ui.button-secondary>

                            <x-ui.button-primary
                                type="submit"
                                class="bg-danger hover:bg-danger-dark border-none text-white"
                            >
                                Ya, Tolak Izin
                            </x-ui.button-primary>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

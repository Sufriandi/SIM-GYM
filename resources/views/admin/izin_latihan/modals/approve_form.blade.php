{{-- resources/views/admin/izin_latihan/modals/approve_form.blade.php --}}

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;

    $memberName      = $izin->member->nama ?? '[Member dihapus]';
    $akhirMembership = $izin->member?->tanggal_akhir
        ? Carbon::parse($izin->member->tanggal_akhir)
        : null;

    $periodeMulai    = Carbon::parse($izin->tanggal_mulai);
    $periodeSelesai  = Carbon::parse($izin->tanggal_selesai);

    // Bukti izin
    $hasEvidence = !empty($izin->bukti_alasan);
    $evidenceUrl = $hasEvidence ? Storage::url($izin->bukti_alasan) : null;
    $ext         = $hasEvidence ? strtolower(pathinfo($izin->bukti_alasan, PATHINFO_EXTENSION)) : null;
@endphp

<div
    x-show="openApproveId === {{ $izin->id }}"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @keydown.escape.window="openApproveId = null"
    @click.self="openApproveId = null"
>
    <div
        class="relative w-full max-w-6xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               overflow-hidden flex flex-col"
    >
        {{-- HEADER MODAL (selaras dengan Tambah Izin) --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Persetujuan Izin
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Member:
                    <span class="font-semibold text-gold-600">{{ $memberName }}</span>
                    – Tentukan jumlah hari kompensasi membership.
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openApproveId = null"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL (tanpa overflow scroll; hanya alasan yang bisa scroll) --}}
        <div class="p-6 pb-5">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

                {{-- =========================== --}}
                {{-- KOLOM KIRI: DATA + ALASAN + BUKTI --}}
                {{-- =========================== --}}
                <div class="lg:col-span-7 space-y-5">
                    {{-- CARD DATA SINGKAT --}}
                    <div class="rounded-2xl border border-brand-borderSoft bg-brand-card px-5 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Diajukan --}}
                            <div class="rounded-xl bg-brand-shell px-4 py-3 flex flex-col justify-center">
                                <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase">
                                    Diajukan
                                </p>
                                <p class="mt-1 text-2xl font-bold text-text-main leading-none">
                                    {{ $izin->jumlah_hari }}
                                    <span class="text-xs font-medium text-text-muted ml-1">Hari</span>
                                </p>
                            </div>

                            {{-- Expired --}}
                            <div class="rounded-xl bg-brand-shell px-4 py-3 flex flex-col justify-center">
                                <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase">
                                    Expired Saat Ini
                                </p>
                                <p class="mt-1 text-sm font-semibold text-text-main leading-snug">
                                    {{ $akhirMembership ? $akhirMembership->translatedFormat('d M Y') : '-' }}
                                </p>
                            </div>

                            {{-- Periode izin --}}
                            <div class="rounded-xl bg-brand-shell px-4 py-3 flex flex-col justify-center">
                                <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase">
                                    Periode Izin
                                </p>
                                <p class="mt-1 text-xs font-medium text-text-main leading-snug">
                                    {{ $periodeMulai->format('d/m/y') }} – {{ $periodeSelesai->format('d/m/y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- CARD ALASAN (punya scrollbar sendiri kalau panjang) --}}
                    <div class="rounded-2xl border border-brand-borderSoft bg-brand-card px-5 pt-3 pb-4">
                        <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase mb-1.5">
                            Alasan Member
                        </p>
                        <div
                            class="text-sm text-text-main leading-relaxed text-left whitespace-pre-wrap
                                   max-h-32 overflow-y-auto custom-scrollbar"
                        >
                            {{ trim($izin->alasan ?: '-') }}
                        </div>
                    </div>

                    {{-- CARD BUKTI LAMPIRAN (mini preview) --}}
                    @if ($hasEvidence)
                        <div class="rounded-2xl border border-brand-borderSoft bg-brand-card overflow-hidden">
                            <div class="px-5 py-2.5 border-b border-brand-borderSoft flex items-center justify-between">
                                <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase">
                                    Bukti Lampiran
                                </p>
                                <a
                                    href="{{ $evidenceUrl }}"
                                    target="_blank"
                                    class="text-xs text-gold-600 hover:text-gold-500 font-semibold inline-flex items-center gap-1"
                                >
                                    Buka Asli
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </div>

                            <div class="h-36 flex items-center justify-center bg-brand-shell/60 px-4">
                                @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img
                                        src="{{ $evidenceUrl }}"
                                        alt="Bukti"
                                        class="max-h-full max-w-full object-contain rounded-lg shadow-sm"
                                    >
                                @elseif ($ext === 'pdf')
                                    <div class="text-center text-xs text-text-main">
                                        <p class="font-semibold mb-1">File PDF Terlampir</p>
                                        <p class="text-[11px] text-text-muted">
                                            Gunakan tombol <span class="font-semibold">Buka Asli</span> untuk melihat secara penuh.
                                        </p>
                                    </div>
                                @else
                                    <div class="text-center text-xs text-text-main">
                                        <div
                                            class="mx-auto w-10 h-10 rounded-full bg-brand-shell flex items-center justify-center
                                                   border border-brand-borderSoft mb-2"
                                        >
                                            <span class="text-[11px] font-bold uppercase">{{ $ext }}</span>
                                        </div>
                                        <p>{{ basename($izin->bukti_alasan) }}</p>
                                        <p class="text-[11px] text-text-muted mt-0.5">
                                            Klik <span class="font-semibold">Buka Asli</span> untuk melihat dokumen.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ========================= --}}
                {{-- KOLOM KANAN: FORM APPROVE --}}
                {{-- ========================= --}}
                <div class="lg:col-span-5 flex flex-col">
                    <div class="rounded-2xl border border-brand-borderSoft bg-brand-card px-5 py-4 flex flex-col h-full">
                        {{-- Error validasi --}}
                        @if ($errors->any())
                            <div class="mb-4 rounded-xl border border-danger bg-danger-soft/40 px-3 py-2 text-xs text-danger">
                                <div class="font-semibold mb-1">Mohon periksa input Anda:</div>
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('admin.izin_latihan.approve', $izin) }}"
                            class="flex flex-col flex-1 space-y-5"
                        >
                            @csrf

                            <div class="pb-2 border-b border-brand-borderSoft/70">
                                <h3 class="text-sm font-semibold text-text-main">
                                    Konfirmasi Persetujuan
                                </h3>
                                <p class="text-xs text-text-muted mt-0.5">
                                    Tentukan jumlah hari kompensasi untuk member.
                                </p>
                            </div>

                            {{-- INPUT HARI DISETUJUI --}}
                            <div>
                                <label class="block text-[11px] font-semibold tracking-wide text-text-muted uppercase mb-1.5">
                                    Jumlah Hari Disetujui
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        name="approved_days"
                                        class="w-full rounded-xl border bg-brand-shell text-3xl font-bold text-text-main px-4 py-3
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                               transition-all placeholder:text-text-muted/40"
                                        required
                                        min="0"
                                        max="{{ $izin->jumlah_hari }}"
                                        value="{{ old('approved_days', $izin->jumlah_hari) }}"
                                    >
                                    <span
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-medium text-text-muted"
                                    >
                                        Hari
                                    </span>
                                </div>
                                <p class="text-[11px] text-text-muted mt-2 leading-tight">
                                    Maksimal <span class="font-semibold text-gold-600">{{ $izin->jumlah_hari }} hari</span>.
                                    Jika diisi <span class="font-semibold">0</span>, izin tetap disetujui tapi membership tidak diperpanjang.
                                </p>
                            </div>

                            {{-- CATATAN ADMIN --}}
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold tracking-wide text-text-muted uppercase mb-1.5">
                                    Catatan Admin
                                    <span class="text-[10px] font-normal normal-case opacity-70">(opsional)</span>
                                </label>
                                <textarea
                                    name="keterangan_admin"
                                    rows="4"
                                    class="w-full h-full min-h-[100px] rounded-xl border bg-brand-shell text-sm text-text-main px-4 py-3
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                           resize-none custom-scrollbar placeholder:text-text-muted/50"
                                    placeholder="Tulis alasan persetujuan atau catatan khusus..."
                                >{{ old('keterangan_admin') }}</textarea>
                                <p class="text-[11px] text-text-muted mt-1">
                                    Catatan ini akan muncul di riwayat serta halaman detail izin.
                                </p>
                            </div>

                            {{-- FOOTER BUTTONS – rata kanan, gaya sama dengan modal create --}}
                            <div class="pt-4 mt-2 border-t border-brand-borderSoft/60 flex items-center justify-end gap-2">
                                <x-ui.button-secondary
                                    type="button"
                                    @click="openApproveId = null"
                                >
                                    Batal
                                </x-ui.button-secondary>

                                <x-ui.button-primary type="submit">
                                    Setujui Izin
                                </x-ui.button-primary>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

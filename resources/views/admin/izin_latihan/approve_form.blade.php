{{-- resources/views/admin/izin_latihan/approve_form.blade.php --}}

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    :page-subtitle="'Tentukan hari perpanjangan membership untuk ' . ($izin->member?->nama ?? '[Dihapus]')"
>
    {{-- LINK KEMBALI --}}
    <div class="mb-6">
        <a href="{{ route('admin.izin_latihan.index') }}"
           class="inline-flex items-center text-gold-700 hover:text-gold-500 text-sm font-semibold transition-colors">
            ← Kembali ke Daftar Pending
        </a>
    </div>

    {{-- CARD UTAMA --}}
    <x-ui.card
        class="max-w-5xl mx-auto border-accent/40 shadow-card-strong bg-brand-cardSoft"
        title="Konfirmasi Persetujuan Izin"
        subtitle="Tentukan jumlah hari perpanjangan membership yang akan diberikan."
    >
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- KOLOM KIRI: KONTEKS KEPUTUSAN --}}
            <div class="space-y-4 p-4 rounded-xl bg-brand-surface-50 border border-brand-borderSoft">
                <h4 class="text-sm font-semibold text-text-main uppercase tracking-wide border-b border-brand-borderSoft pb-2">
                    Konteks Keputusan
                </h4>

                {{-- Hari diajukan --}}
                <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                    <p class="text-xs font-medium text-text-muted">Hari diajukan</p>
                    <p class="text-xl font-semibold text-gold-700">
                        {{ $izin->jumlah_hari }} Hari
                    </p>
                </div>

                {{-- Akhir membership saat ini --}}
                <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                    <p class="text-xs font-medium text-text-muted">Akhir membership saat ini</p>
                    <p class="text-sm font-semibold text-gold-700">
                        @if($izin->member)
                            {{ \Carbon\Carbon::parse($izin->member->tanggal_akhir)->translatedFormat('d F Y') }}
                        @else
                            <span class="text-danger">[Data Member Error]</span>
                        @endif
                    </p>
                </div>

                {{-- Periode izin --}}
                <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                    <p class="text-xs font-medium text-text-muted">Periode izin</p>
                    <p class="text-xs md:text-sm text-text-main text-right">
                        {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M') }}
                        s/d
                        {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d F Y') }}
                    </p>
                </div>

                {{-- Link ke detail lengkap --}}
                <p class="text-center pt-2">
                    <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                       class="text-accent-500 hover:text-accent-600 text-xs font-semibold hover:underline">
                        Lihat alasan & bukti lengkap →
                    </a>
                </p>

                {{-- Ringkasan alasan member (scroll kecil) --}}
                <div class="p-3 rounded-xl bg-brand-card border border-brand-borderSoft">
                    <div class="text-[11px] font-semibold text-text-muted uppercase mb-1">
                        Alasan pengajuan
                    </div>
                    <div class="text-sm text-text-main whitespace-pre-wrap max-h-28 overflow-y-auto">
                        {{ $izin->alasan }}
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FORM PERSETUJUAN --}}
            <div class="space-y-5">
                {{-- ERROR VALIDASI (LIST) --}}
                @if ($errors->any())
                    <div class="rounded-xl border border-danger/40 bg-danger-soft/40 px-4 py-3 text-xs text-danger">
                        <div class="font-semibold mb-1">Mohon periksa input Anda:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-xl border border-danger/40 bg-danger-soft/40 px-4 py-3 text-xs text-danger font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('admin.izin_latihan.approve', $izin) }}"
                      class="space-y-5">
                    @csrf

                    <h4 class="text-sm font-semibold text-text-main uppercase tracking-wide border-b border-brand-borderSoft pb-2">
                        Proses Persetujuan
                    </h4>

                    {{-- INPUT HARI DISETUJUI --}}
                    <div>
                        <label for="approved_days"
                               class="block text-xs font-semibold text-text-main mb-1">
                            Jumlah hari perpanjangan disetujui
                        </label>
                        <input
                            type="number"
                            name="approved_days"
                            id="approved_days"
                            class="w-full rounded-xl border bg-brand-card text-2xl font-semibold text-text-main px-4 py-3
                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   @error('approved_days') border-danger focus:ring-danger @enderror"
                            required
                            min="0"
                            max="{{ $izin->jumlah_hari }}"
                            value="{{ old('approved_days', $izin->jumlah_hari) }}"
                        >
                        <p class="text-[11px] text-text-muted mt-1">
                            Masukkan hari yang disetujui (0 – {{ $izin->jumlah_hari }} hari).
                            Nilai 0 artinya izin disetujui tanpa perpanjangan membership.
                        </p>
                        @error('approved_days')
                            <p class="text-[11px] text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CATATAN ADMIN --}}
                    <div>
                        <label for="keterangan_admin"
                               class="block text-xs font-semibold text-text-main mb-1">
                            Catatan Admin (opsional)
                        </label>
                        <textarea
                            name="keterangan_admin"
                            id="keterangan_admin"
                            rows="4"
                            class="w-full rounded-xl border bg-brand-card text-sm text-text-main px-3 py-2
                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        >{{ old('keterangan_admin') }}</textarea>
                        <p class="text-[11px] text-text-muted mt-1">
                            Catatan ini akan tersimpan sebagai riwayat dan dapat dilihat di detail izin.
                        </p>
                    </div>

                    {{-- TOMBOL SUBMIT --}}
                    <x-ui.button-primary type="submit" class="w-full justify-center text-xs md:text-sm py-3">
                        Proses Persetujuan & Perpanjang Membership
                    </x-ui.button-primary>
                </form>

                {{-- TOMBOL KEMBALI --}}
                <a href="{{ route('admin.izin_latihan.index') }}">
                    <x-ui.button-secondary class="w-full justify-center text-xs md:text-sm py-3">
                        Kembali ke Daftar Pending
                    </x-ui.button-secondary>
                </a>
            </div>
        </div>
    </x-ui.card>
</x-layouts.admin>

{{-- resources/views/admin/izin_latihan/detail.blade.php --}}

<x-layouts.admin
    pageTitle="Detail Izin Member"
    pageSubtitle="Lihat detail lengkap pengajuan izin member."
>

    {{-- Title + Back --}}
    <div class="mb-8 space-y-3">

        {{-- Judul via Component --}}
        <x-ui.section-header
            title="Detail Izin Member"
            subtitle="Lihat detail pengajuan izin yang diajukan member."
        />
        {{-- Tombol Kembali --}}
        <div class="mb-6">
        <a
            href="{{ $izin->status == 'pending' ? route('admin.izin_latihan.index') : route('admin.izin_latihan.history') }}"
            class="inline-flex items-center text-gold-700 hover:text-gold-500 text-sm font-semibold transition-colors"
        >
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>

            Kembali ke {{ $izin->status == 'pending' ? 'Permintaan Pending' : 'Riwayat Persetujuan' }}
        </a>
    </div>
    <hr class="border-t border-brand-borderSoft mb-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ============================== --}}
        {{-- KOLOM KIRI — DETAIL PENGAJUAN   --}}
        {{-- ============================== --}}
        <x-ui.card class="lg:col-span-2"
                   title="Data Pengajuan Izin"
                   subtitle="Detail permintaan izin latihan yang diajukan member.">

            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">

                {{-- STATUS --}}
                <div>
                    <x-ui.label>Status Izin</x-ui.label>
                    @if($izin->status === 'pending')
                        <x-ui.badge variant="warning">Pending</x-ui.badge>
                    @elseif($izin->status === 'disetujui')
                        <x-ui.badge variant="success">Disetujui</x-ui.badge>
                    @else
                        <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                    @endif
                </div>

                {{-- Durasi Diajukan --}}
                <div>
                    <x-ui.label>Durasi Diajukan</x-ui.label>
                    <p class="text-lg font-semibold text-text-main">
                        {{ $izin->jumlah_hari }} Hari
                    </p>
                </div>

                {{-- Durasi Disetujui --}}
                @if ($izin->status !== 'pending')
                    <div>
                        <x-ui.label>Durasi Disetujui</x-ui.label>
                        <p class="text-lg font-bold {{ $izin->status === 'disetujui' ? 'text-success' : 'text-danger' }}">
                            {{ $izin->durasi_izin_disetujui ?? 0 }} Hari
                        </p>
                    </div>
                @endif

                {{-- Tanggal Mulai --}}
                <div>
                    <x-ui.label>Tanggal Mulai</x-ui.label>
                    <p class="text-lg text-text-main">
                        {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d F Y') }}
                    </p>
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <x-ui.label>Tanggal Selesai</x-ui.label>
                    <p class="text-lg text-text-main">
                        {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d F Y') }}
                    </p>
                </div>

                {{-- Tanggal Diproses --}}
                @if ($izin->tanggal_persetujuan)
                    <div>
                        <x-ui.label>Tanggal Diproses</x-ui.label>
                        <p class="text-lg text-text-main">
                            {{ \Carbon\Carbon::parse($izin->tanggal_persetujuan)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                @endif
            </div>

            <x-ui.divider class="my-8" />

            {{-- ============================= --}}
            {{-- DATA MEMBERSHIP — KALAU ADA   --}}
            {{-- ============================= --}}
            @if ($izin->member)
                <x-ui.section-subtitle class="mb-4">Data Membership</x-ui.section-subtitle>

                <x-ui.label>Akhir Membership Member</x-ui.label>
                <p class="text-xl font-bold text-gold-700">
                    {{ \Carbon\Carbon::parse($izin->member->tanggal_akhir)->translatedFormat('d F Y') }}
                </p>

                <x-ui.divider class="my-8" />
            @endif

            {{-- ============================= --}}
            {{-- ALASAN MEMBER --}}
            {{-- ============================= --}}
            <div>
                <x-ui.label>Alasan Pengajuan Member</x-ui.label>
                <div class="p-4 bg-brand-surface-50 rounded-xl border border-brand-borderSoft text-text-main whitespace-pre-wrap">
                    {{ $izin->alasan }}
                </div>
            </div>

            {{-- ============================= --}}
            {{-- CATATAN ADMIN --}}
            {{-- ============================= --}}
            @if ($izin->keterangan_admin)
                <div class="mt-6">
                    <x-ui.label>Catatan Admin</x-ui.label>
                    <div class="p-4 rounded-xl border whitespace-pre-wrap
                                {{ $izin->status === 'disetujui'
                                    ? 'bg-success-soft text-success border-success/40'
                                    : 'bg-danger-soft text-danger border-danger/40' }}">
                        {{ $izin->keterangan_admin }}
                    </div>
                </div>
            @endif

        </x-ui.card>

        {{-- ============================== --}}
        {{-- KOLOM KANAN — BUKTI ALASAN     --}}
        {{-- ============================== --}}
        <div class="space-y-6">

            {{-- BUKTI GAMBAR --}}
            <x-ui.card title="Bukti Alasan">
                @if ($izin->bukti_alasan)
                    <a href="{{ Storage::url($izin->bukti_alasan) }}" target="_blank">
                        <img src="{{ Storage::url($izin->bukti_alasan) }}"
                             alt="Bukti Izin"
                             class="w-full rounded-xl border border-brand-borderSoft hover:border-gold-600 transition-all duration-200 shadow-sm">
                    </a>

                    <a href="{{ Storage::url($izin->bukti_alasan) }}"
                       target="_blank"
                       class="mt-4 block">
                        <x-ui.button-primary class="w-full justify-center">
                            Lihat Ukuran Penuh
                        </x-ui.button-primary>
                    </a>
                @else
                    <p class="text-center text-text-muted italic py-6">
                        Tidak ada bukti yang dilampirkan.
                    </p>
                @endif
            </x-ui.card>

            {{-- JIKA MASIH PENDING --}}
            @if ($izin->status === 'pending')
                <x-ui.card class="border-warning bg-warning-soft/20">
                    <x-ui.section-subtitle class="text-warning mb-2">Menunggu Aksi</x-ui.section-subtitle>

                    <p class="text-sm text-text-muted mb-4">
                        Aksi persetujuan dilakukan di halaman <b>Permintaan Izin Baru</b>.
                    </p>

                    <a href="{{ route('admin.izin_latihan.approve.form', $izin) }}">
                        <x-ui.button-primary class="w-full justify-center">
                            Proses Persetujuan Sekarang
                        </x-ui.button-primary>
                    </a>
                </x-ui.card>
            @endif

        </div>
    </div>
</x-layouts.admin>

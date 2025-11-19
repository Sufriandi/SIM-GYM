{{-- resources/views/admin/izin_latihan/history.blade.php --}}

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Daftar izin latihan yang sudah diproses oleh Admin."
>
    {{-- JUDUL + SUBTITLE --}}
    <x-ui.section-header
        :title="$pageTitle"
        subtitle="Daftar izin latihan yang sudah diproses oleh Admin."
    />

    {{-- TOMBOL KEMBALI DI BAWAH SUBTITLE --}}
    <div class="mt-2">
        <x-ui.back-button
            href="{{ route('admin.izin_latihan.index') }}"
            text="Kembali ke Permintaan Pending"
        />
    </div>

    {{-- GARIS PEMBATAS DI BAWAH TOMBOL --}}
    <hr class="border-t border-brand-borderSoft mb-6 mt-2">

    {{-- CARD UTAMA  --}}
    <div class="max-w-6xl mx-auto">
        <x-ui.card>
            {{-- Header card --}}
            <div class="mb-5">
                <h2 class="text-2xl font-heading text-text-main mb-1">
                    Riwayat Persetujuan &amp; Penolakan
                </h2>
                <p class="text-sm text-text-muted">
                    Semua izin latihan yang sudah diproses oleh Admin.
                </p>
            </div>

            {{-- WRAPPER TABEL --}}
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-sm md:text-base">
                    <thead>
                        <tr class="border-b-2 border-brand-borderSoft/80 bg-brand-shell/60">
                            <th class="px-4 py-3 text-left  text-sm text-text-main uppercase">
                                Member
                            </th>
                            <th class="px-4 py-3 text-center  text-sm text-text-main uppercase">
                                Diajukan (H)
                            </th>
                            <th class="px-4 py-3 text-center  text-sm text-text-main uppercase">
                                Disetujui (H)
                            </th>
                            <th class="px-4 py-3 text-center  text-sm text-text-main uppercase">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left  text-sm text-text-main uppercase">
                                Ket. Admin
                            </th>
                            <th class="px-4 py-3 text-center  text-sm text-text-main uppercase">
                                Detail
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($riwayat_izin as $izin)
                            <tr class="border-b border-brand-borderSoft/60 hover:bg-brand-shell/40 transition-colors">
                                {{-- Member: HANYA nama --}}
                                <td class="px-4 py-3 text-text-main">
                                    {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                                </td>

                                {{-- Diajukan (H) --}}
                                <td class="px-4 py-3 text-center text-text-main">
                                    {{ $izin->jumlah_hari }} Hari
                                </td>

                                {{-- Disetujui (H) --}}
                                <td class="px-4 py-3 text-center font-semibold">
                                    @if($izin->status === 'disetujui')
                                        <span class="text-success">
                                            {{ $izin->durasi_izin_disetujui ?? 0 }}
                                        </span>
                                    @else
                                        <span class="text-danger">0</span>
                                    @endif
                                    <span class="text-text-muted font-normal">Hari</span>
                                </td>

                                {{-- Status badge SELALU terisi --}}
                                <td class="px-4 py-3 text-center">
                                    @if($izin->status === 'disetujui')
                                        <x-ui.badge variant="success">DISETUJUI</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">DITOLAK</x-ui.badge>
                                    @endif
                                </td>

                                {{-- Ket. Admin --}}
                                <td class="px-4 py-3 text-sm text-text-muted align-top">
                                    {{ $izin->keterangan_admin ?? 'Tidak ada keterangan.' }}
                                </td>

                                {{-- Detail link --}}
                                <td class="px-4 py-3 text-center">
                                    <a
                                        href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                                        class="inline-flex items-center gap-1 text-gold-600 hover:text-gold-500 font-semibold transition-colors"
                                    >
                                        Lihat Detail
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-text-muted italic">
                                    Belum ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $riwayat_izin->links() }}
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>

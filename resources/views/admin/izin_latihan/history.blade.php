{{-- resources/views/admin/izin_latihan/history.blade.php --}}

<x-layouts.admin
    pageTitle="Riwayat Persetujuan Izin"
    pageSubtitle="Daftar izin yang telah disetujui atau ditolak."
>

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <x-ui.section-header
                title="Riwayat Persetujuan Izin"
                subtitle="Daftar izin yang telah disetujui atau ditolak."
            />
        {{-- Back Button  --}}
    <div class="mb-6">
        <x-ui.back-button
            href="{{ route('admin.izin_latihan.index') }}"
            text="Kembali ke Permintaan Pending"
        />
    </div>

    <hr class="border-t border-brand-borderSoft mb-8">

    {{-- CARD LIST --}}
    <div class="bg-brand-card rounded-3xl p-6 border border-brand-borderSoft shadow-card">

        <h2 class="text-xl font-heading text-text-main mb-1">
            Riwayat Persetujuan &amp; Penolakan
        </h2>

        <p class="text-sm text-text-muted mb-5">
            Semua izin latihan yang sudah diproses oleh Admin.
        </p>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b-2 border-brand-borderSoft/80 bg-brand-shell/60">
                        <th class="table-header">Member</th>
                        <th class="table-header text-center">Diajukan (H)</th>
                        <th class="table-header text-center">Disetujui (H)</th>
                        <th class="table-header text-center">Status</th>
                        <th class="table-header">Ket. Admin</th>
                        <th class="table-header text-center">Detail</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($riwayat_izin as $izin)
                        <tr class="border-b border-brand-borderSoft/60 hover:bg-brand-shell/40 transition-colors">

                            {{-- Member --}}
                            <td class="px-3 py-3 text-sm {{ $izin->member ? 'text-text-main' : 'text-danger italic' }}">
                                {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                                <div class="text-[11px] text-text-muted">
                                    ID Member: {{ $izin->member?->id ?? '-' }}
                                </div>
                            </td>

                            {{-- Ajukan --}}
                            <td class="px-3 py-3 text-center text-sm text-text-muted">
                                {{ $izin->jumlah_hari }} Hari
                            </td>

                            {{-- Disetujui --}}
                            <td class="px-3 py-3 text-center text-sm font-semibold">
                                @if($izin->status === 'disetujui')
                                    <span class="text-success">
                                        {{ $izin->durasi_izin_disetujui }}
                                    </span>
                                @else
                                    <span class="text-danger">0</span>
                                @endif
                                Hari
                            </td>

                            {{-- Status --}}
                            <td class="px-3 py-3 text-center">
                                @if($izin->status === 'disetujui')
                                    <x-ui.badge color="success" text="Disetujui" />
                                @else
                                    <x-ui.badge color="danger" text="Ditolak" />
                                @endif
                            </td>

                            {{-- Ket. Admin --}}
                            <td class="px-3 py-3 text-sm text-text-muted">
                                {{ $izin->keterangan_admin ?? 'Tidak ada keterangan.' }}
                            </td>

                            {{-- Detail --}}
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                                   class="text-gold-500 hover:text-gold-400 font-semibold inline-flex items-center gap-1 transition">
                                    Lihat Detail
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-text-muted italic">
                                Belum ada data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="mt-6">
            {{ $riwayat_izin->links() }}
        </div>
    </div>

</x-layouts.admin>

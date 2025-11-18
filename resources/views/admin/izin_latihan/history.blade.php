{{-- resources/views/admin/izin_latihan/history.blade.php --}}

<x-layouts.admin
    :title="($pageTitle ?? 'Riwayat Pengajuan Izin Lengkap') . ' – BETA GYM'"
    :page-title="$pageTitle ?? 'Riwayat Pengajuan Izin Lengkap'"
    page-subtitle="Daftar izin yang telah disetujui atau ditolak."
>
    {{-- HEADER UTAMA --}}
    <x-ui.section-header
        :title="$pageTitle ?? 'Riwayat Pengajuan Izin Lengkap'"
        subtitle="Daftar izin yang telah disetujui atau ditolak."
    >
        <a href="{{ route('admin.izin_latihan.index') }}">
            <x-ui.button-secondary>
                ← Kembali ke Permintaan Pending
            </x-ui.button-secondary>
        </a>
    </x-ui.section-header>

    {{-- CARD: RIWAYAT IZIN --}}
    <x-ui.card
        title="Riwayat Persetujuan & Penolakan"
        subtitle="Semua izin latihan yang sudah diproses oleh Admin."
        class="border-brand-borderSoft"
    >
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse min-w-[900px] text-sm">
                <thead>
                    <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                        <th class="px-2 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Member
                        </th>
                        <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Diajukan (H)
                        </th>
                        <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Disetujui (H)
                        </th>
                        <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Status
                        </th>
                        <th class="px-2 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Ket. Admin
                        </th>
                        <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Detail
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-brand-borderSoft/80">
                    @forelse ($riwayat_izin as $izin)
                        <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                            {{-- Member --}}
                            <td class="px-2 py-3 text-left align-top">
                                <div class="text-sm {{ $izin->member ? 'text-text-main' : 'text-danger italic' }}">
                                    {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                                </div>
                                @if ($izin->member)
                                    <div class="text-[11px] text-text-muted">
                                        ID Member: {{ $izin->member->kode_member ?? '-' }}
                                    </div>
                                @endif
                            </td>

                            {{-- Diajukan (H) --}}
                            <td class="px-2 py-3 text-center align-top">
                                <span class="text-sm text-text-muted">
                                    {{ $izin->jumlah_hari }} Hari
                                </span>
                            </td>

                            {{-- Disetujui (H) --}}
                            <td class="px-2 py-3 text-center align-top">
                                @if($izin->status === 'disetujui')
                                    <span class="text-sm font-semibold text-success">
                                        {{ $izin->durasi_izin_disetujui ?? 0 }} Hari
                                    </span>
                                @else
                                    <span class="text-sm font-semibold text-danger">
                                        0 Hari
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-2 py-3 text-center align-top">
                                @if($izin->status === 'disetujui')
                                    <x-ui.badge variant="success">DISETUJUI</x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">DITOLAK</x-ui.badge>
                                @endif
                            </td>

                            {{-- Ket. Admin --}}
                            <td class="px-2 py-3 align-top">
                                <span class="text-sm text-text-muted">
                                    @if ($izin->status !== 'pending')
                                        {{ \Illuminate\Support\Str::limit($izin->keterangan_admin ?? 'Tidak ada keterangan.', 50) }}
                                    @else
                                        Menunggu diproses
                                    @endif
                                </span>
                            </td>

                            {{-- Detail --}}
                            <td class="px-2 py-3 text-center align-top">
                                <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                                   class="text-gold-700 hover:text-gold-500 text-xs md:text-sm font-semibold hover:underline transition-colors">
                                    Lihat Detail →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-text-muted italic">
                                Belum ada riwayat persetujuan atau penolakan izin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $riwayat_izin->links() }}
        </div>
    </x-ui.card>

    {{-- CUSTOM SCROLLBAR (konsisten dengan halaman index) --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F5E6D6; /* brand.shell */
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #D4A757; /* gold-500 */
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #A67C39; /* gold-700 */
        }
    </style>
</x-layouts.admin>

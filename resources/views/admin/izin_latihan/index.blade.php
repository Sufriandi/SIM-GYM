{{-- resources/views/admin/izin_latihan/index.blade.php --}}

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Permintaan izin yang belum diproses."
>
    {{-- HEADER UTAMA HALAMAN --}}
    <x-ui.section-header
        :title="$pageTitle"
        subtitle="Permintaan izin yang belum diproses."
    >
        <a href="{{ route('admin.izin_latihan.history') }}">
            <x-ui.button-secondary>
                Riwayat Persetujuan
            </x-ui.button-secondary>
        </a>
    </x-ui.section-header>

    {{-- CARD UTAMA: TABEL PERMINTAAN IZIN PENDING --}}
    <x-ui.card
        title="Daftar Permintaan Izin Pending"
        subtitle="Semua permintaan izin yang masih menunggu tindakan Admin."
        class="border-brand-borderSoft"
    >
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse min-w-[900px] text-sm">
                <thead>
                    <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                        <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Member
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Hari Diajukan
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Tgl Mulai
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Status
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Detail / Bukti
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-brand-borderSoft/80">
                    @forelse ($daftar_izin as $izin)
                        <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                            {{-- Member --}}
                            <td class="p-3 text-left align-top">
                                <div class="text-sm {{ $izin->member ? 'text-text-main' : 'text-danger italic' }}">
                                    {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                                </div>
                                @if ($izin->member)
                                    <div class="text-[11px] text-text-muted">
                                        ID Member: {{ $izin->member->kode_member ?? '-' }}
                                    </div>
                                @endif
                            </td>

                            {{-- Hari diajukan --}}
                            <td class="p-3 text-center align-top">
                                <span class="text-sm font-semibold text-text-main">
                                    {{ $izin->jumlah_hari }} Hari
                                </span>
                            </td>

                            {{-- Tanggal mulai --}}
                            <td class="p-3 text-center align-top">
                                <span class="text-sm text-text-muted">
                                    {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="p-3 text-center align-top">
                                <x-ui.badge variant="warning">
                                    Pending
                                </x-ui.badge>
                            </td>

                            {{-- Detail / Bukti --}}
                            <td class="p-3 text-center align-top">
                                <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                                   class="text-gold-700 hover:text-gold-500 font-semibold text-xs md:text-sm transition-colors hover:underline">
                                    {{ $izin->bukti_alasan ? 'Lihat Bukti' : 'Lihat Detail →' }}
                                </a>
                            </td>

                            {{-- Aksi --}}
                            <td class="p-3 text-center align-top">
                                @if ($izin->member)
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Setujui → ke form approve --}}
                                        <a href="{{ route('admin.izin_latihan.approve.form', $izin) }}">
                                            <x-ui.button-primary class="px-3 py-1.5 text-[11px]">
                                                Setujui
                                            </x-ui.button-primary>
                                        </a>

                                        {{-- Tolak (SweetAlert2 konfirmasi) --}}
                                        <form
                                            id="reject-form-{{ $izin->id }}"
                                            method="POST"
                                            action="{{ route('admin.izin_latihan.reject', $izin->id) }}"
                                            class="inline-block"
                                        >
                                            @csrf
                                            <x-ui.button-secondary
                                                type="button"
                                                class="px-3 py-1.5 text-[11px] bg-danger-soft text-danger hover:bg-danger-soft/80"
                                                onclick="confirmReject({{ $izin->id }}, '{{ $izin->member?->nama ?? 'Member' }}')"
                                            >
                                                Tolak
                                            </x-ui.button-secondary>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-danger italic text-[11px]">
                                        Aksi diblokir (member tidak aktif)
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-text-muted italic">
                                Tidak ada permintaan izin baru yang perlu diproses.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $daftar_izin->links() }}
        </div>
    </x-ui.card>

    {{-- SCRIPT KONFIRMASI SWEETALERT2 UNTUK TOMBOL TOLAK --}}
    <script>
        function confirmReject(izinId, memberName) {
            if (typeof Swal === 'undefined') {
                console.warn('SweetAlert2 (Swal) tidak ditemukan. Pastikan sudah di-load.');
                document.getElementById('reject-form-' + izinId).submit();
                return;
            }

            Swal.fire({
                title: 'Tolak Izin?',
                text: `Anda yakin ingin menolak permintaan izin dari ${memberName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527', // accent-500
                cancelButtonColor: '#6C5A46',  // text-muted warm
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                background: '#21160F',         // brand.nav
                color: '#F8F2E7',              // brand.card
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reject-form-' + izinId).submit();
                }
            });
        }
    </script>

    {{-- CUSTOM SCROLLBAR (DISAMAKAN DENGAN PALET BARU) --}}
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

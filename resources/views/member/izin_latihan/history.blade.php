{{-- resources/views/member/izin_latihan/history.blade.php --}}

<x-layouts.member
    :pageTitle="$pageTitle ?? 'Riwayat Pengajuan Izin Lengkap'"
    pageSubtitle="Semua izin latihan yang pernah Anda ajukan."
>
    <div class="max-w-5xl mx-auto space-y-4 pt-2">

        {{-- FLASH MESSAGE (error saja, success lewat toast global) --}}
        @if(session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- JUDUL + SUBTITLE --}}
        <x-ui.section-header
            :title="$pageTitle ?? 'Riwayat Pengajuan Izin Lengkap'"
            subtitle="Berisi izin dengan status Pending, Disetujui, maupun Ditolak."
        />

        {{-- TOMBOL KEMBALI DI BAWAH JUDUL --}}
        <div>
            <x-ui.back-button
                href="{{ route('member.izin_latihan.index') }}"
                text="Kembali ke Izin Pending"
            />
        </div>

        {{-- PEMBATAS DI BAWAH TOMBOL KEMBALI --}}
        <hr class="border-t border-brand-borderSoft mb-4">

        {{-- BARIS TOMBOL AJUKAN (KANAN) --}}
        <div class="flex justify-start md:justify-end mb-2">
            <a href="{{ route('member.izin_latihan.create') }}">
                <x-ui.button-primary class="inline-flex items-center">
                    Ajukan Izin Baru
                    <i data-lucide="calendar-plus" class="w-4 h-4 ml-2"></i>
                </x-ui.button-primary>
            </a>
        </div>

        {{-- CARD TABEL RIWAYAT --}}
        <x-ui.card
            title="Riwayat Lengkap Izin Latihan"
            subtitle="Pantau seluruh pengajuan izin yang pernah Anda lakukan."
            class="overflow-hidden"
        >
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-shell/60">
                            <th class="px-3 py-2 text-left  text-text-muted font-medium">Periode Izin</th>
                            <th class="px-3 py-2 text-center text-text-muted font-medium">Durasi</th>
                            <th class="px-3 py-2 text-center text-text-muted font-medium">Status</th>
                            <th class="px-3 py-2 text-center text-text-muted font-medium">Diproses</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($daftar_izin as $izin)
                            @php
                                $mulai    = \Carbon\Carbon::parse($izin->tanggal_mulai);
                                $selesai  = \Carbon\Carbon::parse($izin->tanggal_selesai);
                                $diproses = $izin->tanggal_persetujuan ? \Carbon\Carbon::parse($izin->tanggal_persetujuan) : null;
                            @endphp

                            <tr class="border-b border-brand-borderSoft/60 hover:bg-brand-shell/40 transition-colors">
                                {{-- Periode --}}
                                <td class="px-3 py-2 align-top">
                                    <div class="text-text-main">
                                        {{ $mulai->translatedFormat('d M Y') }} &mdash; {{ $selesai->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-text-muted">
                                        Diajukan: {{ $izin->created_at?->translatedFormat('d M Y, H:i') }}
                                    </div>
                                </td>

                                {{-- Durasi --}}
                                <td class="px-3 py-2 text-center align-top text-text-main font-semibold">
                                    {{ $izin->jumlah_hari }} hari
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-2 text-center align-top">
                                    @if($izin->status === 'pending')
                                        <x-ui.badge variant="warning">Pending</x-ui.badge>
                                    @elseif($izin->status === 'disetujui')
                                        <x-ui.badge variant="success">Disetujui</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                    @endif
                                </td>

                                {{-- Diproses --}}
                                <td class="px-3 py-2 text-center align-top text-text-muted">
                                    @if($diproses)
                                        {{ $diproses->translatedFormat('d M Y, H:i') }}
                                    @else
                                        <span class="text-[11px] italic">Belum diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-text-muted text-sm italic">
                                    Belum ada riwayat izin latihan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($daftar_izin, 'links'))
                <div class="mt-4">
                    {{ $daftar_izin->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.member>

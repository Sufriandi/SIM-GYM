{{-- resources/views/member/izin_latihan/index.blade.php --}}

<x-layouts.member
    :pageTitle="$pageTitle ?? 'Izin Sedang Diajukan'"
    pageSubtitle="Daftar izin latihan yang statusnya masih pending."
>
    <div class="max-w-5xl mx-auto space-y-4">

        {{-- JUDUL + SUBTITLE --}}
        <x-ui.section-header
            :title="$pageTitle ?? 'Izin Sedang Diajukan'"
            subtitle="Daftar izin latihan yang statusnya masih pending."
        />

        {{-- PEMBATAS DI BAWAH SUBTITLE --}}
        <hr class="border-t border-brand-borderSoft mb-4">

        {{-- BARIS TOMBOL AKSI --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-2">
            <div class="text-sm text-text-muted">
            </div>

            <div class="flex flex-wrap items-center gap-3 md:justify-end">
                {{-- Tombol ke Riwayat --}}
                <a href="{{ route('member.izin_latihan.history') }}">
                    <x-ui.button-secondary class="inline-flex items-center">
                        <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                        Riwayat Lengkap
                    </x-ui.button-secondary>
                </a>

                {{-- Tombol Ajukan Baru --}}
                <a href="{{ route('member.izin_latihan.create') }}">
                    <x-ui.button-primary class="inline-flex items-center">
                        Ajukan Izin Baru
                        <i data-lucide="calendar-plus" class="w-4 h-4 ml-2"></i>
                    </x-ui.button-primary>
                </a>
            </div>
        </div>

        {{-- CARD IZIN PENDING --}}
        <x-ui.card
            title="Izin Pending"
            subtitle="Pengajuan izin yang sedang menunggu persetujuan Admin."
            class="overflow-hidden"
        >
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-shell/60">
                            <th class="px-3 py-2 text-left  text-text-muted font-medium">Periode Izin</th>
                            <th class="px-3 py-2 text-center text-text-muted font-medium">Durasi</th>
                            <th class="px-3 py-2 text-center text-text-muted font-medium">Diajukan</th>
                            <th class="px-3 py-2 text-center text-text-muted font-medium">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($daftar_izin as $izin)
                            @php
                                $mulai   = \Carbon\Carbon::parse($izin->tanggal_mulai);
                                $selesai = \Carbon\Carbon::parse($izin->tanggal_selesai);
                                $buat    = $izin->created_at ? \Carbon\Carbon::parse($izin->created_at) : null;
                            @endphp

                            <tr class="border-b border-brand-borderSoft/60 hover:bg-brand-shell/40 transition-colors">
                                {{-- Periode --}}
                                <td class="px-3 py-2 align-top">
                                    <div class="text-text-main">
                                        {{ $mulai->translatedFormat('d M Y') }} &mdash; {{ $selesai->translatedFormat('d M Y') }}
                                    </div>
                                </td>

                                {{-- Durasi --}}
                                <td class="px-3 py-2 text-center align-top text-text-main font-semibold">
                                    {{ $izin->jumlah_hari }} hari
                                </td>

                                {{-- Diajukan --}}
                                <td class="px-3 py-2 text-center align-top text-text-muted">
                                    {{ $buat?->translatedFormat('d M Y, H:i') ?? '-' }}
                                </td>

                                {{-- Status (selalu pending di index) --}}
                                <td class="px-3 py-2 text-center align-top">
                                    <x-ui.badge variant="warning">Pending</x-ui.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-text-muted text-sm italic">
                                    Belum ada izin latihan yang sedang diajukan.
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

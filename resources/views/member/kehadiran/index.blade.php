{{-- resources/views/member/kehadiran/index.blade.php --}}

@php
    $pageTitle    = 'Kehadiran Latihan';
    $pageSubtitle = 'Pantau kehadiran latihan Anda melalui scan kode QR.';
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-6xl mx-auto space-y-8 pb-10">

        {{-- INFO BOX: Cara absen --}}
        <section
            class="bg-brand-card border border-brand-borderSoft rounded-3xl px-6 md:px-8 py-5 md:py-6 shadow-card-soft flex gap-4 items-start">
            <div
                class="mt-1 flex items-center justify-center w-10 h-10 rounded-2xl bg-gold-500/10 border border-gold-500/40">
                <i data-lucide="qr-code" class="w-5 h-5 text-gold-600"></i>
            </div>
            <div class="space-y-1">
                <h2 class="text-base md:text-lg font-heading font-semibold text-brand-text">
                    Untuk mencatat kehadiran, silakan <span class="font-bold text-gold-700">scan kode QR</span> yang
                    ditampilkan Admin di area gym.
                </h2>
                <p class="text-sm text-brand-textSoft">
                    Setiap scan yang berhasil akan tercatat sebagai kehadiran pada sesi latihan yang sedang aktif.
                </p>
            </div>
        </section>

        {{-- RIWAYAT KEHADIRAN --}}
        <section class="bg-brand-card border border-brand-borderSoft rounded-3xl shadow-card-soft overflow-hidden">
            <div class="px-6 md:px-8 py-5 md:py-6 border-b border-brand-borderSoft/50">
                <h3 class="text-xl font-heading font-semibold text-brand-text">
                    Riwayat Kehadiran
                </h3>
                <p class="text-sm text-brand-textSoft mt-1">
                    Daftar sesi latihan yang pernah Anda hadiri.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-brand-surface-75 text-brand-textSoft uppercase text-[11px] tracking-wider">
                            <th class="px-6 md:px-8 py-3 text-left font-semibold">Sesi Latihan</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal & Jam</th>
                            <th class="px-4 py-3 text-left font-semibold">Waktu Scan</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/60">

                        @if($kehadiran->isEmpty())
                            <tr>
                                <td colspan="5" class="px-6 md:px-8 py-10 text-center text-sm text-brand-textSoft">
                                    Belum ada data kehadiran yang tercatat.
                                </td>
                            </tr>
                        @else
                            @foreach($kehadiran as $row)
                                @php
                                    $periode  = $row->absensiPeriode;
                                    $tanggal  = optional($row->tanggal)->translatedFormat('d M Y');
                                    $jamMasuk = $row->jam_masuk ? \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') : '-';
                                @endphp
                                <tr class="hover:bg-brand-surface-50/60 transition-colors">
                                    {{-- Sesi --}}
                                    <td class="px-6 md:px-8 py-4 align-top">
                                        <div class="font-semibold text-brand-text">
                                            {{ $periode?->tipe_periode ? strtoupper($periode->tipe_periode) : 'SESI LATIHAN' }}
                                        </div>
                                        @if($periode)
                                            <div class="text-[11px] text-brand-textSoft mt-0.5">
                                                {{ \Carbon\Carbon::parse($periode->tanggal_mulai)->translatedFormat('d M Y') }}
                                                –
                                                {{ \Carbon\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Tanggal & Jam --}}
                                    <td class="px-4 py-4 align-top">
                                        <div class="text-brand-text text-sm font-medium">
                                            {{ $tanggal ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-brand-textSoft mt-0.5">
                                            Jam latihan: {{ $jamMasuk }}
                                        </div>
                                    </td>

                                    {{-- Waktu Scan --}}
                                    <td class="px-4 py-4 align-top">
                                        <div class="text-sm text-brand-text">
                                            {{ $tanggal ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-brand-textSoft mt-0.5">
                                            {{ $jamMasuk !== '-' ? $jamMasuk . ' WIB' : '-' }}
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-4 py-4 align-top">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                                   {{ $row->is_valid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                                     : 'bg-red-50 text-red-700 border border-red-200' }}">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full mr-1.5
                                                       {{ $row->is_valid ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $row->is_valid ? 'Valid' : 'Tidak Valid' }}
                                        </span>
                                    </td>

                                    {{-- Detail --}}
                                    <td class="px-4 py-4 align-top">
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-gold-700 hover:text-gold-800 hover:underline">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($kehadiran->hasPages())
                <div class="px-6 md:px-8 py-4 border-t border-brand-borderSoft/50">
                    {{ $kehadiran->links() }}
                </div>
            @endif
        </section>

    </div>
</x-layouts.member>

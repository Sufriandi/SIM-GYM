{{-- resources/views/admin/absensi/kehadiran/index.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Absensi Member';

    $modeLabel = [
        'harian' => 'Harian',
        'mingguan' => 'Mingguan',
        'bulanan' => 'Bulanan',
    ];

    $currentMode = $periodeAktif->tipe_periode ?? 'harian';
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Pantau QR absensi dan kehadiran member berdasarkan periode.">
    <div class="space-y-6 print:space-y-4">

        {{-- BAR ATAS: PILIH MODE PERIODE + TOMBOL CETAK --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 hidden-print">
            {{-- Selector mode periode --}}
            <form action="{{ route('admin.absensi.kehadiran.index') }}" method="GET"
                class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft mr-1">
                    Mode Periode
                </span>

                {{-- Simpan filter lain agar tidak hilang saat ganti mode --}}
                <input type="hidden" name="tanggal" value="{{ request('tanggal') }}">
                <input type="hidden" name="member" value="{{ request('member') }}">

                @foreach ($modeOptions as $key => $label)
                    <button type="submit" name="mode" value="{{ $key }}"
                        class="px-4 py-2 rounded-full text-xs font-bold border transition-all duration-200
                            {{ $currentMode === $key
                                ? 'bg-brand-nav text-gold-500 border-brand-nav shadow-sm'
                                : 'bg-brand-card text-brand-textSoft border-brand-borderSoft hover:border-gold-500 hover:text-brand-text' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>

            {{-- Tombol cetak QR --}}
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                       bg-brand-nav text-gold-500 border border-brand-nav shadow-sm
                       hover:bg-gold-500 hover:text-brand-nav transition-colors hidden-print">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak QR</span>
            </button>
        </div>

        {{-- CARD QR PERIODE AKTIF --}}
        <section
            class="bg-brand-card rounded-3xl border border-brand-borderSoft shadow-card-strong overflow-hidden
                   print:w-full print:shadow-none print:border">
            <div class="flex flex-col md:flex-row items-center justify-between px-8 py-8 gap-8">
                {{-- Info Periode --}}
                <div class="space-y-2 md:space-y-3 md:flex-1">
                    <h2 class="text-lg md:text-xl font-heading font-bold text-brand-text">
                        Periode Absensi Aktif ({{ strtoupper($currentMode) }})
                    </h2>

                    <p class="text-brand-text">
                        {{ Carbon::parse($periodeAktif->tanggal_mulai)->format('d M Y') }}
                        &ndash;
                        {{ Carbon::parse($periodeAktif->tanggal_selesai)->format('d M Y') }}
                    </p>

                    <p class="text-xs text-brand-textSoft">
                        Kode QR:
                        <span class="font-mono break-all">
                            {{ $periodeAktif->kode_qr }}
                        </span>
                    </p>

                    <p class="text-xs text-brand-textSoft max-w-lg">
                        Mode periode bisa diubah melalui tombol di atas.
                        Sistem otomatis membuat periode baru jika belum ada yang aktif
                        untuk hari ini pada mode yang dipilih.
                    </p>
                </div>

                {{-- QR Code --}}
                <div class="flex flex-col items-center gap-3 md:items-end">
                    <span class="text-xs font-medium text-brand-textSoft">
                        Scan untuk absen
                    </span>

                    <div class="bg-white p-3 rounded-2xl border border-brand-borderSoft shadow-md print:border-black">
                        {{-- QR Code --}}
                        {!! QrCode::size(220)->margin(1)->generate($qrUrl) !!}
                    </div>

                    <p class="text-[11px] text-brand-textSoft text-center md:text-right max-w-xs">
                        Gunakan tombol <span class="font-semibold">Cetak QR</span> untuk mencetak
                        kartu ini dan tempel di meja resepsionis.
                    </p>
                </div>
            </div>
        </section>

        {{-- FILTER DAFTAR KEHADIRAN --}}
        <section class="bg-brand-card rounded-3xl border border-brand-borderSoft shadow-card-soft overflow-hidden">
            <div class="px-6 pt-6 pb-4 border-b border-brand-borderSoft/60 hidden-print">
                <h3 class="text-base font-heading font-bold text-brand-text mb-3">
                    Daftar Kehadiran
                </h3>

                <form action="{{ route('admin.absensi.kehadiran.index') }}" method="GET"
                    class="flex flex-col md:flex-row md:items-center gap-3 text-sm">
                    {{-- mode tetap dibawa --}}
                    <input type="hidden" name="mode" value="{{ $currentMode }}">

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                            Tanggal
                        </label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                            class="px-3 py-2 rounded-xl border border-brand-borderSoft bg-brand-bg text-xs">
                    </div>

                    <div class="flex items-center gap-2 flex-1">
                        <label class="text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                            Member
                        </label>
                        <input type="text" name="member" value="{{ request('member') }}"
                            placeholder="Cari nama / username…"
                            class="flex-1 px-3 py-2 rounded-xl border border-brand-borderSoft bg-brand-bg text-xs">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-brand-nav text-gold-500
                                   border border-brand-nav hover:bg-gold-500 hover:text-brand-nav transition-colors">
                            Terapkan
                        </button>

                        <a href="{{ route('admin.absensi.kehadiran.index', ['mode' => $currentMode]) }}"
                            class="px-3 py-2 rounded-xl text-xs font-medium border border-brand-borderSoft text-brand-textSoft hover:bg-brand-surface-100">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABEL KEHADIRAN --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-surface-100 border-b border-brand-borderSoft">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Tanggal
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Jam Masuk
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Member
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Keterangan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/60">
                        @forelse ($kehadiran as $row)
                            <tr class="hover:bg-brand-surface-50">
                                <td class="px-6 py-3 align-top">
                                    {{ Carbon::parse($row->tanggal)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-3 align-top">
                                    {{ $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-3 align-top">
                                    <div class="font-semibold text-brand-text">
                                        {{ $row->member->nama ?? '-' }}
                                    </div>
                                    @if ($row->member && $row->member->username)
                                        <div class="text-xs text-brand-textSoft">
                                            {{ '@' . $row->member->username }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 align-top text-xs text-brand-textSoft">
                                    {{ $row->is_valid ? 'Valid' : 'Perlu ditinjau' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-brand-textSoft">
                                    Belum ada kehadiran tercatat dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($kehadiran->hasPages())
                <div class="px-6 py-4 border-t border-brand-borderSoft bg-brand-card/60 hidden-print">
                    {{ $kehadiran->links() }}
                </div>
            @endif
        </section>
    </div>

    {{-- CSS khusus untuk print --}}
    <style>
        @media print {
            body {
                background: #ffffff !important;
            }

            nav,
            header,
            footer,
            .hidden-print {
                display: none !important;
            }

            .shadow-card-strong,
            .shadow-card-soft {
                box-shadow: none !important;
            }

            .bg-brand-card {
                background: #ffffff !important;
            }
        }
    </style>
</x-layouts.admin>

{{-- resources/views/member/kehadiran/index.blade.php --}}

@php
    use Carbon\Carbon;

    $pageTitle    = 'Kehadiran';
    $pageSubtitle = 'Untuk mencatat kehadiran, silakan scan kode QR yang ditampilkan Admin di area gym.';

    /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $kehadiran */
    $kehadiran = $kehadiran ?? collect();

    $isPaginator = $kehadiran instanceof \Illuminate\Pagination\AbstractPaginator;
    $startNo = $isPaginator ? (($kehadiran->currentPage() - 1) * $kehadiran->perPage()) : 0;
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-6xl mx-auto space-y-4 pb-10">

        {{-- FLASH MESSAGE --}}
        @if(session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        @if(session('success'))
            <x-ui.toast type="success" class="mb-2">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            :subtitle="$pageSubtitle"
        />

        {{-- GARIS EMAS --}}
        <hr class="border-t border-gold-500/30 mb-4">

        {{-- INFO BOX --}}
        <section
            class="bg-brand-card border border-brand-borderSoft rounded-3xl px-6 md:px-8 py-5 md:py-6 shadow-card-soft flex gap-4 items-start"
        >
            <div class="mt-1 flex items-center justify-center w-10 h-10 rounded-2xl bg-gold-500/10 border border-gold-500/40">
                <i data-lucide="qr-code" class="w-5 h-5 text-gold-600"></i>
            </div>
            <div class="space-y-1">
                <h2 class="text-base md:text-lg font-heading font-semibold text-brand-text">
                    Scan QR untuk mencatat kehadiran Anda.
                </h2>
                <p class="text-sm text-brand-textSoft">
                    Setiap scan yang berhasil akan tercatat sebagai kehadiran.
                </p>
            </div>
        </section>

        {{-- CARD RIWAYAT --}}
        <x-ui.card class="overflow-hidden border border-brand-borderSoft">
            <div class="px-6 md:px-8 py-5 md:py-6 border-b border-brand-borderSoft/50">
                <h3 class="text-xl font-heading font-semibold text-brand-text">
                    Riwayat Kehadiran
                </h3>
                <p class="text-sm text-brand-textSoft mt-1">
                    Daftar kehadiran yang pernah Anda catat.
                </p>
            </div>

            {{-- MOBILE: LIST (RAPI DENGAN GRID KOLOM TETAP) --}}
            <div class="md:hidden px-4 py-4">
                @php
                    $isEmpty = $isPaginator
                        ? $kehadiran->isEmpty()
                        : ($kehadiran->count() === 0);
                @endphp

                @if($isEmpty)
                    <div class="px-2 py-10 text-center text-sm text-brand-textSoft">
                        Belum ada data kehadiran yang tercatat.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($kehadiran as $i => $row)
                            @php
                                $no = $startNo + $i + 1;

                                $tglObj  = $row->tanggal ? Carbon::parse($row->tanggal) : null;
                                $hari    = $tglObj ? $tglObj->translatedFormat('l') : '-';
                                $tanggal = $tglObj ? $tglObj->translatedFormat('d M Y') : '-';

                                $jamScan = $row->jam_masuk
                                    ? Carbon::parse($row->jam_masuk)->format('H:i')
                                    : '-';

                                $scanLabel = 'Scan Masuk';
                            @endphp

                            <div
                                class="rounded-2xl border border-brand-borderSoft/70 bg-brand-surface-50/35
                                       px-4 py-4 hover:bg-brand-surface-50/60 transition-colors"
                            >
                                <div class="grid grid-cols-[36px_1fr_auto] items-start gap-3">
                                    {{-- NO (fixed) --}}
                                    <div
                                        class="w-9 h-9 rounded-full bg-brand-shell border border-brand-borderSoft
                                               flex items-center justify-center shrink-0 mt-0.5"
                                        aria-hidden="true"
                                    >
                                        <span class="text-[12px] font-semibold text-text-muted">
                                            {{ $no }}
                                        </span>
                                    </div>

                                    {{-- CONTENT --}}
                                    <div class="min-w-0">
                                        {{-- Baris 1: Hari, Tanggal --}}
                                        <div class="text-sm font-semibold text-brand-text truncate">
                                            {{ $hari }}, {{ $tanggal }}
                                        </div>

                                        {{-- Baris 2: 2 kolom tetap (Jam | Scan Masuk) --}}
                                        <div class="mt-1 grid grid-cols-2 gap-x-4 text-xs text-brand-textSoft">
                                            <div class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                                <i data-lucide="clock" class="w-3.5 h-3.5 text-text-muted"></i>
                                                <span class="font-semibold text-brand-text">
                                                    {{ $jamScan !== '-' ? $jamScan . ' WIB' : '-' }}
                                                </span>
                                            </div>

                                            <div class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                                <i data-lucide="qr-code" class="w-3.5 h-3.5 text-text-muted"></i>
                                                <span>{{ $scanLabel }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- STATUS (auto) --}}
                                    <div class="shrink-0 pt-0.5">
                                        <span
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-[11px] font-semibold whitespace-nowrap
                                                {{ $row->is_valid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                                  : 'bg-red-50 text-red-700 border border-red-200' }}"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $row->is_valid ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $row->is_valid ? 'Valid' : 'Tidak Valid' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- DESKTOP: TABLE --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-sm table-fixed">
                    <thead>
                        <tr class="bg-brand-surface-75 text-brand-textSoft uppercase text-[11px] tracking-wider">
                            <th class="w-16 px-6 md:px-8 py-3 text-left font-semibold whitespace-nowrap">No</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Hari / Tanggal Kehadiran</th>
                            <th class="w-52 px-4 py-3 text-left font-semibold whitespace-nowrap">Waktu Scan</th>
                            <th class="w-40 px-4 py-3 text-center font-semibold whitespace-nowrap">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/60">
                        @if($kehadiran->isEmpty())
                            <tr>
                                <td colspan="4" class="px-6 md:px-8 py-10 text-center text-sm text-brand-textSoft">
                                    Belum ada data kehadiran yang tercatat.
                                </td>
                            </tr>
                        @else
                            @foreach($kehadiran as $i => $row)
                                @php
                                    $no = $startNo + $i + 1;

                                    $tglObj  = $row->tanggal ? Carbon::parse($row->tanggal) : null;
                                    $hari    = $tglObj ? $tglObj->translatedFormat('l') : '-';
                                    $tanggal = $tglObj ? $tglObj->translatedFormat('d M Y') : '-';

                                    $jamScan = $row->jam_masuk
                                        ? Carbon::parse($row->jam_masuk)->format('H:i')
                                        : '-';

                                    $scanLabel = 'Scan Masuk';
                                @endphp

                                <tr class="hover:bg-brand-surface-50/60 transition-colors">
                                    <td class="px-6 md:px-8 py-4 align-middle text-brand-textSoft">
                                        {{ $no }}
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <div class="font-semibold text-brand-text whitespace-nowrap">
                                            {{ $hari }} / {{ $tanggal }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <div class="flex flex-col justify-center">
                                            <div class="text-brand-text font-semibold whitespace-nowrap">
                                                {{ $jamScan !== '-' ? $jamScan . ' WIB' : '-' }}
                                            </div>
                                            <div class="text-[11px] text-brand-textSoft mt-0.5">
                                                {{ $scanLabel }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-middle text-center">
                                        <div class="flex items-center justify-center">
                                            <span
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-[11px] font-semibold whitespace-nowrap
                                                    {{ $row->is_valid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                                      : 'bg-red-50 text-red-700 border border-red-200' }}"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $row->is_valid ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                                {{ $row->is_valid ? 'Valid' : 'Tidak Valid' }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($isPaginator && $kehadiran->hasPages())
                <div class="px-6 md:px-8 py-4 border-t border-brand-borderSoft/50">
                    {{ $kehadiran->links() }}
                </div>
            @endif
        </x-ui.card>

    </div>
</x-layouts.member>

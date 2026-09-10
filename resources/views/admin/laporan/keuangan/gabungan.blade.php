@php
    use Carbon\Carbon;

    $fromDate = $from instanceof \Carbon\CarbonInterface ? $from->format('Y-m-d') : (string) $from;
    $toDate = $to instanceof \Carbon\CarbonInterface ? $to->format('Y-m-d') : (string) $to;
    $sumber = $sumber ?? '';
    $total = $total ?? 0;
@endphp

<x-layouts.admin :title="'Audit Data Keuangan – BETA GYM'" page-title="Laporan Keuangan"
    page-subtitle="Audit data transaksi mentah gabungan dari Produk, Membership, dan Latihan Harian.">

    <div class="space-y-6 font-sans text-text-main">

        {{-- 1. NAVIGATION & FILTER --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">

            {{-- Navigation Tabs --}}
            @include('admin.laporan.keuangan.partials.tabs')

            {{-- Filter & Actions (Right Side) --}}
            <div class="flex flex-wrap items-center gap-2 justify-start lg:justify-end">
                <form method="GET" action="{{ route('admin.laporan.keuangan.gabungan') }}" class="flex flex-wrap items-center gap-2">
                    {{-- Date Range --}}
                    <div class="flex items-center gap-2 bg-white dark:bg-brand-card border border-brand-borderSoft rounded-xl px-3 py-1.5 shadow-xs">
                        <i data-lucide="calendar" class="w-4 h-4 text-gold-500 shrink-0"></i>
                        <input type="date" name="from" value="{{ $fromDate }}"
                            class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-28 cursor-pointer">
                        <span class="text-text-muted text-xs">➜</span>
                        <input type="date" name="to" value="{{ $toDate }}"
                            class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-28 cursor-pointer">
                    </div>

                    {{-- Sumber --}}
                    <div class="relative">
                        <select name="sumber"
                            class="appearance-none bg-white dark:bg-brand-card border border-brand-borderSoft text-xs font-medium text-text-main rounded-xl pl-3 pr-8 py-2 focus:ring-1 focus:ring-gold-500 shadow-xs cursor-pointer">
                            <option value="">Semua Sumber</option>
                            <option value="produk" @selected($sumber === 'produk')>Produk Retail</option>
                            <option value="membership" @selected($sumber === 'membership')>Membership</option>
                            <option value="harian" @selected($sumber === 'harian')>Latihan Harian</option>
                        </select>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-text-muted absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>

                    {{-- Buttons --}}
                    <button type="submit"
                        class="px-3.5 py-2 bg-black dark:bg-gold-500 text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gold-400 text-xs font-bold rounded-xl transition shadow-xs">
                        Terapkan
                    </button>
                    @if (request()->anyFilled(['from', 'to', 'sumber']))
                        <a href="{{ route('admin.laporan.keuangan.gabungan') }}"
                            class="px-3 py-2 border border-brand-borderSoft bg-white dark:bg-brand-card text-text-muted hover:text-text-main text-xs font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-brand-shell/50 transition shadow-xs">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- 2. KPI SUMMARY ROW --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px]">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Total Nilai Audit</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gold-50 text-gold-600 dark:bg-gold-950/50 dark:text-gold-400">
                            <i data-lucide="scale" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-text-main stat-number whitespace-nowrap tracking-tight">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </h3>
                </div>
                <p class="mt-3 text-[10px] text-text-muted">Akumulasi seluruh transaksi sesuai filter aktif.</p>
            </div>

            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px]">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Jumlah Transaksi</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-text-main stat-number whitespace-nowrap tracking-tight">
                        {{ number_format($rows->total(), 0, ',', '.') }} <span class="text-xs font-normal text-text-muted">baris</span>
                    </h3>
                </div>
                <p class="mt-3 text-[10px] text-text-muted">Seluruh entri transaksi terdaftar dalam database.</p>
            </div>

            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px]">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Rentang Waktu</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                            <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-sm font-bold text-gold-600 dark:text-gold-400 flex items-center gap-1.5 pt-1">
                        <span>{{ Carbon::parse($fromDate)->format('d M Y') }} – {{ Carbon::parse($toDate)->format('d M Y') }}</span>
                    </h3>
                </div>
                <p class="mt-3 text-[10px] text-text-muted">Sumber: {{ $sumber ? ucfirst($sumber) : 'Semua Sumber (Produk, Member, Harian)' }}</p>
            </div>
        </div>

        {{-- 3. AUDIT DATA TABLE --}}
        <div class="rounded-2xl border border-brand-borderSoft bg-brand-card shadow-sm overflow-hidden">
            <div class="border-b border-brand-borderSoft bg-brand-shell/30 px-6 py-4 flex flex-wrap justify-between items-center gap-2">
                <div>
                    <h3 class="font-bold text-text-main text-base">Data Transaksi Mentah</h3>
                    <p class="text-xs text-text-muted">Pencatatan riwayat audit finansial read-only lintas modul.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gold-50 text-gold-700 border border-gold-200 dark:bg-gold-950/50 dark:text-gold-400 dark:border-gold-800/80">
                    Read-Only Audit
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-text-main">
                    <thead class="bg-brand-shell/20 uppercase text-[11px] font-bold tracking-wider text-text-muted border-b border-brand-borderSoft">
                        <tr>
                            <th class="px-6 py-3.5">Waktu Transaksi</th>
                            <th class="px-6 py-3.5">Sumber</th>
                            <th class="px-6 py-3.5">Nota / Ref</th>
                            <th class="px-6 py-3.5">Nama / Keterangan</th>
                            <th class="px-6 py-3.5">Metode</th>
                            <th class="px-6 py-3.5 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/60">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-brand-shell/30 transition-colors">
                                <td class="px-6 py-3.5 whitespace-nowrap text-xs font-medium text-text-muted">
                                    {{ Carbon::parse($row->tanggal_transaksi)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    @php
                                        $badgeStyles = match($row->sumber) {
                                            'produk' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            'membership' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                            default => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                        };
                                        $sourceLabel = match($row->sumber) {
                                            'produk' => 'Produk',
                                            'membership' => 'Membership',
                                            default => 'Harian',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $badgeStyles }}">
                                        {{ $sourceLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-mono text-xs text-text-main font-semibold">
                                    {{ $row->no_nota }}
                                </td>
                                <td class="px-6 py-3.5 text-xs text-text-muted">
                                    {{ $row->buyer_name ?? '-' }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap text-xs uppercase font-medium text-text-muted">
                                    {{ $row->metode_pembayaran ?? '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-text-main tabular-nums">
                                    Rp {{ number_format($row->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-text-muted">
                                    Tidak ada transaksi yang ditemukan untuk kriteria filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rows->hasPages())
                <div class="p-4 border-t border-brand-borderSoft bg-brand-shell/20">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

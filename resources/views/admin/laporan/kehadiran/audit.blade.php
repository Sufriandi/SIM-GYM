{{-- resources/views/admin/laporan/kehadiran/audit.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Audit Data Kehadiran';

    $filters = $filters ?? [];
    $start   = $filters['start_date'] ?? now()->subDays(29)->toDateString();
    $end     = $filters['end_date'] ?? now()->toDateString();

    $series = $series ?? ['daily'=>[], 'hourly'=>[], 'weekday'=>[]];

    $daily   = collect($series['daily'] ?? []);
    $hourly  = collect($series['hourly'] ?? []);
    $weekday = collect($series['weekday'] ?? []);

    $qs = http_build_query(request()->query());

    $fmtDate = function ($ymd) {
        try { return Carbon::parse($ymd)->translatedFormat('d M Y'); }
        catch (\Throwable $e) { return (string) $ymd; }
    };
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Audit seri data yang digunakan untuk dashboard (daily/hourly/weekday)."
>
    @once
        <style>
            .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.02); }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.22); }
        </style>
    @endonce

    <div class="space-y-6">

        <div class="flex flex-col lg:flex-row lg:items-start gap-4 justify-between">
            @include('admin.laporan.kehadiran.partials.tabs', ['active' => 'audit', 'qs' => $qs])

            <form method="GET" class="flex-shrink-0">
                <input type="hidden" name="mode" value="custom">
                <div class="flex items-center gap-2 bg-white border border-brand-borderSoft rounded-lg p-1.5 shadow-sm">
                    <div class="flex items-center gap-2 px-2 border-r border-gray-100">
                        <i data-lucide="calendar" class="w-4 h-4 text-gold-500"></i>
                    </div>
                    <input type="date" name="start_date" value="{{ $start }}"
                        class="border-none text-xs font-medium text-text-main focus:ring-0 p-1 bg-transparent w-32 cursor-pointer">
                    <span class="text-text-muted text-xs">➜</span>
                    <input type="date" name="end_date" value="{{ $end }}"
                        class="border-none text-xs font-medium text-text-main focus:ring-0 p-1 bg-transparent w-32 cursor-pointer">
                    <button type="submit"
                        class="ml-2 px-3 py-1.5 bg-black text-white text-xs font-bold rounded hover:bg-gray-800 transition shadow-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <x-ui.card class="lg:col-span-6 p-0 border-brand-borderSoft overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="text-sm font-bold text-text-main">Daily Series</div>
                    <div class="text-xs text-text-muted">Tanggal → total check-in (dan opsional ma7/is_peak).</div>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-text-muted font-bold">
                            <tr>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2 text-right">MA7</th>
                                <th class="px-4 py-2 text-center">Peak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($daily as $r)
                                @php
                                    $dt = $r['date'] ?? $r['tanggal'] ?? null;
                                    $tot = (int)($r['total'] ?? 0);
                                    $ma7 = $r['ma7'] ?? null;
                                    $peak = (bool)($r['is_peak'] ?? false);
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 text-text-main">{{ $dt ? $fmtDate($dt) : '-' }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-text-main">{{ number_format($tot) }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-text-muted">{{ is_null($ma7) ? '-' : number_format((float)$ma7, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-center">{{ $peak ? '✓' : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-text-muted">Tidak ada data daily.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card class="lg:col-span-3 p-0 border-brand-borderSoft overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="text-sm font-bold text-text-main">Hourly Series</div>
                    <div class="text-xs text-text-muted">Jam → total.</div>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-text-muted font-bold">
                            <tr>
                                <th class="px-4 py-2 text-left">Jam</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($hourly as $r)
                                @php
                                    $label = $r['label'] ?? null;
                                    $hour  = $r['hour'] ?? null;
                                    $tot   = (int)($r['total'] ?? 0);
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 text-text-main">{{ $label ?? (is_null($hour) ? '-' : (str_pad((string)$hour, 2, '0', STR_PAD_LEFT).':00')) }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-text-main">{{ number_format($tot) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-4 py-8 text-center text-text-muted">Tidak ada data hourly.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card class="lg:col-span-3 p-0 border-brand-borderSoft overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="text-sm font-bold text-text-main">Weekday Series</div>
                    <div class="text-xs text-text-muted">Hari → total.</div>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-text-muted font-bold">
                            <tr>
                                <th class="px-4 py-2 text-left">Hari</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($weekday as $r)
                                @php
                                    $label = $r['label'] ?? $r['hari'] ?? '-';
                                    $tot   = (int)($r['total'] ?? 0);
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 text-text-main">{{ $label }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-text-main">{{ number_format($tot) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-4 py-8 text-center text-text-muted">Tidak ada data weekday.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

        </div>

    </div>
</x-layouts.admin>

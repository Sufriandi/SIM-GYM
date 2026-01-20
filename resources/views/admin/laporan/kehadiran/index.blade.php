{{-- resources/views/admin/laporan/kehadiran/index.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    $pageTitle = $pageTitle ?? 'Laporan Absensi & Kompensasi';

    // ====== FILTER (kompatibel dengan versi sebelumnya) ======
    $filters = $filters ?? [];
    $start   = $filters['start_date'] ?? now()->subDays(29)->toDateString();
    $end     = $filters['end_date'] ?? now()->toDateString();
    $sort    = $filters['sort'] ?? 'newest';

    // ====== DATA DARI CONTROLLER (tetap sama) ======
    $stats  = $stats ?? [];
    $series = $series ?? ['daily'=>[], 'hourly'=>[], 'weekday'=>[]];

    $totalCheckins = (int)($stats['total_checkins'] ?? 0);
    $uniqueMembers = (int)($stats['unique_members'] ?? 0);
    $avgPerDay     = (float)($stats['avg_per_day'] ?? 0);

    $activeDays    = (int)($stats['active_days'] ?? 0);
    $daysInRange   = (int)($stats['days_in_range'] ?? 0);

    $utilization   = $stats['utilization'] ?? null;
    $utilization   = is_null($utilization)
        ? ($daysInRange > 0 ? ($activeDays / $daysInRange) : 0)
        : (float)$utilization;

    $longestStreak = (int)($stats['longest_streak'] ?? 0);
    $peakDay       = $stats['peak_day'] ?? null;

    $trendDelta    = (int)($stats['trend_delta'] ?? 0);
    $trendPercent  = $stats['trend_percent'] ?? null;

    $topMembers    = $stats['top_members'] ?? collect();

    // (opsional) ringkasan kompensasi bila controller mengirim
    $totalIzin      = (int)($stats['total_izin'] ?? 0);
    $totalKompHari  = (int)($stats['total_kompensasi_hari'] ?? 0);

    // query string helper
    $q = request()->query();
    unset($q['page']);
    $qs = http_build_query($q);

    // eksport (opsional)
    $hasExcel = Route::has('admin.laporan.kehadiran.excel');
    $hasPdf   = Route::has('admin.laporan.kehadiran.pdf');
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Ringkasan analitik absensi (check-in) dan metrik kompensasi/izin pada rentang terpilih."
>
    @once
        <style>
            .stat-number { font-variant-numeric: tabular-nums; letter-spacing: -0.02em; }
            .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.02); }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.22); }
        </style>
    @endonce

    {{-- TOAST --}}
    @if (session('success'))
        <x-ui.toast variant="success" class="mb-4">{{ session('success') }}</x-ui.toast>
    @endif
    @if (session('error'))
        <x-ui.toast variant="danger" class="mb-4">{{ session('error') }}</x-ui.toast>
    @endif

    <div class="space-y-6">

        {{-- 1) NAV + FILTER + EXPORT --}}
        <div class="flex flex-col lg:flex-row lg:items-start gap-4 justify-between">

            {{-- Tabs --}}
            @include('admin.laporan.kehadiran.partials.tabs', ['active' => 'ringkasan', 'qs' => $qs])

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.laporan.kehadiran.index') }}" class="flex-shrink-0">
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

            {{-- Export Dropdown --}}
            <div x-data="{ open:false }" class="relative">
                <button type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold
                           bg-white border border-brand-borderSoft hover:bg-gray-50 transition shadow-sm">
                    <i data-lucide="download" class="w-4 h-4 text-text-main"></i>
                    <span>Ekspor</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted"></i>
                </button>

                <div x-show="open" x-cloak @click.away="open=false"
                    class="absolute right-0 mt-2 w-64 rounded-2xl border border-brand-borderSoft bg-white shadow-2xl overflow-hidden z-30">
                    <a
                        class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-gray-50 {{ $hasExcel ? '' : 'opacity-60 cursor-not-allowed' }}"
                        href="{{ $hasExcel ? (route('admin.laporan.kehadiran.excel') . ($qs ? ('?'.$qs) : '')) : '#' }}"
                        @if(!$hasExcel) @click.prevent @endif
                    >
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <div class="min-w-0">
                            <div class="font-semibold text-text-main">Ekspor Excel</div>
                            <div class="text-xs text-text-muted">Rekap data absensi/kompensasi.</div>
                        </div>
                    </a>

                    <a
                        class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-gray-50 {{ $hasPdf ? '' : 'opacity-60 cursor-not-allowed' }}"
                        href="{{ $hasPdf ? (route('admin.laporan.kehadiran.pdf') . ($qs ? ('?'.$qs) : '')) : '#' }}"
                        @if(!$hasPdf) @click.prevent @endif
                    >
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <div class="min-w-0">
                            <div class="font-semibold text-text-main">Ekspor PDF</div>
                            <div class="text-xs text-text-muted">Siap cetak, rapi untuk laporan.</div>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        {{-- 2) KPI CARDS --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

            {{-- Total Check-in (dark style) --}}
            <div class="relative overflow-hidden rounded-2xl bg-[#1A1A1A] p-5 shadow-lg">
                <div class="relative z-10 flex h-full flex-col justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Total Check-in</p>
                        <h3 class="mt-2 text-3xl font-bold text-white stat-number">{{ number_format($totalCheckins) }}</h3>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="h-1.5 w-full rounded-full bg-gray-700">
                            <div class="h-1.5 rounded-full bg-gold-500 w-full"></div>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-gray-400">
                        Rentang: {{ Carbon::parse($start)->translatedFormat('d M Y') }} – {{ Carbon::parse($end)->translatedFormat('d M Y') }}
                    </p>
                </div>
                <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-gold-500/10 blur-3xl"></div>
            </div>

            <x-ui.card class="p-5 border-brand-borderSoft">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-muted">Member Unik</p>
                        <p class="mt-1 text-2xl font-bold text-text-main stat-number">{{ number_format($uniqueMembers) }}</p>
                    </div>
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="mt-3 text-xs text-text-muted">Minimal 1x hadir pada rentang.</p>
            </x-ui.card>

            <x-ui.card class="p-5 border-brand-borderSoft">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-muted">Rata-rata / Hari</p>
                        <p class="mt-1 text-2xl font-bold text-text-main stat-number">{{ number_format($avgPerDay, 2, ',', '.') }}</p>
                    </div>
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-600">
                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="mt-3 text-xs text-text-muted">Rata-rata check-in per hari ada data.</p>
            </x-ui.card>

            <x-ui.card class="p-5 border-brand-borderSoft">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-muted">Utilisasi</p>
                        <p class="mt-1 text-2xl font-bold text-text-main stat-number">{{ round($utilization * 100, 1) }}%</p>
                    </div>
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 text-amber-600">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="mt-3 text-xs text-text-muted">
                    Hari aktif: <span class="font-semibold text-text-main">{{ $activeDays }}</span> / {{ $daysInRange }}
                </p>
            </x-ui.card>

        </div>

        {{-- 3) CHART + SIDE CARDS --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            {{-- Chart --}}
            <div class="lg:col-span-8">
                <x-ui.card
                    x-data="window.absensiDashboard({
                        dailyRaw: @json($series['daily'] ?? [], JSON_UNESCAPED_UNICODE),
                        hourlyRaw: @json($series['hourly'] ?? [], JSON_UNESCAPED_UNICODE),
                        weekdayRaw: @json($series['weekday'] ?? [], JSON_UNESCAPED_UNICODE),
                        start: @json($start),
                        end: @json($end),
                    })"
                    x-init="init()"
                    class="p-6 border-brand-borderSoft h-full"
                >
                    <div class="mb-4 flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <h3 class="text-sm font-bold text-text-main">Visualisasi Kehadiran</h3>
                            <p class="text-xs text-text-muted mt-0.5">Tren harian, distribusi jam, dan pola hari.</p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" @click="setTab('tren')"
                                class="px-3 py-2 text-xs font-semibold rounded-lg border border-brand-borderSoft"
                                :class="tab==='tren' ? 'bg-white text-text-main' : 'bg-white/60 text-text-muted hover:bg-white'">
                                Tren
                            </button>
                            <button type="button" @click="setTab('jam')"
                                class="px-3 py-2 text-xs font-semibold rounded-lg border border-brand-borderSoft"
                                :class="tab==='jam' ? 'bg-white text-text-main' : 'bg-white/60 text-text-muted hover:bg-white'">
                                Per Jam
                            </button>
                            <button type="button" @click="setTab('hari')"
                                class="px-3 py-2 text-xs font-semibold rounded-lg border border-brand-borderSoft"
                                :class="tab==='hari' ? 'bg-white text-text-main' : 'bg-white/60 text-text-muted hover:bg-white'">
                                Pola Hari
                            </button>
                            <button type="button" @click="resetZoom()"
                                class="px-3 py-2 text-xs font-semibold rounded-lg border border-brand-borderSoft bg-white/70 hover:bg-white transition inline-flex items-center gap-2">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                Reset Zoom
                            </button>
                        </div>
                    </div>

                    <div x-show="loading" x-cloak class="rounded-xl border border-brand-borderSoft bg-white/60 p-4">
                        <div class="text-sm font-semibold text-text-main">Menyiapkan grafik…</div>
                        <div class="text-xs text-text-muted mt-1">Memuat pustaka grafik dan menormalisasi data.</div>
                    </div>

                    <div x-show="!loading && isAllEmpty()" x-cloak class="rounded-xl border border-brand-borderSoft bg-white/60 p-5">
                        <div class="text-sm font-extrabold text-text-main">Belum ada data grafik pada rentang ini.</div>
                        <div class="text-sm text-text-muted mt-1">
                            Pastikan controller mengirim <span class="font-semibold">daily/hourly/weekday</span>.
                        </div>
                    </div>

                    <div class="mt-3" x-show="!loading && !isAllEmpty()">
                        <div x-show="tab==='tren'" class="h-[360px]">
                            <canvas id="chartTren"></canvas>
                            <div class="text-xs text-text-muted mt-3">
                                Rata-rata 7 hari membantu tren lebih terbaca. Zoom: scroll, Pan: drag.
                            </div>
                        </div>

                        <div x-show="tab==='jam'" x-cloak class="h-[340px]">
                            <canvas id="chartJam"></canvas>
                        </div>

                        <div x-show="tab==='hari'" x-cloak class="h-[340px]">
                            <canvas id="chartHari"></canvas>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Side cards --}}
            <div class="lg:col-span-4 flex flex-col gap-6">

                <x-ui.card class="p-6 border-brand-borderSoft">
                    <h3 class="text-sm font-bold text-text-main mb-3">Insight Cepat</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Hari puncak</span>
                            <span class="font-bold text-text-main">
                                {{ $peakDay['label'] ?? '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Jumlah (hari puncak)</span>
                            <span class="font-bold text-text-main">
                                {{ $peakDay ? number_format((int)($peakDay['total'] ?? 0)) : '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Streak terpanjang</span>
                            <span class="font-bold text-text-main">{{ number_format($longestStreak) }} hari</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Perubahan (awal vs akhir)</span>
                            <span class="font-bold {{ $trendDelta >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $trendDelta >= 0 ? '+' : '' }}{{ number_format($trendDelta) }}
                            </span>
                        </div>

                        <div class="pt-3 border-t border-dashed border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-text-muted">Total izin</span>
                                <span class="font-bold text-text-main">{{ number_format($totalIzin) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-text-muted">Total kompensasi (hari)</span>
                                <span class="font-bold text-text-main">{{ number_format($totalKompHari) }}</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6 border-brand-borderSoft">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-text-main">Member Paling Rajin</h3>
                        <i data-lucide="trophy" class="w-4 h-4"></i>
                    </div>

                    <div class="space-y-2">
                        @if (count($topMembers) > 0)
                            @foreach ($topMembers as $i => $m)
                                @php
                                    $namaTop = $m->nama ?? $m->user_name ?? $m->name ?? '-';
                                    $totTop  = (int)($m->total ?? 0);
                                @endphp
                                <div class="flex items-center justify-between rounded-xl border border-brand-borderSoft px-3 py-2 hover:bg-gray-50 transition">
                                    <div class="min-w-0">
                                        <div class="text-sm font-extrabold text-text-main truncate">
                                            {{ $i + 1 }}. {{ $namaTop }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-extrabold text-text-main">{{ number_format($totTop) }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="rounded-xl border border-brand-borderSoft bg-gray-50 px-3 py-3 text-sm text-text-muted">
                                Belum ada data pada rentang ini.
                            </div>
                        @endif
                    </div>
                </x-ui.card>

            </div>
        </div>

        {{-- SCRIPT: Chart.js + Dashboard Factory (dipakai Ringkasan) --}}
        @once
            <script>
                (function(){
                    function loadScriptOnce(src){
                        return new Promise((resolve, reject) => {
                            const already = document.querySelector(`script[data-src="${src}"]`);
                            if (already) return resolve(true);

                            const s = document.createElement('script');
                            s.src = src;
                            s.async = true;
                            s.defer = true;
                            s.setAttribute('data-src', src);
                            s.onload = () => resolve(true);
                            s.onerror = () => reject(new Error('Gagal memuat: ' + src));
                            document.head.appendChild(s);
                        });
                    }

                    async function ensureChartStack(){
                        if (window.Chart) return true;
                        await loadScriptOnce('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js');
                        await loadScriptOnce('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js');
                        return !!window.Chart;
                    }

                    function tryRegisterZoom(){
                        if (!window.Chart || typeof window.Chart.register !== 'function') return;
                        const candidates = [
                            window.ChartZoom,
                            window.zoomPlugin,
                            window.ChartjsPluginZoom,
                            window['chartjs-plugin-zoom'],
                        ].filter(Boolean);

                        try {
                            const plugin = candidates[0];
                            if (plugin) window.Chart.register(plugin);
                        } catch (e) {}
                    }

                    window.absensiDashboard = function(payload) {
                        return {
                            tab: 'tren',
                            loading: true,
                            charts: { tren:null, jam:null, hari:null },

                            rawDaily: payload.dailyRaw || [],
                            rawHourly: payload.hourlyRaw || [],
                            rawWeekday: payload.weekdayRaw || [],

                            daily: [],
                            hourly: [],
                            weekday: [],

                            clampNum(v) {
                                const n = Number(v);
                                return Number.isFinite(n) ? n : 0;
                            },

                            fmtShortId(dateStr) {
                                try {
                                    const d = new Date(String(dateStr) + 'T00:00:00');
                                    if (isNaN(d.getTime())) return String(dateStr || '');
                                    return new Intl.DateTimeFormat('id-ID', { day:'2-digit', month:'short' }).format(d);
                                } catch (e) { return String(dateStr || ''); }
                            },

                            normalizeDaily(arr) {
                                const out = (arr || [])
                                    .map(x => {
                                        const date = x.date ?? x.tanggal ?? null;
                                        const total = this.clampNum(x.total ?? 0);
                                        return {
                                            date,
                                            label: x.label ?? (date ? this.fmtShortId(date) : ''),
                                            total,
                                            ma7: (x.ma7 === null || x.ma7 === undefined) ? null : this.clampNum(x.ma7),
                                            is_peak: !!(x.is_peak ?? false),
                                        };
                                    })
                                    .filter(x => x.date);

                                out.sort((a,b) => String(a.date).localeCompare(String(b.date)));

                                const hasMa = out.some(x => x.ma7 !== null);
                                if (!hasMa && out.length) {
                                    for (let i = 0; i < out.length; i++) {
                                        const start = Math.max(0, i - 6);
                                        const slice = out.slice(start, i + 1);
                                        const sum = slice.reduce((acc, cur) => acc + cur.total, 0);
                                        out[i].ma7 = Number((sum / slice.length).toFixed(2));
                                    }
                                }

                                if (!out.some(x => x.is_peak) && out.length) {
                                    let max = -1, idx = -1;
                                    out.forEach((x, i) => { if (x.total > max) { max = x.total; idx = i; } });
                                    if (idx >= 0) out[idx].is_peak = true;
                                }

                                return out;
                            },

                            normalizeHourly(arr) {
                                const map = new Map();
                                (arr || []).forEach(x => {
                                    const h = (x.hour ?? null);
                                    if (h === null || h === undefined) return;
                                    const hh = String(h).padStart(2, '0');
                                    map.set(hh, this.clampNum(x.total ?? 0));
                                });

                                const out = [];
                                for (let i = 0; i < 24; i++) {
                                    const hh = String(i).padStart(2, '0');
                                    out.push({ label: `${hh}:00`, total: this.clampNum(map.get(hh) ?? 0) });
                                }
                                return out;
                            },

                            normalizeWeekday(arr) {
                                const order = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                                const map = new Map();
                                (arr || []).forEach(x => {
                                    const k = x.label ?? x.hari ?? null;
                                    if (!k) return;
                                    map.set(String(k), this.clampNum(x.total ?? 0));
                                });
                                return order.map(h => ({ label: h, total: this.clampNum(map.get(h) ?? 0) }));
                            },

                            isAllEmpty() {
                                const a = (this.daily || []).some(x => x.total > 0);
                                const b = (this.hourly || []).some(x => x.total > 0);
                                const c = (this.weekday || []).some(x => x.total > 0);
                                return !(a || b || c);
                            },

                            destroy(name) {
                                if (this.charts[name]) {
                                    try { this.charts[name].destroy(); } catch(e){}
                                    this.charts[name] = null;
                                }
                            },

                            setTab(v) {
                                this.tab = v;
                                this.$nextTick(() => {
                                    if (v === 'tren') this.renderTren();
                                    if (v === 'jam')  this.renderJam();
                                    if (v === 'hari') this.renderHari();
                                });
                            },

                            resetZoom() {
                                const c = this.tab === 'tren' ? this.charts.tren : (this.tab === 'jam' ? this.charts.jam : this.charts.hari);
                                if (c && typeof c.resetZoom === 'function') c.resetZoom();
                            },

                            renderTren() {
                                this.destroy('tren');
                                const el = document.getElementById('chartTren');
                                if (!el || !window.Chart || !this.daily.length) return;

                                const labels = this.daily.map(x => x.label);
                                const data   = this.daily.map(x => x.total);
                                const ma7    = this.daily.map(x => x.ma7);

                                const peakIdx  = this.daily.findIndex(x => x.is_peak);
                                const peakData = data.map((v, i) => i === peakIdx ? v : null);

                                this.charts.tren = new window.Chart(el, {
                                    type: 'line',
                                    data: {
                                        labels,
                                        datasets: [
                                            { label: 'Check-in', data, fill: true, tension: 0.35, pointRadius: 3, pointHoverRadius: 7, borderWidth: 2 },
                                            { label: 'Rata-rata 7 Hari', data: ma7, fill: false, tension: 0.25, borderDash: [7,7], pointRadius: 0, borderWidth: 2 },
                                            { label: 'Hari Puncak', data: peakData, fill: false, showLine: false, pointRadius: 7, pointHoverRadius: 10, borderWidth: 2 },
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        interaction: { mode: 'index', intersect: false },
                                        plugins: {
                                            legend: { display: true },
                                            zoom: {
                                                zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'x' },
                                                pan:  { enabled: true, mode: 'x' }
                                            }
                                        },
                                        scales: {
                                            y: { beginAtZero: true, ticks: { precision: 0 } },
                                            x: { grid: { display: false } }
                                        }
                                    }
                                });
                            },

                            renderJam() {
                                this.destroy('jam');
                                const el = document.getElementById('chartJam');
                                if (!el || !window.Chart) return;

                                const labels = (this.hourly || []).map(x => x.label);
                                const data   = (this.hourly || []).map(x => x.total);

                                this.charts.jam = new window.Chart(el, {
                                    type: 'bar',
                                    data: { labels, datasets: [{ label: 'Check-in', data, borderWidth: 1 }] },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: true } },
                                        scales: {
                                            y: { beginAtZero: true, ticks: { precision: 0 } },
                                            x: { ticks: { maxRotation: 0, autoSkip: true } }
                                        }
                                    }
                                });
                            },

                            renderHari() {
                                this.destroy('hari');
                                const el = document.getElementById('chartHari');
                                if (!el || !window.Chart) return;

                                const labels = (this.weekday || []).map(x => x.label);
                                const data   = (this.weekday || []).map(x => x.total);

                                this.charts.hari = new window.Chart(el, {
                                    type: 'bar',
                                    data: { labels, datasets: [{ label: 'Check-in', data, borderWidth: 1 }] },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: true } },
                                        scales: {
                                            y: { beginAtZero: true, ticks: { precision: 0 } },
                                            x: { grid: { display: false } }
                                        }
                                    }
                                });
                            },

                            async init() {
                                this.daily   = this.normalizeDaily(this.rawDaily);
                                this.hourly  = this.normalizeHourly(this.rawHourly);
                                this.weekday = this.normalizeWeekday(this.rawWeekday);

                                try {
                                    await ensureChartStack();
                                    tryRegisterZoom();
                                } catch (e) {
                                    this.loading = false;
                                    return;
                                }

                                this.$nextTick(() => {
                                    this.loading = false;
                                    if (!this.isAllEmpty()) this.renderTren();
                                });
                            }
                        };
                    };
                })();
            </script>
        @endonce

    </div>
</x-layouts.admin>

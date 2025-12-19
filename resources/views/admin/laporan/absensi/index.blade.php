{{-- resources/views/admin/laporan/absensi/index.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    $pageTitle = $pageTitle ?? 'Laporan Absensi';

    $filters = $filters ?? [];
    $start   = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
    $end     = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
    $memberId= $filters['member_id'] ?? null;
    $sort    = $filters['sort'] ?? 'newest';
    $perPage = (int)($filters['per_page'] ?? 25);

    $stats  = $stats ?? [];
    $series = $series ?? ['daily'=>[], 'hourly'=>[], 'weekday'=>[]];

    // Controller kamu sekarang mengirim key berikut:
    $totalCheckins = (int)($stats['total_checkins'] ?? 0);
    $uniqueMembers = (int)($stats['unique_members'] ?? 0);
    $avgPerDay     = (float)($stats['avg_per_day'] ?? 0);

    $activeDays    = (int)($stats['active_days'] ?? 0);
    $daysInRange   = (int)($stats['days_in_range'] ?? 0);

    // utilization disepakati FRAKSI (0..1)
    $utilization   = $stats['utilization'] ?? null;
    if (is_null($utilization)) {
        $utilization = $daysInRange > 0 ? ($activeDays / $daysInRange) : 0;
    } else {
        $utilization = (float)$utilization;
    }

    $longestStreak = (int)($stats['longest_streak'] ?? 0);
    $peakDay       = $stats['peak_day'] ?? null;

    $trendDelta    = (int)($stats['trend_delta'] ?? 0);
    $trendPercent  = $stats['trend_percent'] ?? null;

    $topMembers    = $stats['top_members'] ?? collect();

    // Query string ekspor: pertahankan filter, buang page
    $q = request()->query();
    unset($q['page']);
    $qs = http_build_query($q);

    $hasExcel = Route::has('admin.laporan.absensi.excel');
    $hasPdf   = Route::has('admin.laporan.absensi.pdf');
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Dashboard analitik kehadiran member. Gunakan filter untuk mempersempit rentang analisis."
>
    {{-- TOAST --}}
    @if (session('success'))
        <x-ui.toast variant="success" class="mb-4">{{ session('success') }}</x-ui.toast>
    @endif
    @if (session('error'))
        <x-ui.toast variant="danger" class="mb-4">{{ session('error') }}</x-ui.toast>
    @endif

    {{-- HEADER: BADGE RENTANG + EKSPOR --}}
    <div class="flex items-start justify-between gap-3 flex-wrap mb-4">
        <div class="flex items-center gap-2 flex-wrap">
            <x-ui.badge variant="neutral">
                Rentang: {{ Carbon::parse($start)->translatedFormat('d M Y') }} – {{ Carbon::parse($end)->translatedFormat('d M Y') }}
            </x-ui.badge>

            @if($memberId)
                <x-ui.badge variant="warning">Filter ID Member: {{ $memberId }}</x-ui.badge>
            @endif
        </div>

        <div x-data="{ open:false }" class="relative">
            <button type="button"
                @click="open = !open"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold
                       bg-brand-black text-white hover:opacity-90 transition shadow-header">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Ekspor</span>
                <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </button>

            <div x-show="open" x-cloak @click.away="open=false"
                class="absolute right-0 mt-2 w-64 rounded-2xl border border-brand-borderSoft bg-white shadow-2xl overflow-hidden z-30">
                <a
                    class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-gray-50 {{ $hasExcel ? '' : 'opacity-60 cursor-not-allowed' }}"
                    href="{{ $hasExcel ? (route('admin.laporan.absensi.excel') . ($qs ? ('?'.$qs) : '')) : '#' }}"
                    @if(!$hasExcel) @click.prevent @endif
                >
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <div class="min-w-0">
                        <div class="font-semibold text-text-main">Ekspor Excel</div>
                        <div class="text-xs text-text-muted">Rekap lengkap dalam format spreadsheet.</div>
                    </div>
                </a>

                <a
                    class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-gray-50 {{ $hasPdf ? '' : 'opacity-60 cursor-not-allowed' }}"
                    href="{{ $hasPdf ? (route('admin.laporan.absensi.pdf') . ($qs ? ('?'.$qs) : '')) : '#' }}"
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

    {{-- KPI (COUNT-UP) --}}
    <div
        x-data="{
            kpi: {
                total: {{ $totalCheckins }},
                unik: {{ $uniqueMembers }},
                rata: {{ (float)$avgPerDay }},
                util: {{ round($utilization * 100, 1) }},
                delta: {{ $trendDelta }},
            },
            shown: { total:0, unik:0, rata:0, util:0, delta:0 },
            animate(key, to, decimals = 0) {
                const duration = 900;
                const t0 = performance.now();
                const from = 0;

                const step = (t) => {
                    const p = Math.min(1, (t - t0) / duration);
                    const eased = 1 - Math.pow(1 - p, 3);
                    const val = from + (to - from) * eased;
                    this.shown[key] = decimals ? Number(val.toFixed(decimals)) : Math.round(val);
                    if (p < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            },
            init(){
                this.animate('total', this.kpi.total, 0);
                this.animate('unik',  this.kpi.unik,  0);
                this.animate('rata',  this.kpi.rata,  2);
                this.animate('util',  this.kpi.util,  1);
                this.animate('delta', this.kpi.delta, 0);
            }
        }"
        class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3 mb-6"
    >
        <x-ui.card class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-text-muted">Total Check-in</div>
                    <div class="mt-2 text-4xl font-extrabold text-text-main" x-text="shown.total"></div>
                    <div class="mt-2 text-xs text-text-muted">Dalam rentang aktif.</div>
                </div>
                <div class="rounded-2xl p-2 bg-white/70 border border-brand-borderSoft">
                    <i data-lucide="scan" class="w-5 h-5"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-text-muted">Member Unik</div>
                    <div class="mt-2 text-4xl font-extrabold text-text-main" x-text="shown.unik"></div>
                    <div class="mt-2 text-xs text-text-muted">Minimal 1x hadir.</div>
                </div>
                <div class="rounded-2xl p-2 bg-white/70 border border-brand-borderSoft">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-text-muted">Rata-rata / Hari</div>
                    <div class="mt-2 text-4xl font-extrabold text-text-main" x-text="shown.rata"></div>
                    <div class="mt-2 text-xs text-text-muted">Rata-rata check-in (hari ada data).</div>
                </div>
                <div class="rounded-2xl p-2 bg-white/70 border border-brand-borderSoft">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-text-muted">Utilisasi</div>
                    <div class="mt-2 text-4xl font-extrabold text-text-main">
                        <span x-text="shown.util"></span><span>%</span>
                    </div>
                    <div class="mt-2 text-xs text-text-muted">Hari aktif dibanding total hari.</div>
                </div>
                <div class="rounded-2xl p-2 bg-white/70 border border-brand-borderSoft">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-text-muted">Hari Puncak</div>
                    <div class="mt-2 text-xl font-extrabold text-text-main">{{ $peakDay['label'] ?? '-' }}</div>
                    <div class="mt-2 text-xs text-text-muted">
                        {{ $peakDay ? (number_format($peakDay['total'] ?? 0).' check-in') : 'Tidak ada data.' }}
                    </div>
                </div>
                <div class="rounded-2xl p-2 bg-white/70 border border-brand-borderSoft">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-text-muted">Perubahan (Awal vs Akhir)</div>
                    <div class="mt-2 text-3xl font-extrabold text-text-main">
                        <span class="{{ $trendDelta >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $trendDelta >= 0 ? '+' : '' }}{{ number_format($trendDelta) }}
                        </span>
                    </div>
                    <div class="mt-2 text-xs text-text-muted">
                        @if(!is_null($trendPercent))
                            Setara {{ $trendPercent >= 0 ? '+' : '' }}{{ $trendPercent }}%.
                        @else
                            Persentase tidak dihitung (nilai awal = 0).
                        @endif
                    </div>
                </div>
                <div class="rounded-2xl p-2 bg-white/70 border border-brand-borderSoft">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- FILTER (UX) --}}
    <x-ui.card
        x-data="{
            advanced: false,
            setQuick(v){
                const url = new URL(window.location.href);
                url.searchParams.set('quick', v);
                url.searchParams.set('mode', 'custom');
                url.searchParams.delete('page');
                window.location.href = url.toString();
            },
            resetAll(){
                window.location.href = '{{ route('admin.laporan.absensi.index') }}';
            }
        }"
        class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5 md:p-6 mb-6"
    >
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <div class="text-xl font-bold text-text-main">Filter</div>
                <div class="text-sm text-text-muted mt-1">
                    Pilih rentang cepat untuk analisis instan, atau buka opsi lanjut untuk parameter tambahan.
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="resetAll()"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft bg-white/70 hover:bg-white transition">
                    Reset
                </button>
                <button type="button" @click="setQuick('7_hari')"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft bg-white/70 hover:bg-white transition">
                    7 Hari
                </button>
                <button type="button" @click="setQuick('30_hari')"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft bg-white/70 hover:bg-white transition">
                    30 Hari
                </button>
                <button type="button" @click="setQuick('bulan_ini')"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft bg-white/70 hover:bg-white transition">
                    Bulan Ini
                </button>

                <button type="button" @click="advanced = !advanced"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft bg-brand-black text-white hover:opacity-90 transition inline-flex items-center gap-2">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                    <span x-text="advanced ? 'Tutup Opsi Lanjut' : 'Opsi Lanjut'"></span>
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.laporan.absensi.index') }}" class="mt-5">
            <input type="hidden" name="mode" value="custom">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-3">
                    <x-ui.label>Tanggal Mulai</x-ui.label>
                    <x-ui.input type="date" name="start_date" value="{{ $start }}" class="w-full"/>
                </div>

                <div class="lg:col-span-3">
                    <x-ui.label>Tanggal Akhir</x-ui.label>
                    <x-ui.input type="date" name="end_date" value="{{ $end }}" class="w-full"/>
                </div>

                <div class="lg:col-span-3" x-show="advanced" x-cloak>
                    <x-ui.label>ID Member (opsional)</x-ui.label>
                    <x-ui.input type="number" min="1" name="member_id" value="{{ $memberId }}" placeholder="Contoh: 12" class="w-full"/>
                </div>

                <div class="lg:col-span-3" x-show="advanced" x-cloak>
                    <x-ui.label>Urutkan</x-ui.label>
                    <x-ui.select name="sort" class="w-full">
                        <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                        <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
                    </x-ui.select>
                </div>

                <div class="lg:col-span-3" x-show="advanced" x-cloak>
                    <x-ui.label>Per Halaman</x-ui.label>
                    <x-ui.select name="per_page" class="w-full">
                        @foreach ([10, 25, 50, 100, 200] as $n)
                            <option value="{{ $n }}" @selected((int)$perPage === $n)>{{ $n }}</option>
                        @endforeach
                    </x-ui.select>
                    <div class="text-xs text-text-muted mt-1">Semakin besar, semakin berat di render.</div>
                </div>

                <div class="lg:col-span-9 flex items-end justify-end">
                    <button
                        type="submit"
                        class="rounded-2xl px-6 py-3 text-sm font-extrabold bg-accent-600 text-white hover:opacity-90 transition shadow-xl"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-brand-borderSoft bg-white/60 p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-text-main font-semibold">Ringkasan data</div>
                <div class="text-sm text-text-muted">
                    Hari ada data: <span class="font-bold text-text-main">{{ $activeDays }}</span> / {{ $daysInRange }}
                    ({{ round($utilization * 100, 1) }}%)
                    <span class="mx-2">•</span>
                    Streak terpanjang: <span class="font-bold text-text-main">{{ $longestStreak }}</span> hari
                </div>
            </div>
        </form>
    </x-ui.card>

    {{-- VISUALISASI --}}
    <x-ui.card
        x-data="window.absensiDashboard({
            dailyRaw: @json($series['daily'] ?? [], JSON_UNESCAPED_UNICODE),
            hourlyRaw: @json($series['hourly'] ?? [], JSON_UNESCAPED_UNICODE),
            weekdayRaw: @json($series['weekday'] ?? [], JSON_UNESCAPED_UNICODE),
            start: @json($start),
            end: @json($end),
        })"
        class="rounded-3xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-5 md:p-6 mb-6"
    >
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <div class="text-xl font-bold text-text-main">Visualisasi</div>
                <div class="text-sm text-text-muted mt-1">
                    Tren (area + rata-rata 7 hari + hari puncak), distribusi jam, dan pola hari.
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="setTab('tren')"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft"
                    :class="tab==='tren' ? 'bg-white text-text-main' : 'bg-white/60 text-text-muted hover:bg-white'">
                    Tren
                </button>

                <button type="button" @click="setTab('jam')"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft"
                    :class="tab==='jam' ? 'bg-white text-text-main' : 'bg-white/60 text-text-muted hover:bg-white'">
                    Per Jam
                </button>

                <button type="button" @click="setTab('hari')"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft"
                    :class="tab==='hari' ? 'bg-white text-text-main' : 'bg-white/60 text-text-muted hover:bg-white'">
                    Pola Hari
                </button>

                <button type="button" @click="resetZoom()"
                    class="rounded-2xl px-4 py-2 text-sm font-semibold border border-brand-borderSoft bg-white/70 hover:bg-white transition inline-flex items-center gap-2">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Reset Zoom</span>
                </button>
            </div>
        </div>

        {{-- STATUS LOADING --}}
        <div x-show="loading" x-cloak class="mt-5 rounded-2xl border border-brand-borderSoft bg-white/60 p-4">
            <div class="text-sm font-semibold text-text-main">Menyiapkan grafik…</div>
            <div class="text-xs text-text-muted mt-1">Memuat pustaka grafik dan menormalisasi data.</div>
        </div>

        {{-- EMPTY STATE --}}
        <div x-show="!loading && isAllEmpty()" x-cloak class="mt-5 rounded-2xl border border-brand-borderSoft bg-white/60 p-5">
            <div class="text-sm font-extrabold text-text-main">Belum ada data grafik pada rentang ini.</div>
            <div class="text-sm text-text-muted mt-1">
                Jika kamu yakin data ada, pastikan controller mengirim <span class="font-semibold">daily/hourly/weekday</span>.
                Seri <span class="font-semibold">daily</span> minimal butuh <span class="font-semibold">date</span> dan <span class="font-semibold">total</span>.
            </div>
        </div>

        <div class="mt-5" x-show="!loading && !isAllEmpty()">
            <div x-show="tab==='tren'" class="h-[360px]">
                <canvas id="chartTren"></canvas>
                <div class="text-xs text-text-muted mt-3">
                    Catatan: rata-rata 7 hari membantu tren lebih “terbaca”. Zoom pakai scroll, geser untuk pan.
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

    {{-- TOP MEMBER + TABEL --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 mb-6">
        <x-ui.card class="xl:col-span-4 rounded-3xl border border-brand-borderSoft bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-bold text-text-main">Member Paling Rajin</div>
                    <div class="text-sm text-text-muted">10 member dengan check-in terbanyak.</div>
                </div>
                <i data-lucide="trophy" class="w-5 h-5"></i>
            </div>

            <div class="mt-4 space-y-2">
                @if (count($topMembers) > 0)
                    @foreach ($topMembers as $i => $m)
                        @php
                            // Controller kamu mengirim object: { member_id, nama, total }
                            $namaTop = $m->nama ?? $m->user_name ?? $m->name ?? '-';
                            $midTop  = $m->member_id ?? '-';
                            $totTop  = $m->total ?? 0;
                        @endphp
                        <div class="flex items-center justify-between rounded-2xl border border-brand-borderSoft px-3 py-2 hover:bg-gray-50 transition">
                            <div class="min-w-0">
                                <div class="text-sm font-extrabold text-text-main truncate">
                                    {{ $i + 1 }}. {{ $namaTop }}
                                </div>
                                <div class="text-xs text-text-muted">ID Member: {{ $midTop }}</div>
                            </div>
                            <div class="text-sm font-extrabold text-text-main">{{ $totTop }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="rounded-2xl border border-brand-borderSoft bg-gray-50 px-3 py-3 text-sm text-text-muted">
                        Belum ada data pada rentang ini.
                    </div>
                @endif
            </div>
        </x-ui.card>

        
    </div>

    {{-- SCRIPT: Dashboard Chart --}}
    <script>
        // ====== Helper loader (CDN fallback jika Vite tidak expose window.Chart) ======
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
                // Jika Vite sudah pasang window.Chart, tidak perlu CDN
                if (window.Chart) return true;

                // CDN Chart.js (UMD)
                await loadScriptOnce('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js');

                // Optional: plugin zoom (kalau mau)
                // Jika kamu tidak butuh zoom, boleh hapus 2 baris ini.
                await loadScriptOnce('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js');

                return !!window.Chart;
            }

            function tryRegisterZoom(){
                // Registrasi plugin zoom paling aman (beda build bisa beda nama global)
                if (!window.Chart) return;

                // Kandidat global yang umum
                const candidates = [
                    window.ChartZoom,
                    window.zoomPlugin,
                    window.ChartjsPluginZoom,
                    window['chartjs-plugin-zoom'],
                ].filter(Boolean);

                // Jika tidak ketemu, cari lewat window keys
                if (candidates.length === 0) {
                    const key = Object.keys(window).find(k => k.toLowerCase().includes('zoom') && typeof window[k] === 'object');
                    if (key) candidates.push(window[key]);
                }

                // Register jika ada
                try {
                    const plugin = candidates[0];
                    if (plugin && typeof window.Chart.register === 'function') {
                        window.Chart.register(plugin);
                    }
                } catch (e) {
                    // Tidak fatal: chart tetap jalan tanpa zoom
                }
            }

            // Expose dashboard factory ke window agar Alpine pasti bisa akses
            window.absensiDashboard = function(payload) {
                return {
                    tab: 'tren',
                    loading: true,
                    charts: { tren:null, jam:null, hari:null },

                    rawDaily: payload.dailyRaw || [],
                    rawHourly: payload.hourlyRaw || [],
                    rawWeekday: payload.weekdayRaw || [],

                    start: payload.start,
                    end: payload.end,

                    daily: [],
                    hourly: [],
                    weekday: [],

                    clampNum(v) {
                        const n = Number(v);
                        return Number.isFinite(n) ? n : 0;
                    },

                    formatTanggalPendekId(dateStr) {
                        try {
                            // dateStr dari controller: "YYYY-MM-DD"
                            const d = new Date(String(dateStr) + 'T00:00:00');
                            if (isNaN(d.getTime())) return String(dateStr || '');
                            return new Intl.DateTimeFormat('id-ID', { day:'2-digit', month:'short' }).format(d);
                        } catch (e) { return String(dateStr || ''); }
                    },

                    normalisasiHarian(arr) {
                        const out = (arr || [])
                            .map(x => {
                                const date = x.date ?? x.tanggal ?? null;
                                const total = this.clampNum(x.total ?? 0);
                                return {
                                    date,
                                    label: x.label ?? (date ? this.formatTanggalPendekId(date) : ''),
                                    total,
                                    ma7: (x.ma7 === null || x.ma7 === undefined) ? null : this.clampNum(x.ma7),
                                    is_peak: !!(x.is_peak ?? false),
                                };
                            })
                            .filter(x => x.date);

                        out.sort((a,b) => String(a.date).localeCompare(String(b.date)));

                        // Jika ma7 belum ada, hitung sederhana (window 7)
                        const hasMa = out.some(x => x.ma7 !== null);
                        if (!hasMa && out.length) {
                            for (let i = 0; i < out.length; i++) {
                                const start = Math.max(0, i - 6);
                                const slice = out.slice(start, i + 1);
                                const sum = slice.reduce((acc, cur) => acc + cur.total, 0);
                                out[i].ma7 = Number((sum / slice.length).toFixed(2));
                            }
                        }

                        // Jika is_peak belum ada, tentukan peak
                        if (!out.some(x => x.is_peak) && out.length) {
                            let max = -1, idx = -1;
                            out.forEach((x, i) => { if (x.total > max) { max = x.total; idx = i; } });
                            if (idx >= 0) out[idx].is_peak = true;
                        }

                        return out;
                    },

                    normalisasiJam(arr) {
                        // Controller mengirim { hour:int, label:"HH:00", total:int }
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

                    normalisasiHari(arr) {
                        // Controller bisa kirim urutan Minggu..Sabtu atau bebas
                        const urut = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                        const map = new Map();
                        (arr || []).forEach(x => {
                            const k = x.label ?? x.hari ?? null;
                            if (!k) return;
                            map.set(String(k), this.clampNum(x.total ?? 0));
                        });

                        return urut.map(h => ({ label: h, total: this.clampNum(map.get(h) ?? 0) }));
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
                        if (!c) return;
                        if (typeof c.resetZoom === 'function') c.resetZoom();
                    },

                    renderTren() {
                        this.destroy('tren');
                        const el = document.getElementById('chartTren');
                        if (!el || !window.Chart) return;

                        if (!this.daily || this.daily.length === 0) return;

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
                                animation: { duration: 900, easing: 'easeOutQuart' },
                                plugins: {
                                    legend: { display: true },
                                    tooltip: {
                                        callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.formattedValue}` }
                                    },
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
                                animation: { duration: 900, easing: 'easeOutQuart' },
                                plugins: {
                                    legend: { display: true },
                                    tooltip: { callbacks: { label: (ctx) => `Check-in: ${ctx.formattedValue}` } },
                                },
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
                                animation: { duration: 900, easing: 'easeOutQuart' },
                                plugins: { legend: { display: true } },
                                scales: {
                                    y: { beginAtZero: true, ticks: { precision: 0 } },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    },

                    async init() {
                        // Normalisasi series (sinkron dengan controller, tapi tetap tahan banting)
                        this.daily   = this.normalisasiHarian(this.rawDaily);
                        this.hourly  = this.normalisasiJam(this.rawHourly);
                        this.weekday = this.normalisasiHari(this.rawWeekday);

                        try {
                            await ensureChartStack();
                            tryRegisterZoom();
                        } catch (e) {
                            // Kalau gagal memuat Chart, tampilkan empty state saja
                            this.loading = false;
                            return;
                        }

                        this.$nextTick(() => {
                            this.loading = false;
                            if (!this.isAllEmpty()) {
                                // render default
                                this.renderTren();
                            }
                        });
                    }
                };
            };
        })();
    </script>
</x-layouts.admin>

{{-- resources/views/admin/laporan/kehadiran/kompensasi.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Data Kompensasi / Izin';

    $filters = $filters ?? [];
    $start   = $filters['start_date'] ?? now()->subDays(29)->toDateString();
    $end     = $filters['end_date'] ?? now()->toDateString();
    $sort    = $filters['sort'] ?? 'newest';
    $perPage = $filters['per_page'] ?? 25;

    $status  = $filters['status'] ?? (string) request('status', '');

    $rows    = $rows ?? null;
    $total   = (int) ($total ?? (method_exists($rows, 'total') ? $rows->total() : 0));
    $sumHari = (int) ($sumHari ?? 0);

    // Aman untuk paginator/collection
    $collection = $rows
        ? (method_exists($rows, 'getCollection') ? $rows->getCollection() : collect($rows))
        : collect();

    // query string helper (untuk tabs)
    $q = request()->query();
    unset($q['page']);
    $qs = http_build_query($q);

    $fmtDateTime = function ($dt) {
        try {
            return $dt ? Carbon::parse($dt)->translatedFormat('d M Y, H:i') : '-';
        } catch (\Throwable $e) {
            return (string) $dt;
        }
    };

    $fmtDate = function ($d) {
        try {
            return $d ? Carbon::parse($d)->translatedFormat('d M Y') : '-';
        } catch (\Throwable $e) {
            return (string) $d;
        }
    };

    $statusLabel = function ($s) {
        $s = strtolower((string) $s);
        return match ($s) {
            'pending'   => 'PENDING',
            'disetujui' => 'DISETUJUI',
            'ditolak'   => 'DITOLAK',
            default     => '—',
        };
    };

    $statusClass = function ($s) {
        $s = strtolower((string) $s);
        return match ($s) {
            'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
            'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ditolak'   => 'bg-red-50 text-red-700 border-red-200',
            default     => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    };

    $statusHeader = $status ? strtoupper($status) : 'SEMUA';
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Rekap pengajuan izin latihan dan kompensasi hari pada rentang terpilih."
>
    {{-- TOAST --}}
    @if (session('success'))
        <x-ui.toast variant="success" class="mb-4">{{ session('success') }}</x-ui.toast>
    @endif
    @if (session('error'))
        <x-ui.toast variant="danger" class="mb-4">{{ session('error') }}</x-ui.toast>
    @endif

    <div class="space-y-6">

        {{-- NAV + FILTER --}}
        <div class="flex flex-col lg:flex-row lg:items-start gap-4 justify-between">

            {{-- Tabs --}}
            @include('admin.laporan.kehadiran.partials.tabs', ['active' => 'kompensasi', 'qs' => $qs])

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.laporan.kehadiran.kompensasi') }}" class="flex-shrink-0">
                <div class="flex flex-wrap items-center gap-2 bg-white border border-brand-borderSoft rounded-lg p-1.5 shadow-sm">

                    <div class="flex items-center gap-2 px-2 border-r border-gray-100">
                        <i data-lucide="calendar" class="w-4 h-4 text-gold-500"></i>
                    </div>

                    <input type="date" name="start_date" value="{{ $start }}"
                        class="border-none text-xs font-medium text-text-main focus:ring-0 p-1 bg-transparent w-36 cursor-pointer">
                    <span class="text-text-muted text-xs">➜</span>
                    <input type="date" name="end_date" value="{{ $end }}"
                        class="border-none text-xs font-medium text-text-main focus:ring-0 p-1 bg-transparent w-36 cursor-pointer">

                    <select name="status"
                        class="border-none text-xs font-semibold text-text-main focus:ring-0 p-1 bg-transparent cursor-pointer">
                        <option value="" @selected($status === '')>Semua status</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="disetujui" @selected($status === 'disetujui')>Disetujui</option>
                        <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
                    </select>

                    <select name="sort"
                        class="border-none text-xs font-semibold text-text-main focus:ring-0 p-1 bg-transparent cursor-pointer">
                        <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                        <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
                    </select>

                    <select name="per_page"
                        class="border-none text-xs font-semibold text-text-main focus:ring-0 p-1 bg-transparent cursor-pointer">
                        @foreach ([10, 25, 50, 100, 200] as $n)
                            <option value="{{ $n }}" @selected((int)$perPage === $n)>{{ $n }}/hal</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="ml-2 px-3 py-1.5 bg-black text-white text-xs font-bold rounded hover:bg-gray-800 transition shadow-sm">
                        Filter
                    </button>

                    <a href="{{ route('admin.laporan.kehadiran.kompensasi') }}"
                        class="px-3 py-1.5 text-xs font-bold rounded border border-brand-borderSoft bg-white hover:bg-gray-50 transition">
                        Reset
                    </a>
                </div>
            </form>

        </div>

        {{-- TABLE --}}
        <x-ui.card class="p-0 border-brand-borderSoft overflow-hidden">
            <div class="px-6 py-5 bg-brand-cardSoft border-b border-brand-borderSoft">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <div class="text-lg font-extrabold text-text-main">Data Kompensasi / Izin</div>
                        <div class="text-sm text-text-muted">
                            Rentang: {{ $fmtDate($start) }} – {{ $fmtDate($end) }}
                            <span class="mx-2">•</span>
                            Status: <span class="font-extrabold text-text-main">{{ $statusHeader }}</span>
                        </div>
                    </div>

                    <div class="text-sm font-bold text-text-main text-right">
                        <div>Total: <span class="stat-number">{{ number_format($total) }}</span></div>
                        <div class="text-xs text-text-muted mt-0.5">Total kompensasi: <span class="font-extrabold text-text-main">{{ number_format($sumHari) }}</span> hari</div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/70">
                        <tr class="text-left text-text-muted">
                            <th class="px-6 py-4 font-extrabold">Tanggal</th>
                            <th class="px-6 py-4 font-extrabold">Member</th>
                            <th class="px-6 py-4 font-extrabold">Jenis / Alasan</th>
                            <th class="px-6 py-4 font-extrabold">Kompensasi</th>
                            <th class="px-6 py-4 font-extrabold text-right">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft bg-brand-cardSoft">
                        @forelse ($collection as $r)
                            @php
                                $nama = $r->user_name ?? '-';
                                $memberId = $r->member_id ?? null;

                                $created = $r->created_at ?? null;

                                $mulai = $r->tanggal_mulai ?? null;
                                $selesai = $r->tanggal_selesai ?? null;

                                $dur = (int) ($r->durasi_hari ?? $r->durasi_izin_disetujui ?? $r->jumlah_hari ?? 0);

                                $alasan = trim((string) ($r->alasan ?? ''));
                                $alasanShort = $alasan !== '' ? (mb_strlen($alasan) > 80 ? mb_substr($alasan, 0, 80) . '…' : $alasan) : '—';

                                $st = strtolower((string) ($r->status ?? ''));
                                $badgeCls = $statusClass($st);
                                $badgeTxt = $statusLabel($st);
                            @endphp

                            <tr class="hover:bg-white/40 transition">
                                <td class="px-6 py-5">
                                    <div class="font-semibold text-text-main">
                                        {{ $fmtDateTime($created) }}
                                    </div>
                                    <div class="text-xs text-text-muted">
                                        Periode: {{ $fmtDate($mulai) }}{{ $selesai ? (' – ' . $fmtDate($selesai)) : '' }}
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="font-extrabold text-text-main">
                                        {{ $nama }}
                                    </div>
                                    <div class="text-xs text-text-muted">
                                        ID: {{ $memberId ? (int)$memberId : '-' }}
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="font-extrabold text-text-main">Izin</div>
                                    <div class="text-sm text-text-muted mt-0.5">
                                        {{ $alasanShort }}
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="text-lg font-extrabold text-text-main">
                                        {{ $dur > 0 ? number_format($dur) : '—' }}
                                        <span class="text-sm font-semibold text-text-muted">hari</span>
                                    </div>
                                    @if (!empty($r->durasi_izin_disetujui))
                                        <div class="text-xs text-text-muted">Disetujui: {{ (int)$r->durasi_izin_disetujui }} hari</div>
                                    @endif
                                </td>

                                <td class="px-6 py-5 text-right">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full border text-xs font-extrabold {{ $badgeCls }}">
                                        {{ $badgeTxt }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10">
                                    <div class="rounded-2xl border border-brand-borderSoft bg-white/60 p-6">
                                        <div class="text-sm font-extrabold text-text-main">Belum ada data kompensasi/izin.</div>
                                        <div class="text-sm text-text-muted mt-1">
                                            Coba ubah rentang tanggal/status, atau pastikan data sudah masuk pada tabel <span class="font-semibold">izin_latihan</span>.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rows && method_exists($rows, 'links'))
                <div class="px-6 py-4 bg-white border-t border-brand-borderSoft">
                    {{ $rows->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.admin>

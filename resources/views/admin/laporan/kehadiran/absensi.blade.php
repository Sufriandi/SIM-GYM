{{-- resources/views/admin/laporan/kehadiran/absensi.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Data Absensi';

    $filters = $filters ?? [];
    $start   = $filters['start_date'] ?? now()->subDays(29)->toDateString();
    $end     = $filters['end_date'] ?? now()->toDateString();
    $sort    = $filters['sort'] ?? 'newest';
    $perPage = $filters['per_page'] ?? 25;

    $rows  = $rows ?? null;
    $total = (int) ($total ?? (method_exists($rows, 'total') ? $rows->total() : 0));

    // Aman untuk paginator/collection
    $collection = $rows
        ? (method_exists($rows, 'getCollection') ? $rows->getCollection() : collect($rows))
        : collect();

    // query string helper (untuk tabs)
    $q = request()->query();
    unset($q['page']);
    $qs = http_build_query($q);

    $fmtDate = function ($d) {
        try {
            return $d ? Carbon::parse($d)->translatedFormat('d M Y') : '-';
        } catch (\Throwable $e) {
            return (string) $d;
        }
    };

    $fmtTime = function ($t) {
        if (!$t) return '-';
        $s = (string) $t;
        return strlen($s) >= 5 ? substr($s, 0, 5) : $s;
    };
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Rekap detail check-in member pada rentang tanggal terpilih."
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
            @include('admin.laporan.kehadiran.partials.tabs', ['active' => 'absensi', 'qs' => $qs])

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.laporan.kehadiran.absensi') }}" class="flex-shrink-0">
                <div class="flex flex-wrap items-center gap-2 bg-white border border-brand-borderSoft rounded-lg p-1.5 shadow-sm">

                    <div class="flex items-center gap-2 px-2 border-r border-gray-100">
                        <i data-lucide="calendar" class="w-4 h-4 text-gold-500"></i>
                    </div>

                    <input type="date" name="start_date" value="{{ $start }}"
                        class="border-none text-xs font-medium text-text-main focus:ring-0 p-1 bg-transparent w-36 cursor-pointer">
                    <span class="text-text-muted text-xs">➜</span>
                    <input type="date" name="end_date" value="{{ $end }}"
                        class="border-none text-xs font-medium text-text-main focus:ring-0 p-1 bg-transparent w-36 cursor-pointer">

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

                    <a href="{{ route('admin.laporan.kehadiran.absensi') }}"
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
                        <div class="text-lg font-extrabold text-text-main">Data Absensi</div>
                        <div class="text-sm text-text-muted">
                            Rentang: {{ $fmtDate($start) }} – {{ $fmtDate($end) }}
                        </div>
                    </div>
                    <div class="text-sm font-bold text-text-main">
                        Total: <span class="stat-number">{{ number_format($total) }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/70">
                        <tr class="text-left text-text-muted">
                            <th class="px-6 py-4 font-extrabold">Tanggal</th>
                            <th class="px-6 py-4 font-extrabold">Member</th>
                            <th class="px-6 py-4 font-extrabold">Keterangan</th>
                            <th class="px-6 py-4 font-extrabold">Sumber</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft bg-brand-cardSoft">
                        @forelse ($collection as $r)
                            @php
                                $tanggal = $r->tanggal ?? null;
                                $jamMasuk = $r->jam_masuk ?? null;

                                $nama = $r->user_name ?? '-';
                                $memberId = $r->member_id ?? null;

                                $ket = $r->keterangan ?? '-';
                                $sumber = $r->sumber ?? ($r->ip_address ?? ($r->device_info ?? '-'));

                                $isValid = isset($r->is_valid) ? (bool) $r->is_valid : true;

                                $badgeCls = $isValid
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                    : 'bg-red-50 text-red-700 border-red-200';
                                $badgeText = $isValid ? 'VALID' : 'TIDAK VALID';
                            @endphp

                            <tr class="hover:bg-white/40 transition">
                                <td class="px-6 py-5">
                                    <div class="font-semibold text-text-main">
                                        {{ $fmtDate($tanggal) }}, {{ $fmtTime($jamMasuk) }}
                                    </div>
                                    {{-- <div class="text-xs text-text-muted">
                                        ID Row: {{ (int)($r->id ?? 0) }}
                                    </div> --}}
                                </td>

                                <td class="px-6 py-5">
                                    <div class="font-extrabold text-text-main">
                                        {{ $nama }}
                                    </div>
                                    {{-- <div class="text-xs text-text-muted">
                                        ID: {{ $memberId ? (int)$memberId : '-' }}
                                    </div> --}}
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-extrabold {{ $badgeCls }}">
                                            {{ $badgeText }}
                                        </span>
                                        {{-- <span class="text-text-main font-semibold">
                                            {{ $ket }}
                                        </span> --}}
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="text-text-main font-semibold truncate max-w-[360px]">
                                        {{ $sumber ?: '-' }}
                                    </div>
                                    <div class="text-xs text-text-muted">
                                        {{ $r->ip_address ? 'IP' : ($r->device_info ? 'Device' : '-') }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10">
                                    <div class="rounded-2xl border border-brand-borderSoft bg-white/60 p-6">
                                        <div class="text-sm font-extrabold text-text-main">Belum ada data absensi.</div>
                                        <div class="text-sm text-text-muted mt-1">
                                            Coba ubah rentang tanggal atau pastikan data sudah tercatat pada tabel <span class="font-semibold">kehadiran_members</span>.
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

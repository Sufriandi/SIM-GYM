{{-- resources/views/admin/dashboard/index.blade.php --}}

@php
    use Illuminate\Support\Str;

    // =========================================================================
    // DATA PREPARATION & FALLBACKS
    // =========================================================================
    $totalMembers       = (int) ($totalMembers ?? 0);
    $activeMemberships  = (int) ($activeMemberships ?? 0);
    $todayCheckins      = (int) ($todayCheckins ?? 0);
    $totalProducts      = (int) ($totalProducts ?? 0);
    $izinPending        = (int) ($izinPending ?? 0);

    $recentIzin         = $recentIzin ?? collect();
    $latestCheckins     = $latestCheckins ?? collect(); // disarankan dipass dari controller

    $activeRate = (int) ($activeRate ?? (
        $totalMembers > 0 ? round(($activeMemberships / max($totalMembers, 1)) * 100) : 0
    ));

    $pendapatanProdukBulanIni = (int) ($pendapatanProdukBulanIni ?? 0);
    $produkTerlaris           = $produkTerlaris ?? null;
    $membershipTrxThisMonth   = (int) ($membershipTrxThisMonth ?? 0);

    // Payload untuk React
    $dashboardStatsPayload = $dashboardStatsPayload ?? [];
@endphp

<x-layouts.admin pageTitle="Dashboard" pageSubtitle="Pusat kontrol & ringkasan operasional BETA GYM.">

    {{-- React entry --}}
    @vite('resources/js/admin/dashboard.jsx')

    @once
        <style>
            .dash-no-x { max-width: 100%; overflow-x: hidden; }
            .stat-number { font-variant-numeric: tabular-nums; letter-spacing: -0.02em; }

            /* Reveal */
            .reveal {
                opacity: 0;
                transform: translateY(12px);
                transition: opacity 600ms cubic-bezier(0.2, 0.8, 0.2, 1),
                            transform 600ms cubic-bezier(0.2, 0.8, 0.2, 1);
                will-change: opacity, transform;
            }
            .reveal.is-visible { opacity: 1; transform: translateY(0); }

            /* Progress */
            .prog {
                width: 0%;
                transition: width 1000ms cubic-bezier(0.2, 0.8, 0.2, 1);
                will-change: width;
            }

            /* Scrollbar */
            .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.02); }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.22); }

            /* Tabs */
            .tab-btn.active {
                background-color: rgba(250, 204, 21, 0.12);
                color: rgb(161, 98, 7);
                border-color: rgb(234, 179, 8);
                font-weight: 700;
            }

            /* =========================================================
               MODAL FIX UTAMA: root modal juga harus non-interaktif saat hidden
               ========================================================= */
            .modal {
                opacity: 1;
                visibility: visible;
                pointer-events: auto; /* modal aktif bisa klik */
                transition: opacity 0.18s ease-out, visibility 0.18s ease-out;
            }
            .modal.modal-hidden {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;  /* INI YANG FIX: tidak menutup klik halaman */
            }

            .modal-backdrop:hover { cursor: pointer; }

            .modal-backdrop { transition: opacity 0.28s ease-out; opacity: 1; }
            .modal-content  { transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.28s ease-out; opacity: 1; transform: scale(1) translateY(0); }

            .modal.modal-hidden .modal-backdrop { opacity: 0; }
            .modal.modal-hidden .modal-content  { opacity: 0; transform: scale(0.96) translateY(10px); }

            @media (prefers-reduced-motion: reduce) {
                .reveal, .prog, .modal, .modal-backdrop, .modal-content { transition: none !important; }
            }
        </style>
    @endonce

    <div class="dash-no-x pb-20">

        {{-- =========================================================
           SECTION 1: HERO SUMMARY
           ========================================================= --}}
        <div class="mb-8">
            <x-ui.card class="relative overflow-hidden border border-brand-borderSoft/70 bg-brand-shell shadow-sm">

                {{-- Background: tambahkan pointer-events-none agar tidak mengganggu klik --}}
                <div class="absolute inset-0 pointer-events-none">
                    <div class="w-full h-full"
                         style="background-image:url('{{ asset('images/dashboard-hero.jpg') }}'); background-size:cover; background-position:center;">
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/55 to-black/10"></div>
                </div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    <div class="min-w-0">
                        <p class="text-xs font-semibold tracking-wide text-gold-300 uppercase">
                            Selamat datang di BETA GYM
                        </p>

                        <h1 class="mt-1 text-3xl md:text-4xl font-extrabold text-brand-shell drop-shadow-md">
                            Dashboard Manajemen Gym
                        </h1>

                        <p class="mt-2 text-sm text-brand-shell/90 max-w-xl">
                            Pantau aktivitas harian, kelola member dan produk, proses izin latihan,
                            serta akses laporan operasional dalam satu tampilan.
                        </p>

                        <div class="mt-4 inline-flex flex-wrap items-center gap-2 text-[11px] text-brand-shell/80">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-black/40 border border-brand-borderSoft/60">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                {{ now()->translatedFormat('l, d F Y') }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-black/30 border border-brand-borderSoft/40">
                                <i data-lucide="activity" class="w-3 h-3"></i>
                                Sistem berjalan normal
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:w-[380px] w-full">
                        <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm reveal js-reveal">
                            <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">Member Terdaftar</p>
                            <p class="mt-1 text-2xl font-bold text-brand-shell stat-number js-count" data-count="{{ $totalMembers }}">0</p>
                            <div class="mt-1 flex items-center gap-1 text-[11px] text-brand-shell/80">
                                <i data-lucide="users" class="w-3 h-3"></i>
                                <span>Semua member di sistem</span>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm reveal js-reveal">
                            <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">Membership Aktif</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-300 stat-number js-count" data-count="{{ $activeMemberships }}">0</p>
                            <div class="mt-1 flex items-center justify-between text-[11px] text-brand-shell/80">
                                <span><span class="js-count" data-count="{{ $activeRate }}">0</span>% aktif</span>
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="badge-check" class="w-3 h-3"></i> aktif
                                </span>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm reveal js-reveal">
                            <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">Check-in Hari Ini</p>
                            <p class="mt-1 text-2xl font-bold text-gold-300 stat-number js-count" data-count="{{ $todayCheckins }}">0</p>
                            <div class="mt-1 flex items-center gap-1 text-[11px] text-brand-shell/80">
                                <i data-lucide="clock-4" class="w-3 h-3"></i>
                                <span>Kehadiran hari ini</span>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm reveal js-reveal">
                            <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">Izin Pending</p>
                            <p class="mt-1 text-2xl font-bold {{ $izinPending > 0 ? 'text-amber-300' : 'text-brand-shell' }} stat-number js-count"
                               data-count="{{ $izinPending }}">0</p>
                            <div class="mt-1 flex items-center justify-between text-[11px] text-brand-shell/80">
                                <span>Menunggu proses</span>
                                @if ($izinPending > 0)
                                    <a href="{{ route('admin.izin_latihan.index') }}"
                                       class="inline-flex items-center gap-1 hover:text-gold-200 transition-colors">
                                        <span>Proses</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </x-ui.card>
        </div>

        {{-- =========================================================
           SECTION 2: TOOLBAR (Tabs + Actions)
           ========================================================= --}}
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6 reveal js-reveal">
            <div class="inline-flex bg-white border border-brand-borderSoft rounded-lg p-1 shadow-sm">
                <button type="button" class="tab-btn active px-4 py-1.5 text-xs font-medium rounded-md text-text-muted hover:text-text-main transition-all"
                        data-tab="overview" id="tab-overview">
                    Overview
                </button>
                <button type="button" class="tab-btn px-4 py-1.5 text-xs font-medium rounded-md text-text-muted hover:text-text-main transition-all"
                        data-tab="business" id="tab-business">
                    Bisnis & Produk
                </button>
                <button type="button" class="tab-btn px-4 py-1.5 text-xs font-medium rounded-md text-text-muted hover:text-text-main transition-all"
                        data-tab="members" id="tab-members">
                    Member & Izin
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        class="js-modal-open inline-flex items-center gap-2 px-3 py-2 bg-white border border-brand-borderSoft rounded-lg text-xs font-medium text-text-main hover:bg-gray-50 transition shadow-sm"
                        data-modal="modal-export">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-text-muted"></i>
                    Export Laporan
                </button>

                <a href="{{ route('admin.members.create') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 bg-gold-500 hover:bg-gold-600 border border-gold-600 rounded-lg text-xs font-bold text-brand-shell transition shadow-md shadow-gold-500/20">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Member Baru
                </a>
            </div>
        </div>

        {{-- =========================================================
           SECTION 3: CONTENT TABS
           ========================================================= --}}

        {{-- TAB: OVERVIEW --}}
        <div id="content-overview" class="tab-content block space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 reveal js-reveal">
                    <div id="admin-dashboard-chart"
                         data-payload='@json($dashboardStatsPayload)'
                         class="h-full min-h-[320px]">
                    </div>
                </div>

                <div class="reveal js-reveal">
                    <x-ui.card class="h-full border-brand-borderSoft/80 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-widest text-text-muted">Performa Bulan Ini</p>
                                    <h2 class="text-base font-bold text-text-main">Ringkasan Omzet</h2>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-gold-50 border border-gold-100 flex items-center justify-center text-gold-600">
                                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="p-4 rounded-xl border border-brand-borderSoft/50 bg-gray-50/50">
                                    <p class="text-[10px] text-text-muted mb-1">Pendapatan Produk</p>
                                    <p class="text-xl font-bold text-text-main stat-number">
                                        Rp <span class="js-count" data-count="{{ $pendapatanProdukBulanIni }}">0</span>
                                    </p>
                                </div>

                                <div class="p-4 rounded-xl border border-brand-borderSoft/50 bg-gray-50/50">
                                    <p class="text-[10px] text-text-muted mb-1">Transaksi Membership</p>
                                    <div class="flex items-end justify-between">
                                        <p class="text-xl font-bold text-text-main stat-number js-count" data-count="{{ $membershipTrxThisMonth }}">0</p>
                                        <span class="text-[10px] text-text-muted">transaksi</span>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl border border-brand-borderSoft/50 bg-gray-50/50">
                                    <p class="text-[10px] text-text-muted mb-2">Rasio Member Aktif</p>
                                    <div class="w-full h-2 rounded-full bg-black/5 overflow-hidden border border-brand-borderSoft/40">
                                        <div class="h-full bg-emerald-500 prog js-progress" data-progress="{{ min($activeRate, 100) }}"></div>
                                    </div>
                                    <p class="mt-2 text-[11px] text-text-muted">
                                        <span class="font-bold text-text-main">{{ $activeRate }}%</span> dari total member.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-brand-borderSoft/50">
                            <p class="text-[10px] font-semibold text-text-muted uppercase mb-3">Produk Terlaris</p>
                            @if($produkTerlaris)
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-xs">
                                        #1
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-text-main truncate">{{ $produkTerlaris->nama }}</p>
                                        <p class="text-xs text-text-muted">{{ (int)$produkTerlaris->terjual }} terjual bulan ini</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-xs text-text-muted italic">Belum ada data penjualan.</p>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 reveal js-reveal">
                {{-- TABLE: Aktivitas Terkini --}}
                <x-ui.card class="border-brand-borderSoft/80">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
                            <i data-lucide="history" class="w-4 h-4 text-gold-500"></i>
                            Aktivitas Terkini
                        </h3>
                        <a href="{{ route('admin.absensi.index') }}"
                           class="text-[10px] font-semibold text-gold-600 hover:underline">Lihat Semua</a>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="py-2 text-[10px] font-semibold text-text-muted uppercase">Waktu</th>
                                    <th class="py-2 text-[10px] font-semibold text-text-muted uppercase">Member</th>
                                    <th class="py-2 text-[10px] font-semibold text-text-muted uppercase text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs">
                                @forelse($latestCheckins as $chk)
                                    @php
                                        $name = $chk->member?->user?->name
                                            ?? $chk->member?->nama
                                            ?? ('Member #' . ($chk->member_id ?? '-'));

                                        $time = $chk->created_at
                                            ? \Carbon\Carbon::parse($chk->created_at)->format('H:i')
                                            : '-';
                                    @endphp
                                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3 text-text-muted font-mono">{{ $time }}</td>
                                        <td class="py-3 font-medium text-text-main">{{ $name }}</td>
                                        <td class="py-3 text-right">
                                            @if((bool)($chk->is_valid ?? false))
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-bold border border-emerald-100">Valid</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-bold border border-rose-100">Invalid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-text-muted italic">
                                            Belum ada data check-in.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                {{-- TABLE: Izin Pending Terbaru --}}
                <x-ui.card class="border-brand-borderSoft/80">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-blue-500"></i>
                            Izin Pending Terbaru
                        </h3>
                        <a href="{{ route('admin.izin_latihan.index') }}"
                           class="text-[10px] font-semibold text-gold-600 hover:underline">Kelola</a>
                    </div>

                    @php
                        $izinPendingList = $recentIzin
                            ->filter(fn($x) => ($x->status ?? null) === 'pending')
                            ->take(5);
                    @endphp

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="py-2 text-[10px] font-semibold text-text-muted uppercase">Member</th>
                                    <th class="py-2 text-[10px] font-semibold text-text-muted uppercase text-center">Durasi</th>
                                    <th class="py-2 text-[10px] font-semibold text-text-muted uppercase text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs">
                                @forelse($izinPendingList as $izin)
                                    @php
                                        $memberName = $izin->member?->user?->name
                                            ?? $izin->member?->nama
                                            ?? '[Member Dihapus]';

                                        $tgl = $izin->tanggal_mulai
                                            ? \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y')
                                            : '-';
                                    @endphp

                                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3">
                                            <p class="font-medium text-text-main">{{ $memberName }}</p>
                                            <p class="text-[10px] text-text-muted">{{ $tgl }}</p>
                                        </td>
                                        <td class="py-3 text-center text-text-main font-semibold">
                                            {{ (int)($izin->jumlah_hari ?? 0) }} Hari
                                        </td>
                                        <td class="py-3 text-right">
                                            <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                                               class="inline-block px-2 py-1 rounded border border-gray-200 bg-white text-text-main hover:border-gold-400 hover:text-gold-600 transition text-[10px]">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-text-muted italic">Tidak ada izin pending saat ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>

        </div>

        {{-- TAB: BUSINESS --}}
        <div id="content-business" class="tab-content hidden space-y-6">
            <x-ui.card>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="bar-chart-2" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-text-main">Analitik Bisnis Lengkap</h3>
                    <p class="text-sm text-text-muted max-w-md mx-auto mt-2">
                        Fitur ini menampilkan grafik penjualan bulanan, breakdown kategori produk terlaris, dan arus kas harian.
                    </p>
                    <button type="button" class="js-tab text-xs font-semibold text-gold-600 hover:underline mt-4" data-tab="overview">
                        Kembali ke Overview
                    </button>
                </div>
            </x-ui.card>
        </div>

        {{-- TAB: MEMBERS --}}
        <div id="content-members" class="tab-content hidden space-y-6">
            <x-ui.card>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="users" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-text-main">Manajemen Member Cepat</h3>
                    <p class="text-sm text-text-muted max-w-md mx-auto mt-2">
                        Daftar member yang akan habis masa aktifnya dalam 7 hari ke depan akan muncul di sini.
                    </p>
                    <button type="button" class="js-tab text-xs font-semibold text-gold-600 hover:underline mt-4" data-tab="overview">
                        Kembali ke Overview
                    </button>
                </div>
            </x-ui.card>
        </div>

    </div>

    {{-- =========================================================
       MODAL: EXPORT (ROOT MODAL pakai class "modal")
       ========================================================= --}}
    <div id="modal-export" class="modal modal-hidden fixed inset-0 z-50 flex items-center justify-center p-4" aria-hidden="true">
        <div class="modal-backdrop absolute inset-0 bg-black/40 backdrop-blur-sm js-modal-close" data-modal="modal-export"></div>

        <div class="modal-content relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-text-main">Export Laporan</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 js-modal-close" data-modal="modal-export">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="#" method="GET">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-text-muted uppercase mb-1">Jenis Laporan</label>
                        <select class="w-full text-sm border-gray-300 rounded-lg focus:ring-gold-500 focus:border-gold-500">
                            <option value="daily">Laporan Harian</option>
                            <option value="monthly">Laporan Bulanan</option>
                            <option value="members">Data Member Aktif</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text-muted uppercase mb-1">Rentang Tanggal</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" class="text-sm border-gray-300 rounded-lg"
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            <input type="date" class="text-sm border-gray-300 rounded-lg"
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" class="flex-1 px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 js-modal-close"
                            data-modal="modal-export">
                        Batal
                    </button>
                    <button type="button" class="flex-1 px-4 py-2 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-bold shadow-md shadow-gold-500/20">
                        Download
                    </button>
                </div>
            </form>
        </div>
    </div>

    @once
        <script>
            (function () {
                const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                const formatID = (n) => {
                    try { return new Intl.NumberFormat('id-ID').format(n); }
                    catch (e) { return String(n); }
                };

                const animateCount = (el, to, duration = 1200) => {
                    if (prefersReduced) { el.textContent = formatID(to); return; }
                    const start = performance.now();
                    const from = 0;

                    const tick = (now) => {
                        const p = Math.min((now - start) / duration, 1);
                        const ease = 1 - Math.pow(1 - p, 3);
                        el.textContent = formatID(Math.round(from + (to - from) * ease));
                        if (p < 1) requestAnimationFrame(tick);
                    };

                    requestAnimationFrame(tick);
                };

                const setProgress = (scope) => {
                    (scope || document).querySelectorAll('.js-progress[data-progress]').forEach(el => {
                        if (el.dataset.done === '1') return;
                        const w = parseInt(el.dataset.progress || '0', 10);
                        el.style.width = (isNaN(w) ? 0 : Math.max(0, Math.min(100, w))) + '%';
                        el.dataset.done = '1';
                    });
                };

                const runCounters = (scope) => {
                    (scope || document).querySelectorAll('.js-count[data-count]').forEach(el => {
                        if (el.dataset.done === '1') return;
                        const to = parseInt(el.dataset.count || '0', 10);
                        el.textContent = prefersReduced ? formatID(to) : '0';
                        animateCount(el, isNaN(to) ? 0 : to);
                        el.dataset.done = '1';
                    });
                };

                const revealInit = () => {
                    const targets = document.querySelectorAll('.js-reveal');
                    if (!targets.length) return;

                    const activate = (el) => {
                        el.classList.add('is-visible');
                        runCounters(el);
                        setProgress(el);
                    };

                    if (!('IntersectionObserver' in window)) {
                        targets.forEach(activate);
                        return;
                    }

                    const io = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                activate(entry.target);
                                io.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });

                    targets.forEach(el => io.observe(el));
                };

                const switchTab = (tabId) => {
                    document.querySelectorAll('.tab-content').forEach(el => {
                        el.classList.add('hidden');
                        el.classList.remove('block');
                    });

                    const target = document.getElementById('content-' + tabId);
                    if (target) {
                        target.classList.remove('hidden');
                        target.classList.add('block');
                    }

                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    const activeBtn = document.getElementById('tab-' + tabId);
                    if (activeBtn) activeBtn.classList.add('active');
                };

                const toggleModal = (modalId, open = null) => {
                    const modal = document.getElementById(modalId);
                    if (!modal) return;

                    const willOpen = open === null ? modal.classList.contains('modal-hidden') : open;

                    if (willOpen) {
                        modal.classList.remove('modal-hidden');
                        modal.setAttribute('aria-hidden', 'false');
                        document.documentElement.classList.add('overflow-hidden');
                        document.body.classList.add('overflow-hidden');
                    } else {
                        modal.classList.add('modal-hidden');
                        modal.setAttribute('aria-hidden', 'true');
                        document.documentElement.classList.remove('overflow-hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                };

                document.addEventListener('DOMContentLoaded', () => {
                    revealInit();

                    document.addEventListener('click', (e) => {
                        const tabBtn = e.target.closest('[data-tab]');
                        if (tabBtn && (tabBtn.classList.contains('tab-btn') || tabBtn.classList.contains('js-tab'))) {
                            const tab = tabBtn.getAttribute('data-tab');
                            if (tab) switchTab(tab);
                        }

                        const openBtn = e.target.closest('.js-modal-open[data-modal]');
                        if (openBtn) {
                            toggleModal(openBtn.getAttribute('data-modal'), true);
                        }

                        const closeBtn = e.target.closest('.js-modal-close[data-modal]');
                        if (closeBtn) {
                            toggleModal(closeBtn.getAttribute('data-modal'), false);
                        }
                    });

                    // ESC to close modal
                    document.addEventListener('keydown', (e) => {
                        if (e.key !== 'Escape') return;
                        const opened = document.querySelector('.modal:not(.modal-hidden)');
                        if (opened && opened.id) toggleModal(opened.id, false);
                    });

                    // fallback
                    runCounters(document);
                    setProgress(document);
                });
            })();
        </script>
    @endonce

</x-layouts.admin>

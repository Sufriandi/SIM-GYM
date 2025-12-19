{{-- resources/views/admin/dashboard.blade.php --}}

@php
    // Fallback kalau controller belum kirim data
    $totalMembers = $totalMembers ?? 0;
    $activeMemberships = $activeMemberships ?? 0;
    $todayCheckins = $todayCheckins ?? 0;
    $totalProducts = $totalProducts ?? 0;
    $izinPending = $izinPending ?? 0;
    $recentIzin = $recentIzin ?? collect();

    $activeRate = $totalMembers > 0 ? round(($activeMemberships / max($totalMembers, 1)) * 100) : 0;
@endphp

<x-layouts.admin pageTitle="Dashboard" pageSubtitle="Ringkasan cepat aktivitas dan manajemen BETA GYM.">
    {{-- ====== HERO SUMMARY ====== --}}
    <div class="mb-8">
        <x-ui.card class="relative overflow-hidden border border-brand-borderSoft/70 bg-brand-shell">

            {{-- BACKGROUND IMAGE + OVERLAY GELAP --}}
            <div class="absolute inset-0">
                {{-- gambar --}}
                <div class="w-full h-full"
                    style="
                        background-image: url('{{ asset('images/dashboard-hero.jpg') }}');
                        background-size: cover;
                        background-position: center;
                    ">
                </div>

                {{-- overlay supaya teks kebaca --}}
                <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/55 to-black/10"></div>
            </div>

            {{-- KONTEN DASHBOARD (TEKS + KOTAK ANGKA) --}}
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
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
                        <span
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-black/40 border border-brand-borderSoft/60">
                            <i data-lucide="calendar" class="w-3 h-3"></i>
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-black/30 border border-brand-borderSoft/40">
                            <i data-lucide="activity" class="w-3 h-3"></i>
                            Sistem berjalan normal
                        </span>
                    </div>
                </div>

                {{-- Highlight angka ringkas --}}
                <div class="grid grid-cols-2 gap-3 lg:w-[350px]">
                    <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm">
                        <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">
                            Member Terdaftar
                        </p>
                        <p class="mt-1 text-2xl font-bold text-brand-shell">
                            {{ number_format($totalMembers) }}
                        </p>
                        <div class="mt-1 flex items-center gap-1 text-[11px] text-brand-shell/80">
                            <i data-lucide="users" class="w-3 h-3"></i>
                            <span>Semua member di sistem</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm">
                        <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">
                            Membership Aktif
                        </p>
                        <p class="mt-1 text-2xl font-bold text-emerald-300">
                            {{ number_format($activeMemberships) }}
                        </p>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-brand-shell/80">
                            <span>{{ $activeRate }}% aktif</span>
                            <span class="inline-flex items-center gap-1">
                                <i data-lucide="badge-check" class="w-3 h-3"></i>
                                aktif
                            </span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm">
                        <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">
                            Check-in Hari Ini
                        </p>
                        <p class="mt-1 text-2xl font-bold text-gold-300">
                            {{ number_format($todayCheckins) }}
                        </p>
                        <div class="mt-1 flex items-center gap-1 text-[11px] text-brand-shell/80">
                            <i data-lucide="clock-4" class="w-3 h-3"></i>
                            <span>Kehadiran member hari ini</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-black/35 border border-brand-borderSoft/60 px-4 py-3 backdrop-blur-sm">
                        <p class="text-[11px] text-brand-shell/80 uppercase tracking-wide font-semibold">
                            Izin Pending
                        </p>
                        <p
                            class="mt-1 text-2xl font-bold {{ $izinPending > 0 ? 'text-amber-300' : 'text-brand-shell' }}">
                            {{ $izinPending }}
                        </p>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-brand-shell/80">
                            <span>Izin menunggu proses</span>
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

    {{-- ====== RINGKASAN MANAJEMEN (MEMBER / PRODUK / OPERASIONAL) ====== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Blok Member & Membership --}}
        <x-ui.card class="border-brand-borderSoft/80">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                        Member & Membership
                    </p>
                    <h2 class="text-lg font-semibold text-text-main">
                        Manajemen Member
                    </h2>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-brand-shell flex items-center justify-center text-gold-600">
                    <i data-lucide="id-card" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Total Member</span>
                    <span class="font-semibold text-text-main">
                        {{ number_format($totalMembers) }}
                    </span>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted">Membership Aktif</span>
                        <span class="font-semibold text-success">
                            {{ number_format($activeMemberships) }} ({{ $activeRate }}%)
                        </span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-brand-shell overflow-hidden">
                        <div class="h-full bg-emerald-500" style="width: {{ min($activeRate, 100) }}%;"></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 text-xs">
                <a href="{{ route('admin.members.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl bg-brand-shell hover:bg-brand-surface-50 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="users" class="w-4 h-4 text-gold-600"></i>
                        Kelola Data Member
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                {{-- Penjualan Membership (bukan penjualan produk) --}}
                <a href="{{ route('admin.transaksi_membership.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="credit-card" class="w-4 h-4 text-gold-600"></i>
                        Penjualan Membership
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>
            </div>
        </x-ui.card>

        {{-- Blok Produk, Stok & Inventaris --}}
        <x-ui.card class="border-brand-borderSoft/80">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                        Produk & Inventaris
                    </p>
                    <h2 class="text-lg font-semibold text-text-main">
                        Penjualan & Fasilitas
                    </h2>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-brand-shell flex items-center justify-center text-gold-600">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Produk Tersedia</span>
                    <span class="font-semibold text-text-main">
                        {{ number_format($totalProducts) }}
                    </span>
                </div>

                <p class="text-[11px] text-text-muted">
                    Kelola produk (minuman, suplemen, dll), pantau stok, dan catat inventaris
                    alat gym agar operasional tetap tertata.
                </p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 text-xs">
                <a href="{{ route('admin.produk.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl bg-brand-shell hover:bg-brand-surface-50 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="package" class="w-4 h-4 text-gold-600"></i>
                        Kelola Produk
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                <a href="{{ route('admin.stok_produk.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="boxes" class="w-4 h-4 text-gold-600"></i>
                        Stok & Penyesuaian
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                {{-- Transaksi Produk (pengganti penjualan produk) --}}
                <a href="{{ route('admin.transaksi_produk.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-gold-600"></i>
                        Transaksi Produk
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                <a href="{{ route('admin.inventaris.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="dumbbell" class="w-4 h-4 text-gold-600"></i>
                        Inventaris Alat Gym
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>
            </div>
        </x-ui.card>

        {{-- Blok Operasional: Izin, Absensi, Laporan --}}
        <x-ui.card class="border-brand-borderSoft/80">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                        Operasional Harian
                    </p>
                    <h2 class="text-lg font-semibold text-text-main">
                        Izin, Absensi & Laporan
                    </h2>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-brand-shell flex items-center justify-center text-gold-600">
                    <i data-lucide="line-chart" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Izin Pending</span>
                    <span class="font-semibold {{ $izinPending > 0 ? 'text-amber-600' : 'text-text-main' }}">
                        {{ $izinPending }} izin
                    </span>
                </div>

                <p class="text-[11px] text-text-muted">
                    Gunakan modul ini untuk memastikan kehadiran tertib, pengajuan izin
                    terdokumentasi, dan laporan operasional siap kapan saja.
                </p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 text-xs">
                <a href="{{ route('admin.izin_latihan.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl bg-brand-shell hover:bg-brand-surface-50 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-gold-600"></i>
                        Proses Izin Latihan
                    </span>
                    @if ($izinPending > 0)
                        <span
                            class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500 text-white">
                            {{ $izinPending }} pending
                        </span>
                    @else
                        <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                    @endif
                </a>

                <a href="{{ route('admin.absensi.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-gold-600"></i>
                        Kelola Absensi / Check-in
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                <a href="#"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-gold-600"></i>
                        Laporan & Statistik
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>
            </div>
        </x-ui.card>
    </div>

    {{-- ====== BAWAH: IZIN TERBARU + AKTIVITAS & COACH ====== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-4">
        {{-- Izin Latihan Terbaru --}}
        <x-ui.card class="xl:col-span-2 border-brand-borderSoft/80">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-text-main">
                        Izin Latihan Terbaru
                    </h2>
                    <p class="text-xs text-text-muted">
                        Monitoring cepat pengajuan izin terakhir dari member.
                    </p>
                </div>

                <a href="{{ route('admin.izin_latihan.index') }}"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-gold-600 hover:text-gold-500 transition-colors">
                    Lihat semua
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            @if ($recentIzin->isEmpty())
                <p class="py-6 text-center text-sm text-text-muted italic">
                    Belum ada pengajuan izin terbaru.
                </p>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full border-collapse text-xs md:text-sm">
                        <thead>
                            <tr class="border-b border-brand-borderSoft/70 bg-brand-shell/70">
                                <th
                                    class="px-3 py-2 text-left text-[11px] font-semibold tracking-wide uppercase text-text-main">
                                    Member
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">
                                    Durasi
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">
                                    Mulai
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">
                                    Status
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentIzin as $izin)
                                <tr
                                    class="border-b border-brand-borderSoft/60 last:border-0 hover:bg-brand-shell/40 transition-colors">
                                    <td class="px-3 py-2 text-text-main">
                                        {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                                    </td>
                                    <td class="px-3 py-2 text-center text-text-main">
                                        {{ $izin->jumlah_hari }} Hari
                                    </td>
                                    <td class="px-3 py-2 text-center text-text-muted">
                                        {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($izin->status === 'pending')
                                            <x-ui.badge variant="warning">Pending</x-ui.badge>
                                        @elseif($izin->status === 'disetujui')
                                            <x-ui.badge variant="success">Disetujui</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-gold-600 hover:text-gold-500 transition-colors">
                                            Detail
                                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>

        {{-- Aktivitas Cepat & Coach --}}
        <x-ui.card class="border-brand-borderSoft/80">
            <h2 class="text-lg font-semibold text-text-main mb-3">
                Aksi Cepat & Info Sistem
            </h2>

            <div class="space-y-3 text-sm mb-4">
                {{-- Tambah Member --}}
                <a href="{{ route('admin.members.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="user-plus" class="w-4 h-4 text-gold-600"></i>
                        Tambah / Kelola Member
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                {{-- Transaksi Produk --}}
                <a href="{{ route('admin.transaksi_produk.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-gold-600"></i>
                        Input Transaksi Produk
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                {{-- Penjualan Membership --}}
                <a href="{{ route('admin.transaksi_membership.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="credit-card" class="w-4 h-4 text-gold-600"></i>
                        Input Penjualan Membership
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                {{-- Coach --}}
                <a href="{{ route('admin.coaches.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="user-square-2" class="w-4 h-4 text-gold-600"></i>
                        Kelola Coach / Trainer
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                {{-- Absensi --}}
                <a href="{{ route('admin.absensi.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/60 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-gold-600"></i>
                        Rekap Kehadiran Hari Ini
                    </span>
                    <span class="text-[11px] text-text-muted">
                        {{ number_format($todayCheckins) }} check-in
                    </span>
                </a>
            </div>

            <x-ui.divider class="my-4" />

            <h3 class="text-xs font-semibold tracking-wide uppercase text-text-muted mb-2">
                Status Sistem
            </h3>
            <ul class="space-y-1 text-xs text-text-muted">
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                    Sistem berjalan normal, tidak ada gangguan terdeteksi.
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-gold-500"></span>
                    Rutin unduh laporan keuangan & kehadiran setiap akhir bulan.
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    Pastikan data member & membership selalu diperbarui.
                </li>
            </ul>
        </x-ui.card>
    </div>
</x-layouts.admin>

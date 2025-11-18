{{-- resources/views/admin/dashboard.blade.php --}}

@php
    // Fallback kalau controller belum kirim data
    $totalMembers      = $totalMembers      ?? 0;
    $activeMemberships = $activeMemberships ?? 0;
    $todayCheckins     = $todayCheckins     ?? 0;
    $totalProducts     = $totalProducts     ?? 0;
    $izinPending       = $izinPending       ?? 0;
    $recentIzin        = $recentIzin        ?? collect();
@endphp

<x-layouts.admin
    pageTitle="Dashboard"
    pageSubtitle="Ringkasan cepat aktivitas BETA GYM hari ini."
>
    {{-- HEADER SECTION --}}
    <div class="mb-8">
        <x-ui.section-header
            title="Dashboard"
            subtitle="Pantau statistik utama dan aktivitas terbaru BETA GYM."
        />
    </div>

    {{-- TOP STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        {{-- Total Member --}}
        <x-ui.card class="relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-text-muted uppercase">
                        Total Member
                    </p>
                    <p class="mt-1 text-2xl font-bold text-text-main">
                        {{ number_format($totalMembers) }}
                    </p>
                </div>

                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-brand-gunmetal/80 text-gold-300 shadow-gold-glow">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <p class="text-xs text-text-muted">
                Semua member terdaftar di sistem.
            </p>
        </x-ui.card>

        {{-- Membership Aktif --}}
        <x-ui.card class="relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-text-muted uppercase">
                        Membership Aktif
                    </p>
                    <p class="mt-1 text-2xl font-bold text-success">
                        {{ number_format($activeMemberships) }}
                    </p>
                </div>

                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-success-soft text-success shadow-card">
                    <i data-lucide="badge-check" class="w-5 h-5"></i>
                </div>
            </div>

            <p class="text-xs text-text-muted">
                Member yang masih dalam masa membership.
            </p>
        </x-ui.card>

        {{-- Izin Pending --}}
        <x-ui.card class="relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-text-muted uppercase">
                        Izin Pending
                    </p>
                    <p class="mt-1 text-2xl font-bold {{ $izinPending > 0 ? 'text-accent-500' : 'text-text-main' }}">
                        {{ $izinPending }}
                    </p>
                </div>

                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-accent-500/10 text-accent-500 shadow-card">
                    <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                </div>
            </div>

            <p class="text-xs text-text-muted">
                Izin latihan yang menunggu persetujuan Admin.
            </p>

            @if ($izinPending > 0)
                <a href="#"
                   class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-accent-500 hover:text-accent-400 transition-colors">
                    Proses sekarang
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            @endif
        </x-ui.card>

        {{-- Check-in Hari Ini / Produk --}}
        <x-ui.card class="relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-text-muted uppercase">
                        Check-in Hari Ini
                    </p>
                    <p class="mt-1 text-2xl font-bold text-text-main">
                        {{ number_format($todayCheckins) }}
                    </p>
                </div>

                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-brand-gunmetal/80 text-gold-300 shadow-gold-glow">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>

            <p class="text-xs text-text-muted">
                Member yang sudah melakukan kehadiran hari ini.
            </p>
        </x-ui.card>
    </div>

    {{-- MAIN GRID: RIWAYAT IZIN & AKTIVITAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- RIWAYAT IZIN TERBARU --}}
        <x-ui.card class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-text-main">
                        Izin Latihan Terbaru
                    </h2>
                    <p class="text-xs text-text-muted">
                        Beberapa pengajuan izin terakhir dari member.
                    </p>
                </div>

                <a href="{{ route('admin.izin_latihan.index') }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-gold-600 hover:text-gold-500 transition-colors">
                    Lihat semua
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            @if($recentIzin->isEmpty())
                <p class="py-6 text-center text-sm text-text-muted italic">
                    Belum ada pengajuan izin terbaru.
                </p>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-brand-borderSoft/70 bg-brand-shell/70">
                                <th class="px-3 py-2 text-left text-[11px] font-semibold tracking-wide uppercase text-text-main">Member</th>
                                <th class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">Durasi</th>
                                <th class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">Mulai</th>
                                <th class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">Status</th>
                                <th class="px-3 py-2 text-center text-[11px] font-semibold tracking-wide uppercase text-text-main">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentIzin as $izin)
                                <tr class="border-b border-brand-borderSoft/60 last:border-0 hover:bg-brand-shell/40 transition-colors">
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
                                        @if($izin->status === 'pending')
                                            <x-ui.badge variant="warning">Pending</x-ui.badge>
                                        @elseif($izin->status === 'disetujui')
                                            <x-ui.badge variant="success">Disetujui</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-gold-600 hover:text-gold-500 transition-colors">
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

        {{-- PANEL AKTIVITAS / QUICK LINKS --}}
        <x-ui.card>
            <h2 class="text-lg font-semibold text-text-main mb-3">
                Aksi Cepat
            </h2>

            <div class="space-y-3 text-sm">
                <a href="#"
                   class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/50 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="user-plus" class="w-4 h-4 text-gold-600"></i>
                        Tambah Member Baru
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                <a href="#"
                   class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/50 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="package-plus" class="w-4 h-4 text-gold-600"></i>
                        Kelola Produk / Paket
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>

                <a href="#"
                   class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/50 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-gold-600"></i>
                        Proses Izin Pending
                    </span>
                    @if($izinPending > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-accent-500 text-white">
                            {{ $izinPending }}
                        </span>
                    @else
                        <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                    @endif
                </a>

                <a href="#"
                   class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-brand-shell/50 transition-colors">
                    <span class="inline-flex items-center gap-2 text-text-main">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-gold-600"></i>
                        Laporan & Statistik
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-text-muted"></i>
                </a>
            </div>

            <x-ui.divider class="my-5" />

            <x-ui.section-subtitle class="mb-2">
                Info Sistem
            </x-ui.section-subtitle>

            <ul class="space-y-1 text-xs text-text-muted">
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                    Sistem berjalan normal.
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-gold-500"></span>
                    Pastikan rutin mengunduh laporan bulanan.
                </li>
            </ul>
        </x-ui.card>
    </div>
</x-layouts.admin>

@extends('layouts.admin')

@section('content')

<header class="mb-8">
    {{-- Font Heading Oswald dan warna Gold --}}
    <h1 class="text-4xl font-heading text-gold mb-1">ADMIN DASHBOARD</h1>
    <p class="text-text-secondary text-base">Selamat datang, {{ Auth::user()->name }}. Kelola performa BETA GYM hari ini.</p>
</header>

<hr class="border-t border-dark-surface mb-8">

<section class="mb-8">
    <h2 class="text-2xl font-heading text-white mb-6 flex items-center">
        <span class="mr-3 text-gold">📊</span> STATUS KUNCI
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        
        {{-- Card 1: Total Member --}}
        <div class="bg-dark-card rounded-gym p-5 text-center border-l-4 border-gold border border-dark-surface hover:border-gold-700 transition duration-300 group">
            <p class="text-text-secondary text-sm mb-1">Total Member Aktif</p>
            <h3 class="text-5xl text-white font-heading mt-0 mb-0">
                <span class="text-gold group-hover:animate-pulse-gold">{{ $totalMembers }}</span>
            </h3>
            <p class="text-text-secondary text-xs mt-1">Peningkatan 10% bulan ini</p>
        </div>

        {{-- Card 2: Permintaan Izin Latihan Baru --}}
        <div class="bg-dark-card rounded-gym p-5 text-center border-l-4 border-accent border border-dark-surface hover:border-accent-700 transition duration-300 group">
            <p class="text-text-secondary text-sm mb-1">Izin Latihan Baru</p>
            <h3 class="text-5xl text-white font-heading mt-0 mb-0">
                <span class="text-accent group-hover:animate-pulse">{{ $izinPending }}</span>
            </h3>
            <p class="text-text-secondary text-xs mt-1">Perlu diverifikasi segera</p>
        </div>

        {{-- Card 3: Produk Terlaris --}}
        <div class="bg-dark-card rounded-gym p-5 text-center border-l-4 border-gold-500 border border-dark-surface hover:border-gold-600 transition duration-300">
            <p class="text-text-secondary text-sm mb-1">Produk Terlaris</p>
            <h3 class="text-xl text-white font-heading mt-0 mb-0 h-10 flex items-center justify-center">
                <span class="text-gold">{{ $produkTerlaris->nama }}</span>
            </h3>
            <p class="text-text-secondary text-xs mt-1">{{ $produkTerlaris->terjual }} unit terjual</p>
        </div>
        
        {{-- Card 4: Pendapatan Bulan Ini --}}
        <div class="bg-dark-card rounded-gym p-5 text-center border-l-4 border-success border border-dark-surface hover:border-success transition duration-300">
            <p class="text-text-secondary text-sm mb-1">Pendapatan (Bulan Ini)</p>
            <h3 class="text-5xl text-white font-heading mt-0 mb-0">
                <span class="text-gold">Rp. {{ number_format($pendapatanBulanIni/1000000, 0) }} Jt</span>
            </h3>
            <p class="text-success text-xs mt-1 font-semibold">Target 90% tercapai</p>
        </div>
        
    </div>
</section>

<hr class="border-t border-dark-surface mb-8">

<section class="mb-8">
    <h2 class="text-2xl font-heading text-white mb-6 flex items-center">
        <span class="mr-3 text-accent">⚡</span> AKSI CEPAT
    </h2>
    
    <div class="flex flex-wrap gap-4">
        {{-- Tombol Premium (Gold) --}}
        <a href="#" class="px-6 py-3 bg-gold text-primary-900 font-bold uppercase rounded-gym hover:bg-gold-600 border-2 border-gold hover:border-gold-400 transition duration-300 transform hover:scale-105">
            + Tambah Member Baru
        </a>
        
        {{-- Tombol Aksen (Red) --}}
        <a href="#" class="px-6 py-3 bg-accent text-white font-bold uppercase rounded-gym hover:bg-accent-600 border-2 border-accent hover:border-accent-400 transition duration-300 transform hover:scale-105">
            Cek Izin Latihan ({{ $izinPending }} Pending)
        </a>
        
        {{-- Tombol Premium (Gold) --}}
        <a href="#" class="px-6 py-3 bg-gold text-primary-900 font-bold uppercase rounded-gym hover:bg-gold-600 border-2 border-gold hover:border-gold-400 transition duration-300 transform hover:scale-105">
            + Tambah Produk Baru
        </a>
        
        {{-- Tombol Info --}}
        <a href="#" class="px-6 py-3 bg-primary-600 text-text-primary font-bold uppercase rounded-gym hover:bg-primary-500 border-2 border-gold-700 hover:border-gold transition duration-300">
            📈 Lihat Laporan
        </a>
    </div>
</section>

<hr class="border-t border-dark-surface mb-8">

<section>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Tabel Izin Latihan --}}
        <div class="bg-dark-card rounded-premium p-6 lg:col-span-2 border-2 border-dark-surface hover:border-gold-800 transition duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-heading text-gold mt-0">PERMINTAAN IZIN LATIHAN TERBARU</h3>
                <span class="text-xs text-text-secondary bg-dark-surface px-3 py-1 rounded-full border border-gold-800">
                    {{ $izinPending }} Pending
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gold-800">
                            <th class="p-3 text-left text-gold font-heading font-normal uppercase text-sm">Member</th>
                            <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Tanggal</th>
                            <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Status</th>
                            <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestIzin as $izin)
                        <tr class="border-b border-dark-surface hover:bg-dark-surface/50 transition duration-150">
                            <td class="p-3 text-base text-text-primary">{{ $izin->member_nama }}</td>
                            <td class="p-3 text-center text-sm text-text-secondary">{{ $izin->tanggal }}</td>
                            <td class="p-3 text-center text-sm">
                                @if($izin->status == 'Pending')
                                    <span class="bg-accent/20 text-accent font-bold px-3 py-1 rounded-full text-xs uppercase">
                                        {{ $izin->status }}
                                    </span>
                                @else
                                    <span class="bg-success/20 text-success font-bold px-3 py-1 rounded-full text-xs uppercase">
                                        {{ $izin->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-center text-sm">
                                <a href="#" class="text-gold hover:text-gold-400 font-semibold transition duration-150 hover:underline">
                                    Lihat →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Log Aktivitas --}}
        <div class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface hover:border-gold-800 transition duration-300">
            <h3 class="text-xl font-heading text-gold mt-0 mb-4 flex items-center">
                <span class="mr-2">📋</span> LOG AKTIVITAS ADMIN
            </h3>
            
            <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                @foreach ($logAktivitas as $log)
                <div class="border-l-2 border-gold-800 pl-4 pb-4 hover:border-gold transition duration-200">
                    <small class="text-text-secondary block text-xs mb-1">
                        🕐 {{ $log->waktu }}
                    </small>
                    <p class="text-sm text-text-primary leading-relaxed">{!! $log->deskripsi !!}</p>
                </div>
                @endforeach
            </div>
        </div>
        
    </div>
</section>

{{-- Custom Scrollbar Styles --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #16213e;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #c8a870;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9a7a4a;
    }
</style>

@endsection
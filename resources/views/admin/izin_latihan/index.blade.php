@extends('layouts.admin')

@section('content')

<header class="mb-8">
    {{-- PERUBAHAN DI SINI: Kecilkan ke text-3xl dan tambahkan font-extrabold --}}
    <h1 class="text-2xl font-heading text-gold mb-1 font-extrabold">Manajemen Izin Latihan</h1>
    <p class="text-text-secondary text-base">Kelola semua permintaan izin dari member.</p>
</header>

<hr class="border-t border-dark-surface mb-8">

@if (session('success'))
    <div class="bg-success/20 text-success text-base font-semibold p-4 rounded-gym mb-6 border border-success/30">
        {{ session('success') }}
    </div>
@endif
@if (session('danger'))
    <div class="bg-danger/20 text-danger text-base font-semibold p-4 rounded-gym mb-6 border border-danger/30">
        {{ session('danger') }}
    </div>
@endif

<section>
    <div class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-heading text-gold mt-0">Semua Permintaan Izin</h3>
            {{-- Badge untuk total data --}}
            <span class="text-xs text-text-secondary bg-dark-surface px-3 py-1 rounded-full border border-gold-800">
                Total: {{ $daftar_izin->total() }} Izin
            </span>
        </div>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b-2 border-gold-800">
                        <th class="p-3 text-left text-gold font-heading font-normal uppercase text-sm">Member</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Tgl Mulai</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Tgl Selesai</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Durasi</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Status</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar_izin as $izin)
                    <tr class="border-b border-dark-surface hover:bg-dark-surface/50 transition duration-150">
                        
                        <td class="p-3 text-base text-text-primary">{{ $izin->member->name }}</td>
                        
                        <td class="p-3 text-center text-sm text-text-secondary">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}</td>
                        
                        <td class="p-3 text-center text-sm text-text-secondary">{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}</td>
                        
                        <td class="p-3 text-center text-sm text-text-secondary">{{ $izin->durasi }} Hari</td>
                        
                        <td class="p-3 text-center text-sm">
                            {{-- Status Badges Sesuai Tema --}}
                            @if($izin->status == 'pending')
                                <span class="bg-accent/20 text-accent font-bold px-3 py-1 rounded-full text-xs uppercase">
                                    Pending
                                </span>
                            @elseif($izin->status == 'disetujui')
                                <span class="bg-success/20 text-success font-bold px-3 py-1 rounded-full text-xs uppercase">
                                    Disetujui
                                </span>
                            @else
                                <span class="bg-primary-700/50 text-text-secondary font-bold px-3 py-1 rounded-full text-xs uppercase">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        
                        <td class="p-3 text-center text-sm">
                            <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}" class="text-gold hover:text-gold-400 font-semibold transition duration-150 hover:underline">
                                Lihat Detail →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-text-secondary">
                            Tidak ada data permintaan izin yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Links (Breeze/Tailwind default) --}}
        <div class="mt-6">
            {{ $daftar_izin->links() }}
        </div>
    </div>
</section>

{{-- Custom Scrollbar (dari dashboard Anda) --}}
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #16213e; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c8a870; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9a7a4a; }
</style>

@endsection
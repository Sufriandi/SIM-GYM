@extends('layouts.admin')

@section('content')

<header class="mb-8">
    <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle }}</h1>
    <p class="text-text-secondary text-base">Tentukan hari perpanjangan membership yang akan diberikan kepada member <span class="text-gold font-bold">{{ $izin->member?->nama ?? '[Dihapus]' }}</span>.</p>
</header>

<hr class="border-t border-dark-surface mb-8">

@if ($errors->any())
    <div class="bg-danger/20 text-danger text-sm font-semibold p-4 rounded-gym mb-6 border border-danger/30">
        <p>Mohon periksa input Anda:</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if (session('error'))
    <div class="bg-danger/20 text-danger text-base font-semibold p-4 rounded-gym mb-6 border border-danger/30">
        {{ session('error') }}
    </div>
@endif

{{-- FORM UTAMA --}}
<div class="bg-dark-card rounded-premium p-6 border-2 border-accent/50 shadow-accent max-w-4xl mx-auto">
    
    <h3 class="text-2xl font-heading text-accent mt-0 mb-6 border-b border-accent/30 pb-3 text-center">KONFIRMASI PERSETUJUAN IZIN</h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- KOLOM KIRI (50%): KONTEKS KEPUTUSAN INTRA-RINGKAS --}}
        <div class="lg:col-span-1 space-y-4 p-4 bg-dark-surface rounded-gym border border-gold-800">
            <h4 class="text-lg font-heading text-gold border-b border-gold-800/50 pb-2">KONTEKS KEPUTUSAN</h4>
            
            {{-- Hari Diajukan --}}
            <div class="flex justify-between items-center border-b border-dark-surface pb-2">
                <p class="text-sm font-semibold text-text-primary">Hari Diajukan:</p>
                {{-- 🚨 PERBAIKAN: Mengganti text-primary menjadi text-gold untuk kontras tinggi --}}
                <p class="text-xl text-gold font-bold">{{ $izin->jumlah_hari }} Hari</p> 
            </div>
            
            {{-- Akhir Membership Saat Ini --}}
            <div class="flex justify-between items-center border-b border-dark-surface pb-2">
                <p class="text-sm font-semibold text-text-primary">Akhir Membership Saat Ini:</p>
                <p class="text-lg text-gold font-bold">{{ $izin->member ? \Carbon\Carbon::parse($izin->member->tanggal_akhir)->format('d F Y') : '[Data Member Error]' }}</p>
            </div>

            {{-- Periode Izin --}}
            <div class="flex justify-between items-center border-b border-dark-surface pb-2">
                <p class="text-sm font-semibold text-text-primary">Periode Izin:</p>
                <p class="text-sm text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M') }} s/d {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d F Y') }}</p>
            </div>

            <p class="text-center pt-2">
                {{-- Tombol Referensi ke Detail Lengkap --}}
                <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}" class="text-accent hover:underline text-sm font-semibold">
                    Lihat Alasan & Bukti Lengkap →
                </a>
            </p>
            
            {{-- ALASAN PENGAJUAN MEMBER (Diperkecil) --}}
            <div class="p-2 bg-dark-card rounded-gym border border-dark-surface">
                <dt class="text-xs font-heading text-gold uppercase mb-1">Alasan Pengajuan</dt>
                <dd class="text-sm text-text-secondary whitespace-pre-wrap max-h-20 overflow-y-auto">
                    {{ $izin->alasan }}
                </dd>
            </div>
        </div>

        {{-- KOLOM KANAN (50%): FORMULIR AKSI (FOKUS UTAMA) --}}
        <div class="lg:col-span-1 space-y-5">
            
            {{-- FORMULIR AKSI --}}
            <form method="POST" action="{{ route('admin.izin_latihan.approve', $izin) }}" class="space-y-5">
                @csrf
                
                <h4 class="text-lg font-heading text-gold border-b border-gold-800/50 pb-2">PROSES PERSETUJUAN</h4>

                {{-- INPUT HARI YANG DISETUJUI --}}
                <div>
                    <label for="approved_days" class="block text-sm font-semibold text-text-primary mb-1">Jumlah Hari Perpanjangan Disetujui</label>
                    <input type="number" name="approved_days" id="approved_days" 
                           class="w-full bg-dark-card text-text-primary border border-gold-800 rounded-gym p-3 text-2xl focus:ring-gold focus:border-gold @error('approved_days') border-danger @enderror" 
                           required min="0" max="{{ $izin->jumlah_hari }}" 
                           value="{{ old('approved_days', $izin->jumlah_hari) }}">
                    <p class="text-xs text-text-secondary mt-1">Masukkan hari yang disetujui (0 - {{ $izin->jumlah_hari }} hari).</p>
                    @error('approved_days')
                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- CATATAN ADMIN --}}
                <div>
                    <label for="keterangan_admin" class="block text-sm font-semibold text-text-primary mb-1">Catatan Admin (Opsional)</label>
                    <textarea name="keterangan_admin" id="keterangan_admin" rows="3" class="w-full bg-dark-card text-text-primary border border-gold-800 rounded-gym p-3">{{ old('keterangan_admin') }}</textarea>
                </div>

                <button type="submit" 
                        class="w-full px-6 py-3 bg-success text-white font-bold uppercase rounded-gym hover:bg-success-600 border-2 border-success hover:border-success-400 transition duration-300 transform hover:scale-105">
                    PROSES PERSETUJUAN & PERPANJANG MEMBERSHIP
                </button>
            </form>
            
            <hr class="border-t border-dark-surface my-4">

            {{-- Tombol Kembali --}}
            <a href="{{ route('admin.izin_latihan.index') }}" 
                class="w-full block text-center px-6 py-3 bg-danger text-white font-bold uppercase rounded-gym hover:bg-danger-600 border-2 border-danger hover:border-danger-400 transition duration-300">
                KEMBALI KE DAFTAR PENDING
            </a>
        </div>
    </div>
</div>

@endsection
@extends('layouts.admin')

@section('content')

<header class="mb-8">
    <h1 class="text-4xl font-heading text-gold mb-1">Detail Izin Latihan</h1>
    <p class="text-text-secondary text-base">Tinjau dan lakukan persetujuan untuk member: <span class="text-gold font-semibold">{{ $izin->member->name }}</span></p>
    
    <div class="mt-4">
        <a href="{{ route('admin.izin_latihan.index') }}" 
           class="inline-flex items-center text-gold hover:text-gold-400 transition duration-200 font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Kembali ke Daftar Izin
        </a>
    </div>
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
@if (session('info'))
    <div class="bg-info/20 text-info text-base font-semibold p-4 rounded-gym mb-6 border border-info/30">
        {{ session('info') }}
    </div>
@endif

<section>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- KOLOM KIRI: DETAIL DATA IZIN --}}
        <div class="lg:col-span-2 bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark space-y-4">
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                {{-- Status --}}
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Status</dt>
                    <dd class="text-lg text-text-primary">
                        @if($izin->status == 'pending')
                            <span class="bg-accent/20 text-accent font-bold px-4 py-2 rounded-gym text-base uppercase">Pending</span>
                        @elseif($izin->status == 'disetujui')
                            <span class="bg-success/20 text-success font-bold px-4 py-2 rounded-gym text-base uppercase">Disetujui</span>
                        @else
                            <span class="bg-primary-700/50 text-text-secondary font-bold px-4 py-2 rounded-gym text-base uppercase">Ditolak</span>
                        @endif
                    </dd>
                </div>
                {{-- Tgl Mulai --}}
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Tanggal Mulai</dt>
                    <dd class="text-lg text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d F Y') }}</dd>
                </div>
                {{-- Tgl Selesai --}}
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Tanggal Selesai</dt>
                    <dd class="text-lg text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d F Y') }}</dd>
                </div>
                {{-- Durasi --}}
                <div class="col-span-2 md:col-span-1">
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Total Durasi Izin</dt>
                    <dd class="text-lg text-text-primary">{{ $izin->durasi }} Hari</dd>
                </div>
            </div>

            <hr class="border-t border-dark-surface my-4">

            {{-- Alasan --}}
            <div>
                <dt class="text-sm font-heading text-gold uppercase mb-2">Alasan Pengajuan</dt>
                <dd class="text-base text-text-secondary whitespace-pre-wrap p-4 bg-dark-surface rounded-gym border border-gold-800">
                    {{ $izin->alasan }}
                </dd>
            </div>
        </div>
        
        {{-- KOLOM KANAN: BUKTI & AKSI --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Card Bukti Alasan --}}
            <div class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark">
                <h3 class="text-xl font-heading text-gold mt-0 mb-4">Bukti Alasan</h3>
                @if ($izin->bukti_alasan)
                    <a href="{{ Storage::url($izin->bukti_alasan) }}" target="_blank">
                        <img src="{{ Storage::url($izin->bukti_alasan) }}" 
                             alt="Bukti Alasan Izin" 
                             class="w-full h-auto rounded-gym border-2 border-gold-800 hover:border-gold transition duration-200">
                    </a>
                    <a href="{{ Storage::url($izin->bukti_alasan) }}" 
                       target="_blank"
                       class="mt-4 block text-center w-full px-4 py-2 bg-primary-600 text-text-primary font-bold uppercase rounded-gym hover:bg-primary-500 border-2 border-gold-700 hover:border-gold transition duration-300">
                        Lihat Ukuran Penuh
                    </a>
                @else
                    <p class="text-text-secondary text-center italic py-4">Tidak ada bukti yang dilampirkan.</p>
                @endif
            </div>

            {{-- Card Aksi (Hanya jika 'pending') --}}
            @if ($izin->status == 'pending')
            <div class="bg-dark-card rounded-premium p-6 border-2 border-accent/50 shadow-accent">
                <h3 class="text-xl font-heading text-accent mt-0 mb-4">Tindakan Persetujuan</h3>
                <p class="text-text-secondary text-sm mb-4">Harap tinjau bukti dan alasan sebelum menyetujui atau menolak.</p>
                
                <div class="flex flex-col space-y-3">
                    {{-- Form Setujui (Warna Gold) --}}
                    <form method="POST" action="{{ route('admin.izin_latihan.approve', $izin->id) }}">
                        @csrf
                        <button type="submit" 
                                class="w-full px-6 py-3 bg-gold text-primary-900 font-bold uppercase rounded-gym hover:bg-gold-600 border-2 border-gold hover:border-gold-400 transition duration-300 transform hover:scale-105">
                            Setujui Izin
                        </button>
                    </form>
                    
                    {{-- Form Tolak (Warna Merah/Accent) --}}
                    <form method="POST" action="{{ route('admin.izin_latihan.reject', $izin->id) }}">
                        @csrf
                        <button type="submit" 
                                class="w-full px-6 py-3 bg-accent text-white font-bold uppercase rounded-gym hover:bg-accent-600 border-2 border-accent hover:border-accent-400 transition duration-300 transform hover:scale-105">
                            Tolak Izin
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</section>

@endsection
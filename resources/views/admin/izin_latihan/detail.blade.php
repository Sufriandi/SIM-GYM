@extends('layouts.admin')

@section('content')

<header class="mb-8">
    <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle }}</h1>

    <p class="text-text-secondary text-base">
        Lihat detail izin member:
        <span class="{{ $izin->member ? 'text-gold' : 'text-danger italic' }} font-semibold">
            {{ $izin->member?->nama ?? '[Member Dihapus]' }}
        </span>
    </p>

    <div class="mt-4">
        {{-- Link kembali disesuaikan berdasarkan status izin --}}
        <a href="{{ $izin->status == 'pending' ? route('admin.izin_latihan.index') : route('admin.izin_latihan.history') }}"
           class="inline-flex items-center text-gold hover:text-gold-400 transition duration-200 font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Kembali ke {{ $izin->status == 'pending' ? 'Permintaan Pending' : 'Riwayat Persetujuan' }}
        </a>
    </div>
</header>

<hr class="border-t border-dark-surface mb-8">

<section>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI: DETAIL DATA IZIN & MEMBERSHIP --}}
        <div class="lg:col-span-2 bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark space-y-4">

            <h2 class="text-2xl font-heading text-gold mb-4 border-b border-gold-800 pb-2">Data Pengajuan Izin</h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                {{-- Status --}}
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Status Izin</dt>
                    <dd class="text-lg text-text-primary">
                        @if($izin->status == 'pending')
                            <span class="bg-accent/20 text-accent font-bold px-4 py-2 rounded-gym text-base uppercase">Pending</span>
                        @elseif($izin->status == 'disetujui')
                            <span class="bg-success/20 text-success font-bold px-4 py-2 rounded-gym text-base uppercase">Disetujui</span>
                        @else
                            <span class="bg-danger/20 text-danger font-bold px-4 py-2 rounded-gym text-base uppercase">Ditolak</span>
                        @endif
                    </dd>
                </div>

                {{-- Durasi Permintaan --}}
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Durasi Permintaan</dt>
                    <dd class="text-lg text-text-primary">{{ $izin->jumlah_hari }} Hari</dd>
                </div>

                {{-- Durasi Disetujui (Hanya jika sudah diproses) --}}
                @if ($izin->status !== 'pending')
                    <div>
                        <dt class="text-sm font-heading text-gold uppercase mb-1">Durasi Disetujui</dt>
                        <dd class="text-lg {{ $izin->status === 'disetujui' ? 'text-success' : 'text-danger' }} font-bold">{{ $izin->durasi_izin_disetujui ?? 0 }} Hari</dd>
                    </div>
                @endif

                {{-- Tgl Mulai Izin --}}
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Tanggal Mulai Izin</dt>
                    <dd class="text-lg text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d F Y') }}</dd>
                </div>

                {{-- Tgl Selesai Izin --}}
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Tanggal Selesai Izin</dt>
                    <dd class="text-lg text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d F Y') }}</dd>
                </div>

                {{-- Tgl Persetujuan --}}
                @if ($izin->tanggal_persetujuan)
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Tanggal Diproses</dt>
                    <dd class="text-lg text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_persetujuan)->format('d F Y') }}</dd>
                </div>
                @endif
            </div>

            <hr class="border-t border-dark-surface my-6">

            @if ($izin->member)
                <h2 class="text-2xl font-heading text-gold mb-4 border-b border-gold-800 pb-2">Data Membership</h2>
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-1">Akhir Membership Member</dt>
                    <dd class="text-xl text-gold font-bold">{{ \Carbon\Carbon::parse($izin->member->tanggal_akhir)->format('d F Y') }}</dd>
                </div>
            @endif

            <hr class="border-t border-dark-surface my-6">

            {{-- Alasan Member --}}
            <div>
                <dt class="text-sm font-heading text-gold uppercase mb-2">Alasan Pengajuan Member</dt>
                <dd class="text-base text-text-secondary whitespace-pre-wrap p-4 bg-dark-surface rounded-gym border border-gold-800">
                    {{ $izin->alasan }}
                </dd>
            </div>

            {{-- Keterangan Admin (Jika Sudah Diproses) --}}
            @if ($izin->keterangan_admin)
                <div>
                    <dt class="text-sm font-heading text-gold uppercase mb-2">Catatan Admin</dt>
                    <dd class="text-base {{ $izin->status === 'disetujui' ? 'text-success/70' : 'text-danger/70' }} whitespace-pre-wrap p-4 bg-dark-surface rounded-gym border border-success/30">
                        {{ $izin->keterangan_admin }}
                    </dd>
                </div>
            @endif
        </div>

        {{-- KOLOM KANAN: BUKTI --}}
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

            {{-- Tidak ada form aksi di sini, hanya informasi status --}}
            @if ($izin->status === 'pending')
            <div class="bg-dark-card rounded-premium p-6 border-2 border-accent/50 shadow-accent">
                <h3 class="text-xl font-heading text-accent mt-0 mb-4">Menunggu Aksi</h3>
                <p class="text-text-secondary text-sm mb-4">Aksi persetujuan dilakukan di halaman **Permintaan Izin Baru**.</p>
                <a href="{{ route('admin.izin_latihan.approve.form', $izin) }}"
                    class="w-full mt-4 block text-center px-4 py-2 bg-gold text-primary-900 font-bold uppercase rounded-gym hover:bg-gold-600 transition">
                    Proses Persetujuan Sekarang
                </a>
            </div>
            @endif

        </div>
    </div>
</section>

@endsection

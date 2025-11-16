@extends('layouts.member')

@section('content')

<div class="max-w-6xl mx-auto">

    

    <header class="mb-8 md:flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle ?? 'Izin Sedang Diajukan' }}</h1>
            <p class="text-text-secondary text-base">Permintaan izin Anda yang masih menunggu diproses.</p>
        </div>

        <div class="mt-4 md:mt-0 space-y-2 md:space-y-0 md:space-x-4 flex flex-col md:flex-row items-end">
            {{-- Tombol Riwayat Pengajuan --}}
            <a href="{{ route('member.izin_latihan.history') }}"
               class="inline-flex items-center px-4 py-3 bg-dark-surface text-gold font-bold uppercase rounded-gym border border-gold-800 hover:bg-gold-800 hover:text-primary transition duration-300">
                Riwayat Pengajuan
            </a>
            {{-- Tombol Ajukan Baru --}}
            <a href="{{ route('member.izin_latihan.create') }}"
               class="inline-flex items-center px-6 py-3 bg-accent text-white font-bold uppercase rounded-gym hover:bg-accent-600 border-2 border-accent hover:border-accent-400 transition duration-300 transform hover:scale-105">
                + Ajukan Izin Baru
            </a>
        </div>
    </header>

    <hr class="border-t border-dark-surface mb-8">

    <section>
        <div class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark">

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse min-w-[700px]">
                    <thead>
                        <tr class="border-b-2 border-gold-800">
                            <th class="p-3 text-left text-gold font-heading font-normal uppercase text-sm">Tgl Mulai</th>
                            <th class="p-3 text-left text-gold font-heading font-normal uppercase text-sm">Tgl Selesai</th>
                            <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Durasi</th>
                            <th class="p-3 text-left text-gold font-heading font-normal uppercase text-sm">Alasan</th>
                            <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar_izin as $izin)
                        <tr class="border-b border-dark-surface hover:bg-dark-surface/50 transition duration-150">

                            <td class="p-3 text-base text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}</td>

                            <td class="p-3 text-base text-text-primary">{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}</td>

                            <td class="p-3 text-center text-sm text-text-secondary">{{ $izin->jumlah_hari }} Hari</td>

                            <td class="p-3 text-sm text-text-secondary">{{ Str::limit($izin->alasan, 40) }}</td>

                            <td class="p-3 text-center text-sm">
                                <span class="bg-accent/20 text-accent font-bold px-3 py-1 rounded-full text-xs uppercase">
                                    Pending
                                </span>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-text-secondary italic">
                                Tidak ada pengajuan izin yang sedang menunggu persetujuan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            <div class="mt-6">
                {{ $daftar_izin->links() }}
            </div>
        </div>
    </section>
</div>

@endsection
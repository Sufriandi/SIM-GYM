@extends('layouts.member')

@section('content')

<div class="max-w-6xl mx-auto">
    <header class="mb-8 md:flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle ?? 'Riwayat Pengajuan Izin Lengkap' }}</h1>
            <p class="text-text-secondary text-base">Semua riwayat pengajuan Anda, termasuk hasil keputusan Admin.</p>
        </div>

        <div class="mt-4 md:mt-0 space-x-4 flex items-end">
            {{-- Tombol Kembali ke Pending --}}
            <a href="{{ route('member.izin_latihan.index') }}"
               class="inline-flex items-center px-4 py-3 bg-dark-surface text-gold font-bold uppercase rounded-gym border border-gold-800 hover:bg-gold-800 hover:text-primary transition duration-300">
                Izin Pending ({{ \App\Models\IzinLatihan::where('user_id', Auth::id())->where('status', 'pending')->count() }})
            </a>
            {{-- Tombol Ajukan Baru --}}
            <a href="{{ route('member.izin_latihan.create') }}"
               class="inline-flex items-center px-6 py-3 bg-accent text-white font-bold uppercase rounded-gym hover:bg-accent-600 border-2 border-accent hover:border-accent-400 transition duration-300 transform hover:scale-105">
                + Ajukan Izin Baru
            </a>
        </div>
    </header>

    <hr class="border-t border-dark-surface mb-8">

    @if (session('success'))
        <div class="bg-success/20 text-success text-base font-semibold p-4 rounded-gym mb-6 border border-success/30">
            {{ session('success') }}
        </div>
    @endif

    <section>
        <div class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark">

            <div class="overflow-x-auto custom-scrollbar">
                {{-- 🚨 PERBAIKAN: Hapus min-w-[1000px] agar tabel menyesuaikan lebar layar --}}
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gold-800">
                            {{-- 🚨 PERBAIKAN: Padding horizontal (px-2) disesuaikan --}}
                            <th class="px-2 py-3 text-left text-gold font-heading font-normal uppercase text-sm">Tgl Pengajuan</th>
                            <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Diajukan (H)</th>
                            <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Disetujui (H)</th>
                            <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Status</th>
                            <th class="px-2 py-3 text-left text-gold font-heading font-normal uppercase text-sm">Ket. Admin</th>
                            <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar_izin as $izin)
                        <tr class="border-b border-dark-surface hover:bg-dark-surface/50 transition duration-150">

                            <td class="px-2 py-3 text-base text-text-primary">{{ \Carbon\Carbon::parse($izin->created_at)->format('d M Y') }}</td>

                            <td class="px-2 py-3 text-center text-sm text-text-secondary">{{ $izin->jumlah_hari }} Hari</td>

                            {{-- Disetujui (Hari) --}}
                            <td class="px-2 py-3 text-center text-sm font-bold">
                                @if($izin->status == 'disetujui')
                                    <span class="text-success">{{ $izin->durasi_izin_disetujui ?? 0 }} Hari</span>
                                @else
                                    <span class="text-danger">-</span>
                                @endif
                            </td>

                            <td class="px-2 py-3 text-center text-sm">
                                {{-- Status Badges --}}
                                @if($izin->status == 'pending')
                                    <span class="bg-accent/20 text-accent font-bold px-2 py-1 rounded-full text-xs uppercase">Pending</span>
                                @elseif($izin->status == 'disetujui')
                                    <span class="bg-success/20 text-success font-bold px-2 py-1 rounded-full text-xs uppercase">Disetujui</span>
                                @else
                                    <span class="bg-danger/20 text-danger font-bold px-2 py-1 rounded-full text-xs uppercase">Ditolak</span>
                                @endif
                            </td>

                            {{-- Keterangan Admin --}}
                            <td class="px-2 py-3 text-sm text-text-secondary">
                                @if ($izin->status != 'pending')
                                    {{ Str::limit($izin->keterangan_admin ?? 'Tidak ada keterangan.', 30) }}
                                @else
                                    Menunggu diproses
                                @endif
                            </td>
                            
                            {{-- Bukti --}}
                            <td class="px-2 py-3 text-center text-sm">
                                @if ($izin->bukti_alasan)
                                    <a href="{{ Storage::url($izin->bukti_alasan) }}" target="_blank" class="text-gold hover:underline">
                                        Lihat
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-text-secondary italic">
                                Anda belum memiliki riwayat pengajuan izin.
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
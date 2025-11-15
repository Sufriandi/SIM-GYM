@extends('layouts.admin')

@section('content')

<header class="mb-8 md:flex justify-between items-center">
    <div>
        <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle ?? 'Riwayat Pengajuan Izin Lengkap' }}</h1>
        <p class="text-text-secondary text-base">Daftar izin yang telah disetujui atau ditolak.</p>
    </div>
    <a href="{{ route('admin.izin_latihan.index') }}" class="px-4 py-2 bg-dark-surface text-gold font-bold uppercase rounded-gym border border-gold-800 hover:bg-gold-800 hover:text-primary transition duration-300">
        ← Kembali ke Permintaan Pending
    </a>
</header>

<hr class="border-t border-dark-surface mb-8">

{{-- 🚨 PERBAIKAN: Hapus blok notifikasi HTML lama agar SweetAlert2 yang global berfungsi. --}}
{{-- Blok @if (session('danger') || session('error')) sudah dihapus di sini --}}

@if (session('success'))
    {{-- Notifikasi success ini juga harusnya dihapus jika sudah ada di layout utama --}}
    {{-- Tapi kita biarkan kosong di sini untuk saat ini --}}
@endif

<section>
    <div class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark hover:border-gold-800 transition duration-300">

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b-2 border-gold-800">
                        <th class="px-2 py-3 text-left text-gold font-heading font-normal uppercase text-sm">Member</th>
                        <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Diajukan (H)</th>
                        <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Disetujui (H)</th>
                        <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Status</th>
                        <th class="px-2 py-3 text-left text-gold font-heading font-normal uppercase text-sm">Ket. Admin</th>
                        <th class="px-2 py-3 text-center text-gold font-heading font-normal uppercase text-sm">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat_izin as $izin)
                    <tr class="border-b border-dark-surface hover:bg-dark-surface/50 transition duration-150">

                        <td class="px-2 py-3 text-left text-base {{ $izin->member ? 'text-text-primary' : 'text-danger italic' }}">
                            {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                        </td>

                        <td class="px-2 py-3 text-center text-sm text-text-secondary">{{ $izin->jumlah_hari }} Hari</td>

                        <td class="px-2 py-3 text-center text-base font-bold">
                            @if($izin->status == 'disetujui')
                                <span class="text-success">{{ $izin->durasi_izin_disetujui ?? 0 }} Hari</span>
                            @else
                                <span class="text-danger">0 Hari</span>
                            @endif
                        </td>

                        <td class="px-2 py-3 text-center text-sm">
                            @if($izin->status == 'disetujui')
                                <span class="bg-success/20 text-success font-semibold px-2 py-1 rounded-full text-xs uppercase">Disetujui</span>
                            @else
                                <span class="bg-danger/20 text-danger font-semibold px-2 py-1 rounded-full text-xs uppercase">Ditolak</span>
                            @endif
                        </td>

                        <td class="px-2 py-3 text-sm text-text-secondary">
                            @if ($izin->status != 'pending')
                                {{ Str::limit($izin->keterangan_admin ?? 'Tidak ada keterangan.', 30) }}
                            @else
                                Menunggu diproses
                            @endif
                        </td>

                        <td class="px-2 py-3 text-center text-sm">
                            <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}" class="text-gold hover:underline">
                                Lihat Detail →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-text-secondary italic">
                            Belum ada riwayat persetujuan atau penolakan izin.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $riwayat_izin->links() }}
        </div>
    </div>
</section>

@endsection

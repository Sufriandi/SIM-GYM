@extends('layouts.admin')

@section('content')

<header class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle }}</h1>
        <p class="text-text-secondary text-base">Permintaan izin yang belum diproses.</p>
    </div>
    <a href="{{ route('admin.izin_latihan.history') }}" class="px-4 py-2 bg-dark-surface text-gold font-bold rounded-gym border border-gold-800 hover:bg-gold-800 hover:text-primary transition duration-300">
        Riwayat Persetujuan
    </a>
</header>

<hr class="border-t border-dark-surface mb-8">

{{-- 🚨 PERBAIKAN: Hapus blok notifikasi HTML 'success' lama agar SweetAlert2 mengambil alih. --}}
{{-- Blok @if (session('success')) dihapus di sini --}}

@if (session('danger') || session('error') || session('info'))
    {{-- Ini adalah blok untuk menampilkan error/info/danger lama yang kita biarkan tetap ada --}}
    {{-- Namun, karena danger dan success sudah ditangani SweetAlert, blok ini tidak akan terpicu jika SweetAlert2 global berjalan --}}
    {{-- Kita biarkan kosong di sini karena SweetAlert2 yang akan bekerja --}}
@endif

<section>
    <div class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark hover:border-gold-800 transition duration-300">

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b-2 border-gold-800">
                        <th class="p-3 text-left text-gold font-heading font-normal uppercase text-sm">Member</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Hari Diajukan</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Tgl Mulai</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Status</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Detail (Bukti)</th>
                        <th class="p-3 text-center text-gold font-heading font-normal uppercase text-sm">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar_izin as $izin)
                    <tr class="border-b border-dark-surface hover:bg-dark-surface/50 transition duration-150">

                        <td class="p-3 text-left text-base {{ $izin->member ? 'text-text-primary' : 'text-danger italic' }}">
                            {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                        </td>

                        <td class="p-3 text-center text-sm text-text-primary font-semibold">{{ $izin->jumlah_hari }} Hari</td>

                        <td class="p-3 text-center text-sm text-text-secondary">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}</td>

                        <td class="p-3 text-center text-sm">
                            <span class="bg-accent/20 text-accent font-semibold px-3 py-1 rounded-full text-xs uppercase">Pending</span>
                        </td>

                        {{-- Detail (Bukti) --}}
                        <td class="p-3 text-center text-sm">
                            <a href="{{ route('admin.izin_latihan.detail', $izin->id) }}" class="text-gold hover:text-gold-400 font-semibold transition duration-150 hover:underline">
                                {{ $izin->bukti_alasan ? 'Lihat Bukti' : 'Lihat Detail →' }}
                            </a>
                        </td>

                        {{-- Aksi --}}
                        <td class="p-3 text-center text-sm space-x-2">
                            @if ($izin->member)
                                {{-- Tombol Setujui (Redirect ke form) --}}
                                <a href="{{ route('admin.izin_latihan.approve.form', $izin) }}"
                                   class="px-3 py-1 bg-success text-white font-bold rounded-gym text-xs hover:bg-success-600 transition">
                                    Setujui
                                </a>

                                {{-- Form Tolak dengan SweetAlert2 --}}
                                <form id="reject-form-{{ $izin->id }}" method="POST" action="{{ route('admin.izin_latihan.reject', $izin->id) }}" class="inline-block">
                                    @csrf
                                    {{-- Menggunakan type="button" untuk memicu JS --}}
                                    <button type="button"
                                        onclick="confirmReject({{ $izin->id }}, '{{ $izin->member?->nama ?? 'Member' }}')"
                                        class="px-3 py-1 bg-danger text-white font-bold rounded-gym text-xs hover:bg-danger-600 transition">
                                        Tolak
                                    </button>
                                </form>

                            @else
                                <span class="text-danger italic text-xs">Aksi diblokir</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-text-secondary italic">
                            Tidak ada permintaan izin baru yang perlu diproses.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $daftar_izin->links() }}
        </div>
    </div>
</section>

{{-- LOGIKA JAVASCRIPT SWEETALERT2 UNTUK KONFIRMASI TOLAK --}}
<script>
    function confirmReject(izinId, memberName) {
        Swal.fire({
            title: 'Tolak Izin?',
            text: `Anda yakin ingin menolak permintaan izin dari ${memberName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Merah
            cancelButtonColor: '#6c757d',  // Abu-abu
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal',
            background: '#1a1f32',
            color: '#ffffff',
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika Admin mengklik Ya, submit form yang sesuai
                document.getElementById('reject-form-' + izinId).submit();
            }
        });
    }
</script>

{{-- Custom Scrollbar --}}
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #16213e; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c8a870; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9a7a4a; }
</style>

@endsection

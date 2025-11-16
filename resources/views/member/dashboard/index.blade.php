@extends('layouts.member')

@section('content')

<header class="mb-8">
    {{-- $pageTitle (jika Anda mengirimnya dari MemberDashboardController) --}}
    <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle ?? 'Member Dashboard' }}</h1>
    <p class="text-text-secondary text-base">Selamat datang kembali, <span class="text-gold font-semibold">{{ Auth::user()->name }}</span>!</p>
</header>

<hr class="border-t border-dark-surface mb-8">

<div class="bg-accent/20 text-accent text-base font-semibold p-4 rounded-gym mb-6 border border-accent/30 flex items-center space-x-3">
    <span>🔥</span>
    <p>Membership Anda akan berakhir dalam <span class="font-bold">3 hari</span>. Segera lakukan perpanjangan!</p>
</div>

<section class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- Card 1: Ajukan Izin --}}
    <a href="{{ route('member.izin_latihan.create') }}"
       class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark hover:border-gold-800 transition duration-300 group hover:shadow-premium">
        <div class="flex items-center space-x-4">
            <div class="bg-gold/20 text-gold p-3 rounded-gym">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-heading text-gold mt-0 mb-1 group-hover:underline">Ajukan Izin Latihan</h3>
                <p class="text-text-secondary text-sm">Tidak bisa hadir? Ajukan izin Anda di sini.</p>
            </div>
        </div>
    </a>

    {{-- Card 2: Riwayat Izin --}}
    <a href="{{ route('member.izin_latihan.index') }}"
       class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark hover:border-gold-800 transition duration-300 group hover:shadow-premium">
        <div class="flex items-center space-x-4">
            <div class="bg-gold/20 text-gold p-3 rounded-gym">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2H4zm0 14h12V4H4v12zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm0 4a1 1 0 110 2h8a1 1 0 110-2H5z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-heading text-gold mt-0 mb-1 group-hover:underline">Riwayat Izin</h3>
                <p class="text-text-secondary text-sm">Lihat status semua pengajuan izin Anda.</p>
            </div>
        </div>
    </a>

    {{-- Card 3: Profil Saya --}}
    <a href="{{ route('profile.edit') }}"
       class="bg-dark-card rounded-premium p-6 border-2 border-dark-surface shadow-dark hover:border-gold-800 transition duration-300 group hover:shadow-premium">
        <div class="flex items-center space-x-4">
            <div class="bg-gold/20 text-gold p-3 rounded-gym">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-heading text-gold mt-0 mb-1 group-hover:underline">Profil Saya</h3>
                <p class="text-text-secondary text-sm">Perbarui data diri dan password Anda.</p>
            </div>
        </div>
    </a>

</section>

{{-- 🛑 BAGIAN YANG MENYEBABKAN ERROR DIHAPUS DARI SINI 🛑 --}}
{{--
    <section>
        <div class="bg-dark-card ...">
            ...
            <table ...>
                <tbody>
                    @forelse ($daftar_izin as $izin)  <-- ERROR DI SINI
                    ...
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
--}}

@endsection

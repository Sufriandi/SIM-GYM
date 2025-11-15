@extends('layouts.member')

@section('content')

<div class="max-w-4xl mx-auto">
    <header class="mb-8">
        <h1 class="text-4xl font-heading text-gold mb-1">{{ $pageTitle ?? 'Formulir Izin Latihan' }}</h1>
        <p class="text-text-secondary text-base">Ajukan izin jika Anda tidak dapat berlatih. (Pastikan jujur!)</p>

        <div class="mt-4">
             <a href="{{ route('member.izin_latihan.index') }}"
               class="inline-flex items-center text-gold hover:text-gold-400 transition duration-200 font-semibold">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                     <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                 </svg>
                 Kembali ke Riwayat Izin
             </a>
        </div>
    </header>

    <hr class="border-t border-dark-surface mb-8">

    {{-- Tampilkan Error Validasi --}}
    @if ($errors->any())
        <div class="bg-danger/20 text-danger text-base font-semibold p-4 rounded-gym mb-6 border border-danger/30">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-dark-card rounded-premium p-6 lg:p-8 border-2 border-dark-surface shadow-dark">

        <form method="POST" action="{{ route('member.izin_latihan.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Tanggal Mulai --}}
                <div>
                    <label for="tanggal_mulai" class="block font-heading text-gold uppercase text-sm mb-2">Tanggal Mulai Izin</label>
                    <input type="date"
                           id="tanggal_mulai"
                           name="tanggal_mulai"
                           value="{{ old('tanggal_mulai') }}"
                           class="w-full bg-dark-surface border border-gold-900 rounded-gym px-4 py-3 text-text-primary placeholder-text-secondary focus:outline-none focus:border-gold transition duration-200">
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label for="tanggal_selesai" class="block font-heading text-gold uppercase text-sm mb-2">Tanggal Selesai Izin</label>
                    <input type="date"
                           id="tanggal_selesai"
                           name="tanggal_selesai"
                           value="{{ old('tanggal_selesai') }}"
                           class="w-full bg-dark-surface border border-gold-900 rounded-gym px-4 py-3 text-text-primary placeholder-text-secondary focus:outline-none focus:border-gold transition duration-200">
                </div>
            </div>

            {{-- Alasan --}}
            <div class="mt-6">
                <label for="alasan" class="block font-heading text-gold uppercase text-sm mb-2">Alasan Izin</label>
                <textarea id="alasan"
                          name="alasan"
                          rows="5"
                          placeholder="Tuliskan alasan lengkap Anda (Contoh: Sakit, Tugas Luar Kota, dll...)"
                          class="w-full bg-dark-surface border border-gold-900 rounded-gym px-4 py-3 text-text-primary placeholder-text-secondary focus:outline-none focus:border-gold transition duration-200">{{ old('alasan') }}</textarea>
            </div>

            {{-- Upload Bukti --}}
            <div class="mt-6">
                <label for="bukti_alasan" class="block font-heading text-gold uppercase text-sm mb-2">Lampirkan Bukti (Opsional)</label>
                <p class="text-text-secondary text-xs mb-2">Lampirkan bukti seperti surat dokter atau tiket. (Format: jpg, png, pdf. Maks: 2MB)</p>
                <input type="file"
                       id="bukti_alasan"
                       name="bukti_alasan"
                       class="w-full bg-dark-surface border border-gold-900 rounded-gym px-4 py-3 text-text-secondary
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-gym file:border-0
                               file:bg-gold file:text-primary-900 file:font-semibold
                               hover:file:bg-gold-600 transition duration-200">
            </div>

            {{-- Tombol Submit --}}
            <div class="mt-8 text-right">
                <button type="submit"
                        class="px-8 py-3 bg-accent text-white font-bold uppercase rounded-gym hover:bg-accent-600 border-2 border-accent hover:border-accent-400 transition duration-300 transform hover:scale-105 shadow-accent">
                    Kirim Pengajuan Izin
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
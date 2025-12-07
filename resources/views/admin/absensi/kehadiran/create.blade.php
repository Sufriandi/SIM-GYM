{{-- resources/views/admin/absensi/sesi/create.blade.php --}}

@php
    $pageTitle = $pageTitle ?? 'Buat Sesi Absensi';
@endphp

<x-layouts.admin
    :page-title="$pageTitle"
    page-subtitle="Atur nama sesi, tanggal, dan jam aktif absensi."
>
    <div class="space-y-4 max-w-4xl">

        {{-- HEADER + BACK --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Sesi absensi akan digunakan sebagai acuan kehadiran member."
        />

        <x-ui.back-button
            href="{{ route('admin.absensi.sesi.index') }}"
            text="Kembali ke daftar sesi"
            class="mb-2"
        />

        {{-- FLASH MESSAGE --}}
        @if (session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- ERROR VALIDATION SUMMARY --}}
        @if ($errors->any())
            <x-ui.toast type="danger" class="mb-4">
                <div class="text-sm font-semibold mb-1">Mohon periksa kembali input Anda.</div>
                <ul class="text-xs list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.toast>
        @endif

        {{-- FORM CARD --}}
        <x-ui.card
            title="Formulir Sesi Absensi"
            subtitle="Lengkapi informasi berikut untuk membuat sesi absensi baru."
        >
            <form
                method="POST"
                action="{{ route('admin.absensi.sesi.store') }}"
                class="space-y-5"
            >
                @csrf

                {{-- Nama Sesi --}}
                <div>
                    <x-ui.label for="nama_sesi">Nama Sesi</x-ui.label>
                    <input
                        type="text"
                        id="nama_sesi"
                        name="nama_sesi"
                        value="{{ old('nama_sesi') }}"
                        class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                        placeholder="Contoh: Latihan Pagi, Kelas HIIT, Sesi Sore, dan sebagainya"
                        required
                    >
                    @error('nama_sesi')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal + Waktu --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.label for="tanggal">Tanggal</x-ui.label>
                        <input
                            type="date"
                            id="tanggal"
                            name="tanggal"
                            value="{{ old('tanggal') }}"
                            class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                            required
                        >
                        @error('tanggal')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.label for="jam_mulai">Jam Mulai</x-ui.label>
                        <input
                            type="time"
                            id="jam_mulai"
                            name="jam_mulai"
                            value="{{ old('jam_mulai') }}"
                            class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                            required
                        >
                        @error('jam_mulai')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.label for="jam_selesai">Jam Selesai</x-ui.label>
                        <input
                            type="time"
                            id="jam_selesai"
                            name="jam_selesai"
                            value="{{ old('jam_selesai') }}"
                            class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                            required
                        >
                        @error('jam_selesai')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Catatan Admin --}}
                <div>
                    <x-ui.label for="catatan_admin">Catatan Admin (opsional)</x-ui.label>
                    <textarea
                        id="catatan_admin"
                        name="catatan_admin"
                        rows="3"
                        class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                        placeholder="Contoh: Sesi khusus member aktif, batas keterlambatan 15 menit, dan lain-lain."
                    >{{ old('catatan_admin') }}</textarea>
                    @error('catatan_admin')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <p class="text-[11px] text-text-muted max-w-md">
                        Setelah sesi dibuat, sistem akan menghasilkan kode QR yang dapat ditampilkan untuk proses absensi member.
                    </p>

                    <x-ui.button-primary type="submit" class="justify-center sm:w-auto w-full">
                        Simpan Sesi Absensi
                        <i data-lucide="save" class="w-4 h-4 ml-2"></i>
                    </x-ui.button-primary>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.admin>

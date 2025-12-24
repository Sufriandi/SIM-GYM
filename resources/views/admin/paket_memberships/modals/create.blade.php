{{-- resources/views/admin/paket_memberships/modals/create.blade.php --}}
@php
    // Default fallback agar tidak error jika lupa mengirim dari controller
    $tipeOptions = $tipeOptions ?? [
        'single' => 'Single',
        'double' => 'Double',
        'triple' => 'Triple',
    ];

    // Modal create dibuka otomatis hanya jika error validasi section ini
    // Pastikan form mengirim <input type="hidden" name="_section" value="paket_create">
    $openCreateFormErrors = $errors->any() && old('_section') === 'paket_create';
@endphp

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-start justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openCreate = false" @keydown.escape.window="openCreate = false" role="dialog" aria-modal="true"
    aria-labelledby="modal-paket-create-title">

    <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[calc(100vh-48px)] flex flex-col">

        {{-- HEADER (fixed) --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80 shrink-0">
            <div>
                <h2 id="modal-paket-create-title" class="text-xl font-semibold text-text-main">Tambah Paket Membership
                </h2>
                <p class="text-sm text-text-muted mt-0.5">Masukkan data paket membership baru.</p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false" aria-label="Tutup modal">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY (scrollable di mobile) --}}
        <div class="px-6 pb-6 pt-4 overflow-y-auto custom-scrollbar flex-1 overscroll-contain"
            style="-webkit-overflow-scrolling: touch;">

            {{-- ERROR VALIDASI CREATE --}}
            @if ($openCreateFormErrors)
                <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                    <p class="text-sm font-semibold">Ada kesalahan input:</p>
                    <ul class="list-disc list-inside text-xs mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.paket_memberships.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_section" value="paket_create">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- NAMA --}}
                    <div class="md:col-span-2">
                        <x-ui.label for="nama_create">Nama Paket<span class="text-danger">*</span></x-ui.label>
                        <input type="text" id="nama_create" name="nama" value="{{ old('nama') }}" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   @error('nama') border-danger ring-danger-soft @enderror">
                        @error('nama')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TIPE --}}
                    <div>
                        <x-ui.label for="tipe_create">Tipe Paket<span class="text-danger">*</span></x-ui.label>
                        <select id="tipe_create" name="tipe" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   @error('tipe') border-danger ring-danger-soft @enderror">
                            <option value="" disabled {{ old('tipe') ? '' : 'selected' }}>Pilih tipe paket
                            </option>
                            @foreach ($tipeOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('tipe') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipe')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror

                        <p class="text-[11px] text-text-muted mt-1">
                            Single = 1 orang, Double = 2 orang, Triple = 3 orang.
                        </p>
                    </div>

                    {{-- DURASI --}}
                    <div>
                        <x-ui.label for="durasi_create">Durasi (hari)<span class="text-danger">*</span></x-ui.label>
                        <input type="number" id="durasi_create" name="durasi" min="1"
                            value="{{ old('durasi') }}" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   @error('durasi') border-danger ring-danger-soft @enderror">
                        @error('durasi')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror

                        <p class="text-[11px] text-text-muted mt-1">
                            Contoh: 1 hari = 1, 1 bulan ≈ 30, 3 bulan ≈ 90, 1 tahun ≈ 365.
                        </p>
                    </div>

                    {{-- HARGA --}}
                    <div class="md:col-span-2">
                        <x-ui.label for="harga_create">Harga (Rp)<span class="text-danger">*</span></x-ui.label>
                        <input type="number" id="harga_create" name="harga" min="0" step="1000"
                            value="{{ old('harga') }}" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   @error('harga') border-danger ring-danger-soft @enderror">
                        @error('harga')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- DESKRIPSI --}}
                <div>
                    <x-ui.label for="deskripsi_create">Deskripsi (opsional)</x-ui.label>
                    <textarea id="deskripsi_create" name="deskripsi" rows="3"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                               @error('deskripsi') border-danger ring-danger-soft @enderror">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror

                    <p class="text-[11px] text-text-muted mt-1">
                        Contoh: “Paket bulanan reguler”, “Family – paket couple”, “Paket pelajar wajib menunjukkan kartu
                        pelajar”.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft/70 mt-2">
                    <x-ui.button-secondary type="button" @click="openCreate = false">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Paket</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>

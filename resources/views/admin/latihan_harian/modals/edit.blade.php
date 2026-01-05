{{-- resources/views/admin/latihan_harian/modals/edit.blade.php --}}
@php
    $hargaUmum = is_array($defaultHarga) ? $defaultHarga['umum'] ?? 0 : $defaultHarga ?? 0;
    $hargaPelajar = is_array($defaultHarga) ? $defaultHarga['pelajar'] ?? $hargaUmum : $defaultHarga ?? 0;

    $nilaiHarga = (int) old('harga', $item->harga);
    $displayHarga = $nilaiHarga ? number_format($nilaiHarga, 0, ',', '.') : '';

    // Logic auto-open jika ada error validasi pada item ini
    $openEditOnLoad = $errors->any() && old('_method') === 'PUT' && (int) old('latihan_id') === (int) $item->id;
@endphp

<div x-show="openEditId === {{ $item->id }} || {{ $openEditOnLoad ? 'true' : 'false' }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openEditId = null" @keydown.escape.window="openEditId = null">

    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Latihan Harian</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Perbarui data latihan harian atas nama
                    <span class="font-semibold">{{ $item->nama }}</span>.
                </p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEditId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form action="{{ route('admin.latihan_harian.update', $item) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                {{-- ID untuk identifikasi validasi --}}
                <input type="hidden" name="latihan_id" value="{{ $item->id }}">

                {{-- ERROR VALIDASI --}}
                @if ($openEditOnLoad)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-2">
                        <p class="text-sm font-semibold">Ada kesalahan input saat mengubah data:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- GRID 2 KOLOM --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- KOLOM KIRI --}}
                    <div class="space-y-4">
                        {{-- NAMA --}}
                        <div>
                            <x-ui.label for="nama_edit_{{ $item->id }}">Nama Pelanggan</x-ui.label>
                            <input type="text" id="nama_edit_{{ $item->id }}" name="nama"
                                value="{{ old('nama', $item->nama) }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2
                                       focus:ring-primary-dark focus:border-transparent"
                                required>
                            @error('nama')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TANGGAL --}}
                        <div>
                            <x-ui.label for="tanggal_edit_{{ $item->id }}">Tanggal</x-ui.label>
                            <div class="relative">
                                <input type="date" id="tanggal_edit_{{ $item->id }}" name="tanggal"
                                    value="{{ old('tanggal', $item->tanggal->format('Y-m-d')) }}"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent"
                                    required>
                                <i data-lucide="calendar"
                                    class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                            @error('tanggal')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KATEGORI --}}
                        <div>
                            <x-ui.label for="kategori_edit_{{ $item->id }}">Kategori</x-ui.label>
                            <div class="relative">
                                <select id="kategori_edit_{{ $item->id }}" name="kategori"
                                    class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main
                                           px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent"
                                    @change="setDefaultHargaLatihan($event.target.value, {{ $hargaUmum }}, {{ $hargaPelajar }}, 'harga_edit_{{ $item->id }}')"
                                    required>
                                    <option value="umum"
                                        {{ old('kategori', $item->kategori) === 'umum' ? 'selected' : '' }}>Umum
                                    </option>
                                    <option value="pelajar"
                                        {{ old('kategori', $item->kategori) === 'pelajar' ? 'selected' : '' }}>Pelajar
                                    </option>
                                </select>
                                <i data-lucide="chevron-down"
                                    class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                            <p class="text-[11px] text-text-muted mt-1">
                                Umum: Rp {{ number_format($hargaUmum, 0, ',', '.') }} ·
                                Pelajar: Rp {{ number_format($hargaPelajar, 0, ',', '.') }}.
                            </p>
                            @error('kategori')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- KOLOM KANAN --}}
                    <div class="space-y-4">
                        {{-- HARGA --}}
                        <div>
                            <x-ui.label for="harga_display_edit_{{ $item->id }}">Harga (Rp)</x-ui.label>
                            <input type="hidden" name="harga" id="harga_edit_{{ $item->id }}"
                                value="{{ $nilaiHarga }}">

                            <input type="text" id="harga_display_edit_{{ $item->id }}" data-rupiah-display
                                data-target="harga_edit_{{ $item->id }}" inputmode="numeric" autocomplete="off"
                                value="{{ $displayHarga }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2
                                       focus:ring-primary-dark focus:border-transparent"
                                required>
                            <p class="text-[11px] text-text-muted mt-1">
                                Sesuaikan jika ada perubahan tarif, promo, atau diskon.
                            </p>
                            @error('harga')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- METODE PEMBAYARAN --}}
                        <div>
                            <x-ui.label for="metode_pembayaran_edit_{{ $item->id }}">Metode Pembayaran</x-ui.label>
                            <div class="relative">
                                <select id="metode_pembayaran_edit_{{ $item->id }}" name="metode_pembayaran"
                                    class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main
                                           px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent"
                                    required>
                                    <option value="cash"
                                        {{ old('metode_pembayaran', $item->metode_pembayaran) === 'cash' ? 'selected' : '' }}>
                                        Cash</option>
                                    <option value="transfer"
                                        {{ old('metode_pembayaran', $item->metode_pembayaran) === 'transfer' ? 'selected' : '' }}>
                                        Transfer</option>
                                    <option value="qris"
                                        {{ old('metode_pembayaran', $item->metode_pembayaran) === 'qris' ? 'selected' : '' }}>
                                        QRIS</option>
                                </select>
                                <i data-lucide="chevron-down"
                                    class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                            @error('metode_pembayaran')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- KETERANGAN (FULL WIDTH) --}}
                    <div class="md:col-span-2">
                        <x-ui.label for="keterangan_edit_{{ $item->id }}">Keterangan (opsional)</x-ui.label>
                        <textarea id="keterangan_edit_{{ $item->id }}" name="keterangan" rows="3"
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                   border-brand-borderSoft focus:outline-none focus:ring-2
                                   focus:ring-primary-dark focus:border-transparent"
                            placeholder="Contoh: Promo 17 Agustus, teman member, dll.">{{ old('keterangan', $item->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-brand-borderSoft">
                    <x-ui.button-secondary type="button" @click="openEditId = null">
                        Batal
                    </x-ui.button-secondary>
                    <x-ui.button-primary type="submit">
                        Simpan Perubahan
                    </x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>

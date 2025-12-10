@php
    // config('gym.harga_harian') diasumsikan berupa:
    // ['umum' => 20000, 'pelajar' => 15000] atau satu angka saja
    $hargaUmum = is_array($defaultHarga) ? $defaultHarga['umum'] ?? 0 : $defaultHarga ?? 0;
    $hargaPelajar = is_array($defaultHarga) ? $defaultHarga['pelajar'] ?? $hargaUmum : $defaultHarga ?? 0;

    $oldHarga = old('harga');
    $displayHarga = $oldHarga !== null ? number_format((int) $oldHarga, 0, ',', '.') : '';
    $today = now()->toDateString();
@endphp

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openCreate = false">

    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Latihan Harian</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Masukkan data pelanggan yang latihan harian hari ini.
                </p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form action="{{ route('admin.latihan_harian.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- ERROR VALIDASI --}}
                @if ($errors->any() && old('_method') !== 'PUT')
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-2">
                        <p class="text-sm font-semibold">Ada kesalahan input:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- GRID 2 KOLOM --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- KOLOM KIRI: Nama, Tanggal, Kategori --}}
                    <div class="space-y-4">
                        {{-- NAMA --}}
                        <div>
                            <x-ui.label for="nama_create">Nama Pelanggan</x-ui.label>
                            <input type="text" id="nama_create" name="nama" value="{{ old('nama') }}"
                                placeholder="Contoh: Budi, Andi, dll."
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
                            <x-ui.label for="tanggal_create">Tanggal</x-ui.label>
                            <div class="relative">
                                <input type="date" id="tanggal_create" name="tanggal"
                                    value="{{ old('tanggal', $today) }}"
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
                            <x-ui.label for="kategori_create">Kategori</x-ui.label>
                            <div class="relative">
                                @php $hasOldHarga = $oldHarga !== null; @endphp
                                <select id="kategori_create" name="kategori"
                                    class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main
                                               px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent"
                                    x-init="@if (!$hasOldHarga) setDefaultHargaLatihan($el.value || 'umum', {{ $hargaUmum }}, {{ $hargaPelajar }}, 'harga_create'); @endif"
                                    @change="setDefaultHargaLatihan($event.target.value, {{ $hargaUmum }}, {{ $hargaPelajar }}, 'harga_create')"
                                    required>
                                    <option value="umum" {{ old('kategori', 'umum') === 'umum' ? 'selected' : '' }}>
                                        Umum</option>
                                    <option value="pelajar" {{ old('kategori') === 'pelajar' ? 'selected' : '' }}>
                                        Pelajar</option>
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

                    {{-- KOLOM KANAN: Harga, Metode Pembayaran --}}
                    <div class="space-y-4">
                        {{-- HARGA (MASK RUPIAH) --}}
                        <div>
                            <x-ui.label for="harga_display_create">Harga (Rp)</x-ui.label>

                            {{-- asli dikirim ke server --}}
                            <input type="hidden" name="harga" id="harga_create" value="{{ $oldHarga ?? '' }}">

                            {{-- tampilan terformat --}}
                            <input type="text" id="harga_display_create" data-rupiah-display
                                data-target="harga_create" inputmode="numeric" autocomplete="off"
                                placeholder="Contoh: 20.000" value="{{ $displayHarga }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                          border-brand-borderSoft focus:outline-none focus:ring-2
                                          focus:ring-primary-dark focus:border-transparent"
                                required>
                            <p class="text-[11px] text-text-muted mt-1">
                                Dapat diubah jika ada promo atau diskon.
                            </p>
                            @error('harga')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- METODE PEMBAYARAN --}}
                        <div>
                            <x-ui.label for="metode_pembayaran_create">Metode Pembayaran</x-ui.label>
                            <div class="relative">
                                <select id="metode_pembayaran_create" name="metode_pembayaran"
                                    class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main
                                               px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent"
                                    required>
                                    <option value="cash"
                                        {{ old('metode_pembayaran', 'cash') === 'cash' ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="transfer"
                                        {{ old('metode_pembayaran') === 'transfer' ? 'selected' : '' }}>Transfer
                                    </option>
                                    <option value="qris" {{ old('metode_pembayaran') === 'qris' ? 'selected' : '' }}>
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

                    {{-- KETERANGAN (FULL WIDTH, 2 KOLOM) --}}
                    <div class="md:col-span-2">
                        <x-ui.label for="keterangan_create">Keterangan (opsional)</x-ui.label>
                        <textarea id="keterangan_create" name="keterangan" rows="3"
                            placeholder="Contoh: Promo 17 Agustus, free trial, teman member, dll."
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                         border-brand-borderSoft focus:outline-none focus:ring-2
                                         focus:ring-primary-dark focus:border-transparent">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-brand-borderSoft">
                    <x-ui.button-secondary type="button" @click="openCreate = false">
                        Batal
                    </x-ui.button-secondary>
                    <x-ui.button-primary type="submit">
                        Simpan Transaksi
                    </x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>

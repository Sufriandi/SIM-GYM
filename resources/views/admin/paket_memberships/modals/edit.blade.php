{{-- resources/views/admin/paket_memberships/modals/edit.blade.php --}}
@php
    /** @var \App\Models\PaketMembership $paket */
@endphp

<div x-show="openEdit" x-cloak x-transition @click.self="openEdit = false"
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm">
    <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Paket Membership</h2>
                <p class="text-sm text-text-muted mt-0.5">{{ $paket->nama }}</p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEdit = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY MODAL --}}
        <div class="px-6 pb-6 pt-4 max-h-[80vh] overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.paket_memberships.update', $paket) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="paket_id" value="{{ $paket->id }}">

                {{-- ERROR VALIDASI KHUSUS EDIT PAKET INI --}}
                @if ($errors->any() && old('_method') === 'PUT' && old('paket_id') == $paket->id)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-2">
                        <p class="text-sm font-semibold">Ada kesalahan input:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- NAMA --}}
                    <div class="md:col-span-2">
                        <x-ui.label for="nama_edit_{{ $paket->id }}">
                            Nama Paket<span class="text-danger">*</span>
                        </x-ui.label>
                        <input type="text" id="nama_edit_{{ $paket->id }}" name="nama"
                            value="{{ old('nama', $paket->nama) }}" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('nama') border-danger ring-danger-soft @enderror">
                        @error('nama')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TIPE --}}
                    <div>
                        <x-ui.label for="tipe_edit_{{ $paket->id }}">
                            Tipe Paket<span class="text-danger">*</span>
                        </x-ui.label>
                        <select id="tipe_edit_{{ $paket->id }}" name="tipe" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('tipe') border-danger ring-danger-soft @enderror">
                            @foreach ($tipeOptions as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('tipe', $paket->tipe) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipe')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DURASI --}}
                    <div>
                        <x-ui.label for="durasi_edit_{{ $paket->id }}">
                            Durasi (hari)<span class="text-danger">*</span>
                        </x-ui.label>
                        <input type="number" id="durasi_edit_{{ $paket->id }}" name="durasi" min="1"
                            value="{{ old('durasi', $paket->durasi) }}" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('durasi') border-danger ring-danger-soft @enderror">
                        @error('durasi')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- HARGA --}}
                    <div class="md:col-span-2">
                        <x-ui.label for="harga_edit_{{ $paket->id }}">
                            Harga (Rp)<span class="text-danger">*</span>
                        </x-ui.label>
                        <input type="number" id="harga_edit_{{ $paket->id }}" name="harga" min="0"
                            step="1000" value="{{ old('harga', (int) $paket->harga) }}" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('harga') border-danger ring-danger-soft @enderror">
                        @error('harga')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- DESKRIPSI --}}
                <div>
                    <x-ui.label for="deskripsi_edit_{{ $paket->id }}">
                        Deskripsi (opsional)
                    </x-ui.label>
                    <textarea id="deskripsi_edit_{{ $paket->id }}" name="deskripsi" rows="3"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('deskripsi') border-danger ring-danger-soft @enderror">{{ old('deskripsi', $paket->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft/70 mt-2">
                    <x-ui.button-secondary type="button" @click="openEdit = false">
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

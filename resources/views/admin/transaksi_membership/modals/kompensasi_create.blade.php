{{-- resources/views/admin/transaksi_membership/modals/kompensasi_create.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    $memberOptions = ($members ?? collect())
        ->map(function ($m) {
            $name = $m->user?->name ?? '';
            $username = $m->user?->username ? ' (' . $m->user->username . ')' : '';
            return [
                'id' => (int) $m->id,
                'label' => trim($name . $username),
            ];
        })
        ->values()
        ->all();

    $oldMemberId = old('member_id');
    $oldMemberLabel = '';
    if ($oldMemberId) {
        $found = collect($memberOptions)->firstWhere('id', (int) $oldMemberId);
        $oldMemberLabel = $found['label'] ?? '';
    }

    $defaultTanggalTransaksi = old('tanggal_transaksi', Carbon::now()->format('Y-m-d\TH:i'));

    $openErrors = $errors->any() && old('_section') === 'trx_membership_kompensasi_create';
@endphp

<div x-show="openKompensasi" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-start justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openKompensasi = false" @keydown.escape.window="openKompensasi = false" role="dialog" aria-modal="true"
    aria-labelledby="modal-trx-kompensasi-title">

    <div class="relative w-full max-w-3xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
                max-h-[calc(100vh-48px)] flex flex-col"
        x-data="trxMembershipKompensasiModal()" x-init="init()" @click.away="dropdownOpen = false">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80 shrink-0">
            <div>
                <h2 id="modal-trx-kompensasi-title" class="text-xl font-semibold text-text-main">Tambah Bonus/Trial
                    (Kompensasi)</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Menambah masa aktif tanpa pembayaran. Boleh untuk member baru, aktif, maupun expired.
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openKompensasi = false" aria-label="Tutup modal">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 overflow-y-auto custom-scrollbar flex-1 overscroll-contain"
            style="-webkit-overflow-scrolling: touch;">

            @if ($openErrors)
                <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                    <p class="text-sm font-semibold">Ada kesalahan input:</p>
                    <ul class="list-disc list-inside text-xs mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.transaksi_membership.kompensasi_manual.store') }}"
                class="space-y-5">
                @csrf
                <input type="hidden" name="_section" value="trx_membership_kompensasi_create">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- KIRI --}}
                    <div class="space-y-4">

                        {{-- MEMBER --}}
                        <div class="space-y-2">
                            <x-ui.label for="member_search_komp">Member<span class="text-danger">*</span></x-ui.label>

                            <div class="relative" @click.outside="dropdownOpen = false">
                                <input type="text" id="member_search_komp" x-model="form.memberSearch"
                                    @focus="dropdownOpen = true" @input="dropdownOpen = true"
                                    placeholder="Cari nama / username member..." autocomplete="off"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                           @error('member_id') border-danger ring-danger-soft @enderror">

                                <input type="hidden" name="member_id" :value="form.memberId ?? ''">

                                <div x-show="dropdownOpen" x-cloak
                                    class="absolute z-50 mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg overflow-hidden">
                                    <div class="max-h-56 overflow-y-auto custom-scrollbar" @wheel.stop @touchmove.stop>
                                        <template x-for="m in filteredMembers(form.memberSearch)"
                                            :key="'m-' + m.id">
                                            <button type="button"
                                                class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                                @click="selectMember(m)" x-text="m.label"></button>
                                        </template>

                                        <div x-show="filteredMembers(form.memberSearch).length === 0"
                                            class="px-3 py-2 text-xs text-text-muted">
                                            Member tidak ditemukan.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @error('member_id')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PAKET (wajib karena paket_id NOT NULL) --}}
                        <div class="space-y-2">
                            <x-ui.label for="paket_id_komp">Referensi Paket<span
                                    class="text-danger">*</span></x-ui.label>
                            <select id="paket_id_komp" name="paket_id" required
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('paket_id') border-danger ring-danger-soft @enderror">
                                <option value="">-- Pilih Paket --</option>
                                @foreach ($paketList as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('paket_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }} ({{ ucfirst($p->tipe) }}, {{ $p->durasi }} hari)
                                    </option>
                                @endforeach
                            </select>

                            @error('paket_id')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-text-muted">
                                Ini hanya untuk mengisi kolom <code>paket_id</code> (FK). Durasi bonus ditentukan oleh
                                “Jumlah hari”.
                            </p>
                        </div>
                    </div>

                    {{-- KANAN --}}
                    <div class="space-y-4">

                        {{-- JUMLAH HARI --}}
                        <div>
                            <x-ui.label for="jumlah_hari_komp">Jumlah Hari Bonus<span
                                    class="text-danger">*</span></x-ui.label>
                            <input type="number" id="jumlah_hari_komp" name="jumlah_hari" min="1" max="365"
                                value="{{ old('jumlah_hari', 3) }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('jumlah_hari') border-danger ring-danger-soft @enderror">

                            @error('jumlah_hari')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-text-muted mt-1">
                                Contoh: trial 3 hari, bonus 7 hari, kompensasi admin 14 hari, dll.
                            </p>
                        </div>

                        {{-- TANGGAL TRANSAKSI --}}
                        <div>
                            <x-ui.label for="tanggal_transaksi_komp">Tanggal & Waktu Transaksi</x-ui.label>
                            <input type="datetime-local" id="tanggal_transaksi_komp" name="tanggal_transaksi"
                                value="{{ $defaultTanggalTransaksi }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('tanggal_transaksi') border-danger ring-danger-soft @enderror">

                            @error('tanggal_transaksi')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KETERANGAN --}}
                        <div>
                            <x-ui.label for="keterangan_komp">Keterangan (opsional)</x-ui.label>
                            <textarea id="keterangan_komp" name="keterangan" rows="4"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('keterangan') border-danger ring-danger-soft @enderror"
                                placeholder="Contoh: Trial admin 3 hari, Bonus event, dll.">{{ old('keterangan') }}</textarea>

                            @error('keterangan')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft/70 mt-2">
                    <x-ui.button-secondary type="button" @click="openKompensasi = false">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan</x-ui.button-primary>
                </div>
            </form>
        </div>

        <script>
            function trxMembershipKompensasiModal() {
                return {
                    members: @js($memberOptions),

                    form: {
                        memberSearch: @js($oldMemberLabel),
                        memberId: @js($oldMemberId),
                    },

                    dropdownOpen: false,

                    init() {},

                    filteredMembers(text) {
                        const q = (text || '').toLowerCase().trim();
                        if (!q) return this.members;
                        return this.members.filter(m => (m.label || '').toLowerCase().includes(q));
                    },

                    selectMember(m) {
                        this.form.memberId = m.id;
                        this.form.memberSearch = m.label;
                        this.dropdownOpen = false;
                    },
                }
            }
        </script>
    </div>
</div>

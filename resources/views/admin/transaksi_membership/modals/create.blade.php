{{-- resources/views/admin/transaksi_membership/modals/create.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    // options member untuk dropdown searchable (buyer & participant)
    $memberOptions = ($members ?? collect())
        ->map(function ($m) {
            $name = $m->user?->name ?? '';
            $username = $m->user?->username ? ' (' . $m->user->username . ')' : '';
            return [
                'id' => $m->id,
                'label' => trim($name . $username),
            ];
        })
        ->values()
        ->all();

    // old buyer
    $oldBuyerId = old('buyer_member_id');
    $oldBuyerLabel = '';
    if ($oldBuyerId) {
        $found = collect($memberOptions)->firstWhere('id', (int) $oldBuyerId);
        $oldBuyerLabel = $found['label'] ?? '';
    }

    // datetime-local default
    $defaultTanggalTransaksi = old('tanggal_transaksi', Carbon::now()->format('Y-m-d\TH:i'));

    // old participants (array)
    $oldParticipantIds = old('participant_ids', []);
    $oldP1 = $oldParticipantIds[0] ?? null;
    $oldP2 = $oldParticipantIds[1] ?? null;

    $oldP1Label = '';
    if ($oldP1) {
        $foundP1 = collect($memberOptions)->firstWhere('id', (int) $oldP1);
        $oldP1Label = $foundP1['label'] ?? '';
    }

    $oldP2Label = '';
    if ($oldP2) {
        $foundP2 = collect($memberOptions)->firstWhere('id', (int) $oldP2);
        $oldP2Label = $foundP2['label'] ?? '';
    }

    // metode
    $metodeOptions = $metodeOptions ?? [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];

    // paket meta untuk alpine
    $paketMeta = ($paketList ?? collect())
        ->map(
            fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'tipe' => $p->tipe, // single/double/triple
                'durasi' => $p->durasi,
            ],
        )
        ->values()
        ->all();
@endphp

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openCreate = false" @keydown.escape.window="openCreate = false" @wheel.prevent @touchmove.prevent>

    <div class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        x-data="{
            members: @js($memberOptions),
            paketMeta: @js($paketMeta),
        
            form: {
                buyerSearch: @js($oldBuyerLabel),
                buyerId: @js($oldBuyerId),
        
                paketId: @js(old('paket_id')),
                tanggalTransaksi: @js($defaultTanggalTransaksi),
        
                jenisTransaksi: @js(old('jenis_transaksi', 'sale')),
                metodePembayaran: @js(old('metode_pembayaran', 'cash')),
                keterangan: @js(old('keterangan')),
            },
        
            participant: {
                p1Search: @js($oldP1Label),
                p1Id: @js($oldP1),
                p2Search: @js($oldP2Label),
                p2Id: @js($oldP2),
            },
        
            dropdownOpen: false,
            p1Open: false,
            p2Open: false,
        
            closeAll() {
                this.dropdownOpen = false;
                this.p1Open = false;
                this.p2Open = false;
            },
        
            getSelectedPackageType() {
                if (!this.form.paketId) return null;
                const id = String(this.form.paketId);
                const p = this.paketMeta.find(pk => String(pk.id) === id);
                return p ? p.tipe : null;
            },
        
            maxAdditional() {
                const t = this.getSelectedPackageType();
                if (t === 'double') return 1;
                if (t === 'triple') return 2;
                return 0;
            },
        
            matchByText(m, text) {
                if (!text) return true;
                return m.label.toLowerCase().includes(String(text).toLowerCase());
            },
        
            selectBuyer(m) {
                this.form.buyerId = m.id;
                this.form.buyerSearch = m.label;
                this.dropdownOpen = false;
        
                // jika buyer sama dengan peserta, kosongkan peserta tsb
                if (String(this.participant.p1Id) === String(m.id)) {
                    this.participant.p1Id = null;
                    this.participant.p1Search = '';
                }
                if (String(this.participant.p2Id) === String(m.id)) {
                    this.participant.p2Id = null;
                    this.participant.p2Search = '';
                }
            },
        
            selectP1(m) {
                this.participant.p1Id = m.id;
                this.participant.p1Search = m.label;
                this.p1Open = false;
        
                // cegah duplikat dengan p2
                if (String(this.participant.p2Id) === String(m.id)) {
                    this.participant.p2Id = null;
                    this.participant.p2Search = '';
                }
            },
        
            selectP2(m) {
                this.participant.p2Id = m.id;
                this.participant.p2Search = m.label;
                this.p2Open = false;
        
                // cegah duplikat dengan p1
                if (String(this.participant.p1Id) === String(m.id)) {
                    this.participant.p1Id = null;
                    this.participant.p1Search = '';
                }
            },
        }" @click.away="closeAll()">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Transaksi Membership</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Sistem akan otomatis menghitung periode (auto-extend) berdasarkan riwayat transaksi member.
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.transaksi_membership.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- KIRI --}}
                    <div class="space-y-4">
                        {{-- BUYER (SEARCHABLE) --}}
                        <div class="space-y-2">
                            <x-ui.label for="buyer_search_create">Member Pembeli (Primary)</x-ui.label>

                            <div class="relative">
                                <input type="text" id="buyer_search_create" x-model="form.buyerSearch"
                                    @focus="dropdownOpen = true" @input="dropdownOpen = true"
                                    placeholder="Cari nama / username member..." autocomplete="off"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">

                                <input type="hidden" name="buyer_member_id" :value="form.buyerId ?? ''">

                                {{-- DROPDOWN BUYER --}}
                                <div x-show="dropdownOpen" x-cloak @wheel.stop @touchmove.stop
                                    class="absolute z-50 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-brand-borderSoft
                                           bg-brand-shell shadow-lg custom-scrollbar">
                                    <template x-for="m in members" :key="'buyer-' + m.id">
                                        <button type="button" x-show="matchByText(m, form.buyerSearch)"
                                            class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                            @click="selectBuyer(m)" x-text="m.label"></button>
                                    </template>

                                    <div x-show="members.filter(m => matchByText(m, form.buyerSearch)).length === 0"
                                        class="px-3 py-2 text-xs text-text-muted">
                                        Member tidak ditemukan.
                                    </div>
                                </div>
                            </div>

                            @error('buyer_member_id')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-text-muted">
                                Wajib pilih 1 pembeli (primary). Untuk paket double/triple, isi peserta tambahan di
                                bawah.
                            </p>
                        </div>

                        {{-- PAKET --}}
                        <div class="space-y-2">
                            <x-ui.label for="paket_id_create">Paket Membership</x-ui.label>
                            <select id="paket_id_create" name="paket_id" required x-model="form.paketId"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
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
                        </div>

                        {{-- PESERTA TAMBAHAN (CUSTOM DROPDOWN) --}}
                        <div class="space-y-2">
                            <x-ui.label>Peserta Tambahan (khusus paket double / triple)</x-ui.label>

                            {{-- Peserta 1 --}}
                            <template x-if="maxAdditional() >= 1">
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-semibold text-text-muted">
                                        Peserta 1 (tambahan)
                                    </label>

                                    <div class="relative">
                                        <input type="text" x-model="participant.p1Search" @focus="p1Open = true"
                                            @input="p1Open = true" placeholder="Cari nama / username member..."
                                            autocomplete="off"
                                            class="w-full rounded-xl border bg-brand-shell text-xs text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">

                                        <input type="hidden" name="participant_ids[]" :value="participant.p1Id ?? ''">

                                        <div x-show="p1Open" x-cloak @wheel.stop @touchmove.stop
                                            class="absolute z-50 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-brand-borderSoft
                                                   bg-brand-shell shadow-lg custom-scrollbar">
                                            <template x-for="m in members" :key="'p1-' + m.id">
                                                <button type="button" x-show="matchByText(m, participant.p1Search)"
                                                    :disabled="String(m.id) === String(form.buyerId) || String(m.id) === String(
                                                        participant.p2Id)"
                                                    @click="selectP1(m)"
                                                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50 disabled:opacity-40 disabled:cursor-not-allowed"
                                                    x-text="m.label">
                                                </button>
                                            </template>

                                            <div x-show="members.filter(x => matchByText(x, participant.p1Search)).length === 0"
                                                class="px-3 py-2 text-xs text-text-muted">
                                                Member tidak ditemukan.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Peserta 2 --}}
                            <template x-if="maxAdditional() >= 2">
                                <div class="space-y-1 mt-2">
                                    <label class="block text-[11px] font-semibold text-text-muted">
                                        Peserta 2 (tambahan)
                                    </label>

                                    <div class="relative">
                                        <input type="text" x-model="participant.p2Search" @focus="p2Open = true"
                                            @input="p2Open = true" placeholder="Cari nama / username member..."
                                            autocomplete="off"
                                            class="w-full rounded-xl border bg-brand-shell text-xs text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">

                                        <input type="hidden" name="participant_ids[]" :value="participant.p2Id ?? ''">

                                        <div x-show="p2Open" x-cloak @wheel.stop @touchmove.stop
                                            class="absolute z-50 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-brand-borderSoft
                                                   bg-brand-shell shadow-lg custom-scrollbar">
                                            <template x-for="m in members" :key="'p2-' + m.id">
                                                <button type="button" x-show="matchByText(m, participant.p2Search)"
                                                    :disabled="String(m.id) === String(form.buyerId) || String(m.id) === String(
                                                        participant.p1Id)"
                                                    @click="selectP2(m)"
                                                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50 disabled:opacity-40 disabled:cursor-not-allowed"
                                                    x-text="m.label">
                                                </button>
                                            </template>

                                            <div x-show="members.filter(x => matchByText(x, participant.p2Search)).length === 0"
                                                class="px-3 py-2 text-xs text-text-muted">
                                                Member tidak ditemukan.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            @error('participant_ids')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                            @error('participant_ids.*')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-text-muted">
                                Peserta tambahan tidak boleh sama dengan pembeli dan tidak boleh duplikat.
                                Kapasitas mengikuti tipe paket (single=0, double=1, triple=2).
                            </p>
                        </div>
                    </div>

                    {{-- KANAN --}}
                    <div class="space-y-4">
                        {{-- TANGGAL TRANSAKSI --}}
                        <div>
                            <x-ui.label for="tanggal_transaksi_create">Tanggal & Waktu Transaksi</x-ui.label>
                            <input type="datetime-local" id="tanggal_transaksi_create" name="tanggal_transaksi"
                                value="{{ $defaultTanggalTransaksi }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">

                            @error('tanggal_transaksi')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-text-muted mt-1">
                                Default: sekarang. Dipakai sebagai basis perhitungan tanggal mulai (auto-extend).
                            </p>
                        </div>

                        {{-- JENIS TRANSAKSI --}}
                        <div>
                            <x-ui.label for="jenis_transaksi_create">Jenis Transaksi</x-ui.label>
                            <select id="jenis_transaksi_create" name="jenis_transaksi" x-model="form.jenisTransaksi"
                                required
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                <option value="sale"
                                    {{ old('jenis_transaksi', 'sale') === 'sale' ? 'selected' : '' }}>Sale</option>
                                <option value="adjustment"
                                    {{ old('jenis_transaksi') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            </select>

                            @error('jenis_transaksi')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-text-muted mt-1">
                                Jika <strong>sale</strong>, metode pembayaran wajib. Jika <strong>adjustment</strong>,
                                metode boleh kosong.
                            </p>
                        </div>

                        {{-- METODE PEMBAYARAN (FIX: pakai x-if agar tidak menimpa) --}}
                        <template x-if="form.jenisTransaksi === 'sale'">
                            <div>
                                <x-ui.label for="metode_pembayaran_create">Metode Pembayaran</x-ui.label>
                                <select id="metode_pembayaran_create" name="metode_pembayaran"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                    @foreach ($metodeOptions as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('metode_pembayaran', 'cash') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('metode_pembayaran')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </template>

                        <template x-if="form.jenisTransaksi !== 'sale'">
                            <input type="hidden" name="metode_pembayaran" value="">
                        </template>

                        {{-- KETERANGAN --}}
                        <div>
                            <x-ui.label for="keterangan_create">Keterangan (opsional)</x-ui.label>
                            <textarea id="keterangan_create" name="keterangan" rows="4"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                placeholder="Contoh: Pembayaran tunai, promo, catatan khusus.">{{ old('keterangan') }}</textarea>

                            @error('keterangan')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft/70 mt-2">
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

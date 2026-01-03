{{-- resources/views/admin/transaksi_membership/modals/create.blade.php --}}
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

    $oldBuyerId = old('buyer_member_id');
    $oldBuyerLabel = '';
    if ($oldBuyerId) {
        $found = collect($memberOptions)->firstWhere('id', (int) $oldBuyerId);
        $oldBuyerLabel = $found['label'] ?? '';
    }

    $defaultTanggalTransaksi = old('tanggal_transaksi', Carbon::now()->format('Y-m-d\TH:i'));

    $oldParticipantIds = (array) old('participant_ids', []);
    $oldParticipantIds = array_values(array_filter(array_map('intval', $oldParticipantIds)));

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

    $metodeOptions = $metodeOptions ?? [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];

    $paketMeta = ($paketList ?? collect())
        ->map(
            fn($p) => [
                'id' => (int) $p->id,
                'nama' => (string) $p->nama,
                'tipe' => (string) $p->tipe,
                'durasi' => (int) $p->durasi,
            ],
        )
        ->values()
        ->all();

    $openCreateFormErrors = $errors->any() && old('_section') === 'trx_membership_create';
@endphp

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-start justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openCreate = false" @keydown.escape.window="openCreate = false" role="dialog" aria-modal="true"
    aria-labelledby="modal-trx-create-title">

    <div class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
                max-h-[calc(100vh-48px)] flex flex-col"
        x-data="trxMembershipPembayaranModal()" x-init="init()" @click.away="closeAll()">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80 shrink-0">
            <div>
                <h2 id="modal-trx-create-title" class="text-xl font-semibold text-text-main">Tambah Membership</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Sistem akan otomatis menghitung periode (auto-extend) berdasarkan histori transaksi.
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false" aria-label="Tutup modal">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 overflow-y-auto custom-scrollbar flex-1 overscroll-contain"
            style="-webkit-overflow-scrolling: touch;">

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

            <form method="POST" action="{{ route('admin.transaksi_membership.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_section" value="trx_membership_create">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- KIRI --}}
                    <div class="space-y-4">

                        {{-- BUYER --}}
                        <div class="space-y-2">
                            <x-ui.label for="buyer_search_create">Member Pembeli (Primary)<span
                                    class="text-danger">*</span></x-ui.label>

                            <div class="relative" @click.outside="dropdownOpen = false">
                                <input type="text" id="buyer_search_create" x-model="form.buyerSearch"
                                    @focus="dropdownOpen = true" @input="dropdownOpen = true"
                                    placeholder="Cari nama / username member..." autocomplete="off"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                           @error('buyer_member_id') border-danger ring-danger-soft @enderror">

                                <input type="hidden" name="buyer_member_id" :value="form.buyerId ?? ''">

                                <div x-show="dropdownOpen" x-cloak
                                    class="absolute z-50 mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg overflow-hidden">
                                    <div class="max-h-56 overflow-y-auto custom-scrollbar" @wheel.stop @touchmove.stop>
                                        <template x-for="m in filteredMembers(form.buyerSearch)" :key="'buyer-' + m.id">
                                            <button type="button"
                                                class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                                @click="selectBuyer(m)" x-text="m.label"></button>
                                        </template>

                                        <div x-show="filteredMembers(form.buyerSearch).length === 0"
                                            class="px-3 py-2 text-xs text-text-muted">
                                            Member tidak ditemukan.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @error('buyer_member_id')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-text-muted">
                                Paket double/triple: isi peserta tambahan di bawah.
                            </p>
                        </div>

                        {{-- PAKET --}}
                        <div class="space-y-2">
                            <x-ui.label for="paket_id_create">Paket Membership<span
                                    class="text-danger">*</span></x-ui.label>

                            <select id="paket_id_create" name="paket_id" required x-model="form.paketId"
                                @change="onPaketChange()"
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
                        </div>

                        {{-- PESERTA TAMBAHAN --}}
                        <div class="space-y-2">
                            <x-ui.label>Peserta Tambahan (khusus paket double / triple)</x-ui.label>

                            {{-- Peserta 1 --}}
                            <template x-if="maxAdditional() >= 1">
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-semibold text-text-muted">Peserta 1
                                        (tambahan)</label>

                                    <div class="relative" @click.outside="p1Open = false">
                                        <input type="text" x-model="participant.p1Search" @focus="p1Open = true"
                                            @input="p1Open = true" placeholder="Cari nama / username member..."
                                            autocomplete="off"
                                            class="w-full rounded-xl border bg-brand-shell text-xs text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">

                                        <input type="hidden" name="participant_ids[]" :value="participant.p1Id ?? ''">

                                        <div x-show="p1Open" x-cloak
                                            class="absolute z-50 mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg overflow-hidden">
                                            <div class="max-h-56 overflow-y-auto custom-scrollbar" @wheel.stop
                                                @touchmove.stop>
                                                <template x-for="m in filteredMembers(participant.p1Search)"
                                                    :key="'p1-' + m.id">
                                                    <button type="button" :disabled="isDisabledParticipant(m.id, 1)"
                                                        @click="selectP1(m)"
                                                        class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50
                                                               disabled:opacity-40 disabled:cursor-not-allowed"
                                                        x-text="m.label"></button>
                                                </template>
                                                <div x-show="filteredMembers(participant.p1Search).length === 0"
                                                    class="px-3 py-2 text-xs text-text-muted">
                                                    Member tidak ditemukan.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Peserta 2 --}}
                            <template x-if="maxAdditional() >= 2">
                                <div class="space-y-1 mt-2">
                                    <label class="block text-[11px] font-semibold text-text-muted">Peserta 2
                                        (tambahan)</label>

                                    <div class="relative" @click.outside="p2Open = false">
                                        <input type="text" x-model="participant.p2Search" @focus="p2Open = true"
                                            @input="p2Open = true" placeholder="Cari nama / username member..."
                                            autocomplete="off"
                                            class="w-full rounded-xl border bg-brand-shell text-xs text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">

                                        <input type="hidden" name="participant_ids[]"
                                            :value="participant.p2Id ?? ''">

                                        <div x-show="p2Open" x-cloak
                                            class="absolute z-50 mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg overflow-hidden">
                                            <div class="max-h-56 overflow-y-auto custom-scrollbar" @wheel.stop
                                                @touchmove.stop>
                                                <template x-for="m in filteredMembers(participant.p2Search)"
                                                    :key="'p2-' + m.id">
                                                    <button type="button" :disabled="isDisabledParticipant(m.id, 2)"
                                                        @click="selectP2(m)"
                                                        class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50
                                                               disabled:opacity-40 disabled:cursor-not-allowed"
                                                        x-text="m.label"></button>
                                                </template>
                                                <div x-show="filteredMembers(participant.p2Search).length === 0"
                                                    class="px-3 py-2 text-xs text-text-muted">
                                                    Member tidak ditemukan.
                                                </div>
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
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('tanggal_transaksi') border-danger ring-danger-soft @enderror">

                            @error('tanggal_transaksi')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- METODE PEMBAYARAN --}}
                        <div>
                            <x-ui.label for="metode_pembayaran_create">Metode Pembayaran<span
                                    class="text-danger">*</span></x-ui.label>
                            <select id="metode_pembayaran_create" name="metode_pembayaran" required
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('metode_pembayaran') border-danger ring-danger-soft @enderror">
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

                        {{-- KETERANGAN --}}
                        <div>
                            <x-ui.label for="keterangan_create">Keterangan (opsional)</x-ui.label>
                            <textarea id="keterangan_create" name="keterangan" rows="4"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('keterangan') border-danger ring-danger-soft @enderror"
                                placeholder="Contoh: Pembayaran tunai, promo, catatan khusus.">{{ old('keterangan') }}</textarea>

                            @error('keterangan')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft/70 mt-2">
                    <x-ui.button-secondary type="button" @click="openCreate = false">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>

    <script>
        function trxMembershipPembayaranModal() {
            return {
                members: @js($memberOptions),
                paketMeta: @js($paketMeta),

                form: {
                    buyerSearch: @js($oldBuyerLabel),
                    buyerId: @js($oldBuyerId),
                    paketId: @js(old('paket_id')),
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

                init() {
                    this.sanitizeParticipantsForPackage();
                },

                closeAll() {
                    this.dropdownOpen = false;
                    this.p1Open = false;
                    this.p2Open = false;
                },

                filteredMembers(text) {
                    const q = (text || '').toLowerCase().trim();
                    if (!q) return this.members;
                    return this.members.filter(m => (m.label || '').toLowerCase().includes(q));
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

                onPaketChange() {
                    this.sanitizeParticipantsForPackage();
                },

                sanitizeParticipantsForPackage() {
                    const max = this.maxAdditional();

                    if (max === 0) {
                        this.participant.p1Id = null;
                        this.participant.p1Search = '';
                        this.participant.p2Id = null;
                        this.participant.p2Search = '';
                        this.p1Open = false;
                        this.p2Open = false;
                        return;
                    }

                    if (max === 1) {
                        this.participant.p2Id = null;
                        this.participant.p2Search = '';
                        this.p2Open = false;
                    }

                    if (this.form.buyerId) {
                        if (String(this.participant.p1Id) === String(this.form.buyerId)) {
                            this.participant.p1Id = null;
                            this.participant.p1Search = '';
                        }
                        if (String(this.participant.p2Id) === String(this.form.buyerId)) {
                            this.participant.p2Id = null;
                            this.participant.p2Search = '';
                        }
                    }

                    if (this.participant.p1Id && this.participant.p2Id &&
                        String(this.participant.p1Id) === String(this.participant.p2Id)) {
                        this.participant.p2Id = null;
                        this.participant.p2Search = '';
                    }
                },

                selectBuyer(m) {
                    this.form.buyerId = m.id;
                    this.form.buyerSearch = m.label;
                    this.dropdownOpen = false;

                    if (String(this.participant.p1Id) === String(m.id)) {
                        this.participant.p1Id = null;
                        this.participant.p1Search = '';
                    }
                    if (String(this.participant.p2Id) === String(m.id)) {
                        this.participant.p2Id = null;
                        this.participant.p2Search = '';
                    }
                },

                isDisabledParticipant(memberId, slot) {
                    if (this.form.buyerId && String(memberId) === String(this.form.buyerId)) return true;
                    if (slot === 1 && this.participant.p2Id && String(memberId) === String(this.participant.p2Id))
                        return true;
                    if (slot === 2 && this.participant.p1Id && String(memberId) === String(this.participant.p1Id))
                        return true;
                    return false;
                },

                selectP1(m) {
                    if (this.isDisabledParticipant(m.id, 1)) return;
                    this.participant.p1Id = m.id;
                    this.participant.p1Search = m.label;
                    this.p1Open = false;

                    if (String(this.participant.p2Id) === String(m.id)) {
                        this.participant.p2Id = null;
                        this.participant.p2Search = '';
                    }
                },

                selectP2(m) {
                    if (this.isDisabledParticipant(m.id, 2)) return;
                    this.participant.p2Id = m.id;
                    this.participant.p2Search = m.label;
                    this.p2Open = false;

                    if (String(this.participant.p1Id) === String(m.id)) {
                        this.participant.p1Id = null;
                        this.participant.p1Search = '';
                    }
                },
            }
        }
    </script>
</div>

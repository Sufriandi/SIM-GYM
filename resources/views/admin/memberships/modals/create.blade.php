{{-- resources/views/admin/memberships/modals/create.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    // opsi member untuk text info dan select
    $memberOptions = $members
        ->map(function ($m) {
            return [
                'id' => $m->id,
                'label' => trim($m->user?->name . ($m->user?->username ? ' (' . $m->user->username . ')' : '')),
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

    $metodeOptions = [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];

    // untuk isi ulang select anggota tambahan
    $oldGroupIds = old('group_member_ids', []);
    $oldGroup1 = $oldGroupIds[0] ?? null;
    $oldGroup2 = $oldGroupIds[1] ?? null;

    // meta paket untuk Alpine (cek tipe single/double/triple)
    $paketMeta = $paketList
        ->map(
            fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'tipe' => $p->tipe,
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
        
            create: {
                memberSearch: @js($oldMemberLabel),
                memberId: @js($oldMemberId),
                paketId: @js(old('paket_id')),
                tanggalTransaksi: @js($defaultTanggalTransaksi),
                metodePembayaran: @js(old('metode_pembayaran', 'cash')),
                keterangan: @js(old('keterangan')),
            },
        
            dropdownOpen: false,
        
            // helper untuk cari tipe paket
            getSelectedPackageType() {
                if (!this.create.paketId) return null;
                const id = String(this.create.paketId);
                const p = this.paketMeta.find(pk => String(pk.id) === id);
                return p ? p.tipe : null;
            },
        
            matchMember(m) {
                if (!this.create.memberSearch) return true;
                return m.label.toLowerCase().includes(this.create.memberSearch.toLowerCase());
            },
        
            selectMember(m) {
                this.create.memberId = m.id;
                this.create.memberSearch = m.label;
                this.dropdownOpen = false;
            },
        }">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Transaksi Membership</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Catat penjualan membership dan sistem akan otomatis memperpanjang masa aktif member.
                </p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.memberships.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- KOLOM KIRI --}}
                    <div class="space-y-4">
                        {{-- MEMBER UTAMA (SEARCHABLE) --}}
                        <div class="space-y-2">
                            <x-ui.label for="member_search_create">Member utama</x-ui.label>
                            <div class="relative">
                                <input type="text" id="member_search_create" x-model="create.memberSearch"
                                    @focus="dropdownOpen = true" @input="dropdownOpen = true"
                                    placeholder="Cari nama / username member..." autocomplete="off"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                <input type="hidden" name="member_id" :value="create.memberId ?? ''">

                                {{-- DROPDOWN MEMBER --}}
                                <div x-show="dropdownOpen" x-cloak
                                    class="absolute z-50 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg custom-scrollbar">
                                    <template x-for="m in members" :key="m.id">
                                        <button type="button" x-show="matchMember(m)"
                                            class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                            @click="selectMember(m)" x-text="m.label"></button>
                                    </template>
                                    <div x-show="members.filter(m => matchMember(m)).length === 0"
                                        class="px-3 py-2 text-xs text-text-muted">
                                        Member tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-text-muted">
                                Wajib pilih 1 member utama. Untuk paket double/triple, anggota tambahan diisi di bawah.
                            </p>
                        </div>

                        {{-- PAKET MEMBERSHIP --}}
                        <div class="space-y-2">
                            <x-ui.label for="paket_id_create">Paket Membership</x-ui.label>
                            <select id="paket_id_create" name="paket_id" required
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                x-model="create.paketId">
                                <option value="">-- Pilih Paket --</option>
                                @foreach ($paketList as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('paket_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }} ({{ ucfirst($p->tipe) }}, {{ $p->durasi }} hari)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ANGGOTA TAMBAHAN (DINAMIS) --}}
                        <div class="space-y-2">
                            <x-ui.label>Anggota tambahan (khusus paket double / triple)</x-ui.label>

                            {{-- DOUBLE / TRIPLE: MEMBER 1 --}}
                            <div x-show="getSelectedPackageType() === 'double' || getSelectedPackageType() === 'triple'"
                                x-cloak>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">
                                    Member 1 (anggota tambahan)
                                </label>
                                <select name="group_member_ids[]"
                                    class="w-full rounded-xl border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                    <option value="">-- Pilih Member --</option>
                                    @foreach ($members as $m)
                                        <option value="{{ $m->id }}"
                                            @if ($oldGroup1 == $m->id) selected @endif>
                                            {{ $m->user?->name }}{{ $m->user?->username ? ' (' . $m->user->username . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- TRIPLE: MEMBER 2 --}}
                            <div x-show="getSelectedPackageType() === 'triple'" x-cloak class="mt-2">
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">
                                    Member 2 (anggota tambahan)
                                </label>
                                <select name="group_member_ids[]"
                                    class="w-full rounded-xl border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                    <option value="">-- Pilih Member --</option>
                                    @foreach ($members as $m)
                                        <option value="{{ $m->id }}"
                                            @if ($oldGroup2 == $m->id) selected @endif>
                                            {{ $m->user?->name }}{{ $m->user?->username ? ' (' . $m->user->username . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <p class="text-[11px] text-text-muted">
                                Untuk paket <strong>single</strong> bagian ini otomatis tidak dipakai.
                                Sistem akan memvalidasi agar anggota tambahan tidak sama dengan member utama
                                dan tidak ganda satu sama lain.
                            </p>
                        </div>
                    </div>

                    {{-- KOLOM KANAN --}}
                    <div class="space-y-4">
                        {{-- TANGGAL & WAKTU TRANSAKSI --}}
                        <div>
                            <x-ui.label for="tanggal_transaksi_create">
                                Tanggal & Waktu Transaksi
                            </x-ui.label>
                            <input type="datetime-local" id="tanggal_transaksi_create" name="tanggal_transaksi"
                                value="{{ $defaultTanggalTransaksi }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                            <p class="text-[11px] text-text-muted mt-1">
                                Default: waktu sekarang. Tanggal ini juga bisa dijadikan dasar perhitungan
                                masa aktif membership (tanggal_mulai).
                            </p>
                        </div>

                        {{-- METODE PEMBAYARAN --}}
                        <div>
                            <x-ui.label for="metode_pembayaran_create">Metode Pembayaran</x-ui.label>
                            <select id="metode_pembayaran_create" name="metode_pembayaran" required
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                @foreach ($metodeOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('metode_pembayaran', 'cash') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- KETERANGAN --}}
                        <div>
                            <x-ui.label for="keterangan_create">Keterangan (opsional)</x-ui.label>
                            <textarea id="keterangan_create" name="keterangan" rows="4"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                placeholder="Contoh: Pembayaran tunai di kasir, promo akhir tahun.">{{ old('keterangan') }}</textarea>
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

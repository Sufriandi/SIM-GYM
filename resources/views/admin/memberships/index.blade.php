@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $pageTitle = $pageTitle ?? 'Penjualan Membership';

    // modal create otomatis terbuka jika ada error validasi (hanya POST, belum ada PUT)
    $openCreateOnLoad = $errors->any() ? 'true' : 'false';

    $today = Carbon::today();

    $metodeOptions = [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Catat transaksi membership dan pantau status masa aktif member.">

    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        openDetailId: null,
        openEditId: null,
    
        // filter frontend
        searchTerm: '',
        statusFilter: 'all',
        metodeFilter: 'all',
    }"
        x-effect="
            const main  = document.querySelector('main');
            const html  = document.documentElement;
            const body  = document.body;
            const locked = openCreate || openDetailId || openEditId;

            const targets = [html, body, main].filter(Boolean);

            if (locked) {
                targets.forEach((el) => {
                    if (el.dataset.prevOverflowY === undefined) {
                        el.dataset.prevOverflowY = el.style.overflowY || '';
                    }
                    el.style.overflowY = 'hidden';
                });
            } else {
                targets.forEach((el) => {
                    if (el.dataset.prevOverflowY !== undefined) {
                        el.style.overflowY = el.dataset.prevOverflowY;
                        delete el.dataset.prevOverflowY;
                    } else {
                        el.style.removeProperty('overflow-y');
                    }
                });
            }
        ">
        {{-- HEADER --}}
        <x-ui.section-header :title="$pageTitle"
            subtitle="Setiap transaksi akan menambah atau memperpanjang masa aktif membership member." />
        <hr class="border-t border-brand-borderSoft mb-6">

        {{-- SEARCH + FILTER + ACTION --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- SEARCH + FILTER (frontend only) --}}
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <div
                    class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm h-[42px]">
                    <div class="pl-4 text-text-muted">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>

                    <input type="text" x-model="searchTerm" placeholder="Cari member / paket membership..."
                        class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-2 pl-3 pr-2 rounded-l-full">

                    <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                    {{-- BUTTON FILTER --}}
                    <button type="button" @click="showFilter = !showFilter"
                        class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-text-muted hover:text-text-main mr-1 rounded-full hover:bg-brand-surface-50">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Filter</span>
                    </button>
                </div>

                {{-- FILTER DROPDOWN (status + metode, purely frontend) --}}
                <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                    class="absolute top-[48px] left-0 w-full bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5 z-10">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                            <h4 class="text-sm font-semibold text-text-main">Filter</h4>
                            <button type="button" class="text-xs text-danger hover:underline"
                                @click="
                                        statusFilter = 'all';
                                        metodeFilter = 'all';
                                    ">
                                Reset
                            </button>
                        </div>

                        {{-- STATUS --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                                Status membership
                            </label>
                            <select x-model="statusFilter"
                                class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2">
                                <option value="all">Semua status</option>
                                <option value="upcoming">Belum aktif</option>
                                <option value="active">Aktif</option>
                                <option value="expired">Expired</option>
                                <option value="canceled">Dibatalkan</option>
                            </select>
                        </div>

                        {{-- METODE PEMBAYARAN --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                                Metode pembayaran
                            </label>
                            <select x-model="metodeFilter"
                                class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2">
                                <option value="all">Semua metode</option>
                                @foreach ($metodeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center gap-2">
                <x-ui.button-primary type="button" @click="openCreate = true">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i>
                    Tambah Membership
                </x-ui.button-primary>
            </div>
        </div>

        {{-- CARD TABEL TRANSAKSI --}}
        <x-ui.card class="border-brand-borderSoft ">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Transaksi Membership</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        {{ $memberships->total() }} transaksi yang tercatat.
                    </p>
                </div>

                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        Halaman {{ $memberships->currentPage() }} dari {{ $memberships->lastPage() }}
                    </span>
                </div>
            </div>

            {{-- WRAPPER TABEL TRANSAKSI --}}
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[800px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- NO --}}
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">
                                No
                            </th>

                            {{-- MEMBER --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[24%]">
                                Member
                            </th>

                            {{-- PAKET (HANYA NAMA) --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[20%]">
                                Paket
                            </th>

                            {{-- BERLAKU SAMPAI --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted whitespace-nowrap w-[16%]">
                                Berlaku Sampai
                            </th>

                            {{-- METODE (HANYA METODE, TANPA TANGGAL) --}}
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[10%]">
                                Metode
                            </th>

                            {{-- STATUS --}}
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[10%]">
                                Status
                            </th>

                            {{-- KETERANGAN (HANYA DI LAYAR LEBAR) --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted hidden xl:table-cell w-[18%]">
                                Keterangan
                            </th>

                            {{-- DETAIL --}}
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted whitespace-nowrap w-[8%]">
                                Detail
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($memberships as $membership)
                            @php
                                $member = $membership->member;
                                $paket = $membership->paket;

                                $mulai = $membership->tanggal_mulai ? Carbon::parse($membership->tanggal_mulai) : null;
                                $akhir = $membership->tanggal_akhir ? Carbon::parse($membership->tanggal_akhir) : null;
                                $canceledAt = $membership->canceled_at ? Carbon::parse($membership->canceled_at) : null;

                                if ($canceledAt) {
                                    $statusKey = 'canceled';
                                    $statusLabel = 'Dibatalkan';
                                    $statusVariant = 'danger';
                                } elseif ($mulai && $today->lt($mulai)) {
                                    $statusKey = 'upcoming';
                                    $statusLabel = 'Belum Aktif';
                                    $statusVariant = 'info';
                                } elseif ($mulai && $akhir && $today->between($mulai, $akhir)) {
                                    $statusKey = 'active';
                                    $statusLabel = 'Aktif';
                                    $statusVariant = 'success';
                                } elseif ($akhir && $today->gt($akhir)) {
                                    $statusKey = 'expired';
                                    $statusLabel = 'Expired';
                                    $statusVariant = 'warning';
                                } else {
                                    $statusKey = 'unknown';
                                    $statusLabel = 'Tidak Diketahui';
                                    $statusVariant = 'neutral';
                                }

                                $akhirText = $akhir ? $akhir->format('d M Y') : '—';

                                $groupNames = $membership->groupMembers
                                    ->map(fn($gm) => $gm->member?->nama)
                                    ->filter()
                                    ->values();

                                $metodeLabel =
                                    $metodeOptions[$membership->metode_pembayaran] ??
                                    Str::title($membership->metode_pembayaran);
                            @endphp

                            <tr x-data="{
                                memberName: @js($member->nama ?? ''),
                                memberUsername: @js($member->user->username ?? ''),
                                paketName: @js($paket->nama ?? ''),
                                status: '{{ $statusKey }}',
                                metode: '{{ $membership->metode_pembayaran }}',
                            }"
                                x-show="
                        (!searchTerm
                            || memberName.toLowerCase().includes(searchTerm.toLowerCase())
                            || memberUsername.toLowerCase().includes(searchTerm.toLowerCase())
                            || paketName.toLowerCase().includes(searchTerm.toLowerCase()))
&& (statusFilter === 'all' || statusFilter === status)
                        && (metodeFilter === 'all' || metodeFilter === metode)
                    "
                                class="hover:bg-brand-surface-50 transition-colors duration-150">
                                {{-- NO --}}
                                <td class="p-3 text-center align-middle text-xs text-text-muted">
                                    {{ $loop->iteration + ($memberships->currentPage() - 1) * $memberships->perPage() }}
                                </td>

                                {{-- MEMBER + ANGGOTA TAMBAHAN --}}
                                <td class="p-3 text-left align-middle">
                                    <div
                                        class="text-sm font-semibold {{ $member ? 'text-text-main' : 'text-danger italic' }}
                                max-w-[230px] md:max-w-[260px] truncate">
                                        {{ $member->user->name ?? '[Member dihapus]' }}
                                    </div>

                                    @if ($groupNames->isNotEmpty())
                                        <div class="mt-1 text-[11px] text-text-muted">
                                            Anggota tambahan: {{ $groupNames->join(', ') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- PAKET (HANYA NAMA) --}}
                                <td class="p-3 text-left align-middle">
                                    <span class="text-sm font-semibold text-text-main max-w-[220px] line-clamp-1">
                                        {{ $paket->nama ?? '[Paket dihapus]' }}
                                    </span>
                                </td>

                                {{-- BERLAKU SAMPAI --}}
                                <td class="p-3 text-left align-middle whitespace-nowrap">
                                    <div class="text-sm text-text-main">
                                        {{ $akhirText }}
                                    </div>
                                </td>

                                {{-- METODE PEMBAYARAN (TANPA TANGGAL) --}}
                                <td class="p-3 text-center align-middle">
                                    <x-ui.badge variant="neutral">
                                        {{ $metodeLabel }}
                                    </x-ui.badge>
                                </td>

                                {{-- STATUS --}}
                                <td class="p-3 text-center align-middle">
                                    <x-ui.badge :variant="$statusVariant">
                                        {{ $statusLabel }}
                                    </x-ui.badge>

                                    @if ($canceledAt)
                                        <div class="mt-1 text-[11px] text-text-muted">
                                            {{ $canceledAt->format('d M Y H:i') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- KETERANGAN (DISSEMBUNYIKAN DI LAYAR KECIL) --}}
                                <td class="p-3 text-left align-middle hidden xl:table-cell">
                                    <div class="text-xs text-text-muted max-w-[260px] truncate">
                                        {{ $membership->keterangan ? Str::limit($membership->keterangan, 80) : '—' }}
                                    </div>
                                </td>

                                {{-- DETAIL (SEPERTI RIWAYAT IZIN LATIHAN) --}}
                                <td class="px-3 py-4 text-center align-middle">
                                    <button type="button"
                                        class="inline-flex items-center justify-center gap-1 text-gold-600 hover:text-gold-500 font-semibold text-xs md:text-sm transition-colors whitespace-nowrap"
                                        @click="openDetailId = {{ $membership->id }}">
                                        <span>Lihat Detail</span>
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-text-muted italic">
                                    Belum ada transaksi membership yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $memberships->onEachSide(1)->links() }}
            </div>
        </x-ui.card>

        {{-- MODAL CREATE --}}
        @include('admin.memberships.modals.create', [
            'members' => $members,
            'paketList' => $paketList,
        ])

        {{-- MODAL DETAIL & EDIT (SATU PER MEMBERSHIP) --}}
        @foreach ($memberships as $membership)
            @include('admin.memberships.modals.detail', [
                'membership' => $membership,
                'metodeOptions' => $metodeOptions,
                'today' => $today,
            ])

            @include('admin.memberships.modals.edit', [
                'membership' => $membership,
                'members' => $members,
                'paketList' => $paketList,
                'metodeOptions' => $metodeOptions,
            ])
        @endforeach

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    </div>

    {{-- SCRIPT KONFIRMASI HAPUS TRANSAKSI --}}
    <script>
        function confirmDeleteMembership(id, memberName, paketName) {
            const formId = 'delete-membership-' + id;

            if (typeof Swal === 'undefined') {
                if (confirm(
                        'Yakin ingin menghapus transaksi membership "' + paketName +
                        '" untuk member ' + memberName + ' ?'
                    )) {
                    document.getElementById(formId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Transaksi?',
                text: 'Anda yakin ingin menghapus transaksi membership "' + paketName +
                    '" untuk member ' + memberName +
                    '? Jika transaksi sudah aktif / kadaluarsa, sistem akan menolak penghapusan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
</x-layouts.admin>

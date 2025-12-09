{{-- resources/views/admin/memberships/index.blade.php --}}
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
    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="bg-primary-soft border border-primary text-primary-dark px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-danger-soft border border-danger text-danger px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        searchTerm: '',
        statusFilter: 'all',
        metodeFilter: 'all',
    }">
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
        <x-ui.card class="border-brand-borderSoft">
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

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[5%]">
                                No
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[24%]">
                                Member
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[18%]">
                                Paket
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[15%]">
                                Periode
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[13%]">
                                Metode & Tanggal
                            </th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[9%]">
                                Status
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[18%]">
                                Keterangan
                            </th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[8%]">
                                Aksi
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

                                $periodeText =
                                    $mulai && $akhir ? $mulai->format('d M Y') . ' – ' . $akhir->format('d M Y') : '—';

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
                                               max-w-[230px] md:max-w-[280px] truncate">
                                        {{ $member->nama ?? '[Member dihapus]' }}
                                    </div>

                                    @if ($groupNames->isNotEmpty())
                                        <div class="mt-1 text-[11px] text-text-muted">
                                            Anggota tambahan: {{ $groupNames->join(', ') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- PAKET --}}
                                <td class="p-3 text-left align-middle">
                                    <div class="text-sm font-semibold text-text-main max-w-[210px] truncate">
                                        {{ $paket->nama ?? '[Paket dihapus]' }}
                                    </div>

                                    @if ($paket)
                                        <div class="mt-1 flex items-center gap-2">
                                            @php
                                                $badgeVariant =
                                                    $paket->tipe === 'single'
                                                        ? 'primary'
                                                        : ($paket->tipe === 'double'
                                                            ? 'success'
                                                            : 'warning');
                                            @endphp

                                            <x-ui.badge :variant="$badgeVariant">
                                                {{ Str::ucfirst($paket->tipe) }}
                                            </x-ui.badge>
                                        </div>
                                    @endif
                                </td>

                                {{-- PERIODE --}}
                                <td class="p-3 text-left align-middle">
                                    <div class="text-sm text-text-main">
                                        {{ $periodeText }}
                                    </div>
                                </td>

                                {{-- METODE + TANGGAL TRANSAKSI --}}
                                <td class="p-3 text-left align-middle">
                                    <div class="mb-1">
                                        <x-ui.badge variant="neutral">
                                            {{ $metodeLabel }}
                                        </x-ui.badge>
                                    </div>
                                    <div class="text-[11px] text-text-muted">
                                        {{ Carbon::parse($membership->tanggal_transaksi)->format('d M Y H:i') }}
                                    </div>
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

                                {{-- KETERANGAN --}}
                                <td class="p-3 text-left align-middle">
                                    <div class="text-xs text-text-muted max-w-[260px] truncate">
                                        {{ $membership->keterangan ? Str::limit($membership->keterangan, 80) : '—' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="px-3 py-4 align-middle w-[8%] min-w-[110px]">
                                    <div class="flex items-center justify-center gap-4 h-full">
                                        {{-- DETAIL --}}
                                        <a href="{{ route('admin.memberships.show', $membership) }}"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150"
                                            title="Detail">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </a>

                                        {{-- HAPUS --}}
                                        <form id="delete-membership-{{ $membership->id }}"
                                            action="{{ route('admin.memberships.destroy', $membership) }}"
                                            method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                class="relative group p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors duration-150"
                                                title="Hapus"
                                                onclick="confirmDeleteMembership(
                                                    {{ $membership->id }},
                                                    @js($member->nama ?? '[Member dihapus]'),
                                                    @js($paket->nama ?? '[Paket dihapus]')
                                                )">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                <span
                                                    class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                           text-[10px] font-medium text-danger
                                                           opacity-0 group-hover:opacity-100
                                                           transition-opacity duration-150">
                                                    Hapus
                                                </span>
                                            </button>
                                        </form>
                                    </div>
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

        {{-- MODAL CREATE (TERPISAH DI FILE LAIN) --}}
        @include('admin.memberships.modals.create', [
            'members' => $members,
            'paketList' => $paketList,
            'openCreateOnLoad' => $openCreateOnLoad,
        ])

        <style>
            [x-cloak] {
                display: none !important;
            }

            .custom-scrollbar::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: #F5E6D6;
                border-radius: 999px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #D4A757;
                border-radius: 999px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #A67C39;
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

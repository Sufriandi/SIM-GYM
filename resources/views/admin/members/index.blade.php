{{-- resources/views/admin/members/index.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $pageTitle = $pageTitle ?? 'Daftar Member';
    $search = request('search');

    $openCreateOnLoad = $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false';
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Kelola data member yang terdaftar di BETA GYM.">
    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="mb-4 bg-primary-soft border border-primary text-primary-dark px-4 py-3 rounded">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-danger-soft border border-danger text-danger px-4 py-3 rounded">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        openEditId: null,
        openDetailId: null,
        search: '{{ $search }}',
    }"
        x-effect="
            const main = document.querySelector('main');
            const html = document.documentElement;
            const body = document.body;
            const locked = openCreate || !!openEditId || !!openDetailId;
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
        <x-ui.section-header :title="$pageTitle" subtitle="Daftar member aktif dan histori keanggotaannya." />
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- SEARCH + ADD --}}
        <div class="mt-6 mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="relative w-full max-w-md">
                <form action="{{ route('admin.members.index') }}" method="GET" id="searchForm">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input type="text" id="searchInput" name="search" x-model="search"
                            value="{{ $search }}" placeholder="Cari nama, username, atau no. HP..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                                   placeholder:text-text-muted/50 py-2 pl-3 pr-4 rounded-r-full
                                   focus:ring-0 focus:outline-none focus-visible:outline-none"
                            autocomplete="off">

                        <button type="submit" class="hidden">Cari</button>
                    </div>
                </form>
            </div>

            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i>
                Tambah Member
            </x-ui.button-primary>
        </div>

        {{-- CARD TABEL MEMBER --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Member</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Semua member yang terdaftar dalam sistem.
                        @if ($search)
                            <span class="font-semibold text-gold-700">
                                &nbsp;Hasil untuk "{{ $search }}" ({{ $members->total() }} member)
                            </span>
                        @endif
                    </p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $members->total() }} Member
                    </span>
                </div>
            </div>

            <div class="w-full">
                <table class="table-fixed w-full border-collapse text-xs md:text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[4%] min-w-[32px]">
                                No</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[8%] min-w-[70px]">
                                Foto</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[22%] min-w-[140px]">
                                Nama</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%] min-w-[120px]">
                                Username</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%] min-w-[120px]">
                                No. HP</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[18%] min-w-[110px]">
                                Status</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%] min-w-[120px]">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($members as $member)
                            @php
                                $user = $member->user;
                                $displayName = $user->name ?? '-';

                                $foto = !empty($user?->foto)
                                    ? Storage::url($user->foto)
                                    : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';

                                // STATUS dari tanggal_mulai/tanggal_akhir
                                $today = Carbon::today();

                                $mulaiRaw = $member->tanggal_mulai;
                                $akhirRaw = $member->tanggal_akhir;

                                if (empty($mulaiRaw) && empty($akhirRaw)) {
                                    // belum ada periode => dianggap belum aktif (sesuai requirement)
                                    $statusKey = 'belum_aktif';
                                } elseif (!empty($mulaiRaw) && !empty($akhirRaw)) {
                                    $mulai = Carbon::parse($mulaiRaw)->startOfDay();
                                    $akhir = Carbon::parse($akhirRaw)->endOfDay();

                                    if ($today->betweenIncluded($mulai, $akhir)) {
                                        $statusKey = 'aktif';
                                    } elseif ($today->gt($akhir)) {
                                        $statusKey = 'expired';
                                    } else {
                                        // $today < $mulai
                                        $statusKey = 'belum_aktif';
                                    }
                                } else {
                                    // kasus data tidak lengkap (salah satu null) -> tetap masuk belum_aktif agar tidak muncul status ke-4
                                    $statusKey = 'belum_aktif';
                                }

                                $statusVariant = match ($statusKey) {
                                    'aktif' => 'success',
                                    'expired' => 'danger',
                                    'belum_aktif' => 'warning',
                                    default => 'neutral',
                                };

                                $statusLabel = Str::upper(str_replace('_', ' ', $statusKey));

                            @endphp

                            <tr x-show="
                                    !search
                                    || @js(strtolower($displayName)).includes(search.toLowerCase())
                                    || @js(strtolower($user->username ?? '')).includes(search.toLowerCase())
                                    || @js(strtolower($user->no_hp ?? '')).includes(search.toLowerCase())
                                "
                                class="hover:bg-brand-surface-50 transition-colors duration-150">
                                <td class="p-3 align-middle text-sm font-medium text-text-main">
                                    {{ $loop->iteration + ($members->currentPage() - 1) * $members->perPage() }}
                                </td>

                                <td class="p-3 align-middle">
                                    <img src="{{ $foto }}" alt="Foto {{ $displayName }}"
                                        class="w-11 h-11 rounded-lg object-cover border border-brand-borderSoft shadow-sm"
                                        onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';">
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main max-w-[180px] truncate">
                                        {{ $displayName }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm text-text-main max-w-[140px] truncate">
                                        {{ $user->username ?? '-' }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm text-text-main max-w-[140px] truncate">
                                        {{ $user->no_hp ?? '-' }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <x-ui.badge :variant="$statusVariant">
                                        {{ $statusLabel }}
                                    </x-ui.badge>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="flex items-center justify-center gap-3 h-full">
                                        <button type="button" @click="openDetailId = {{ $member->id }}"
                                            title="Detail Member"
                                            class="relative group p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors duration-150">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-medium text-info opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                Detail
                                            </span>
                                        </button>

                                        <button type="button" @click="openEditId = {{ $member->id }}"
                                            title="Edit Member"
                                            class="relative group p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-medium text-yellow-600 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                Edit
                                            </span>
                                        </button>

                                        <form id="delete-member-{{ $member->id }}"
                                            action="{{ route('admin.members.destroy', $member) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus Member"
                                                class="relative group p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors duration-150"
                                                onclick="confirmDeleteMember({{ $member->id }}, @js($displayName))">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                <span
                                                    class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-medium text-danger opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                    Hapus
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    Belum ada data member.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $members->appends(['search' => $search])->links() }}
            </div>
        </x-ui.card>

        {{-- MODALS --}}
        @include('admin.members.modals.create')

        @foreach ($members as $member)
            @include('admin.members.modals.detail', ['member' => $member])
            @include('admin.members.modals.edit', ['member' => $member])
        @endforeach

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

    <script>
        function confirmDeleteMember(memberId, memberName) {
            if (typeof Swal === 'undefined') {
                if (confirm('Yakin ingin menghapus member ' + memberName + '?')) {
                    document.getElementById('delete-member-' + memberId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Member?',
                text: 'Anda yakin ingin menghapus data member ' + memberName +
                    '? Tindakan ini tidak dapat dibatalkan.',
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
                    document.getElementById('delete-member-' + memberId).submit();
                }
            });
        }
    </script>
</x-layouts.admin>

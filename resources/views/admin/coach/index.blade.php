{{-- resources/views/admin/coach/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Daftar Coach';

    // Buka modal create otomatis jika ada error dan bukan request PUT
    $openCreateOnLoad = $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false';

    $search = request('search');
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Kelola data coach yang terdaftar di BETA GYM.">
    {{-- STATE UTAMA --}}
    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        openDetailId: null,
        openEditId: null,
        search: @js($search ?? ''),
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

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header :title="$pageTitle" subtitle="Daftar coach aktif dan pengelolaan datanya." />

        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- BARIS PENCARIAN + TOMBOL TAMBAH --}}
        <div class="mt-6 mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- SEARCH BAR --}}
            <div class="relative w-full max-w-md">
                <form action="{{ route('admin.coaches.index') }}" method="GET">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm focus-within:ring-2 focus-within:ring-primary-dark/40 transition-all hover:border-brand-borderSoft/80 h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input type="text" name="search" x-model="search" value="{{ $search }}"
                            placeholder="Cari nama coach..."
                            class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-2 pl-3 pr-4 rounded-r-full focus:outline-none focus-visible:outline-none"
                            autocomplete="off">

                        <button type="submit" class="hidden">Cari</button>
                    </div>
                </form>
            </div>

            {{-- TOMBOL TAMBAH COACH --}}
            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Coach
            </x-ui.button-primary>
        </div>

        {{-- CARD TABEL COACH --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            {{-- HEADER CARD --}}
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Coach</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Semua coach yang terdaftar dalam sistem.
                        @if ($search)
                            <span class="font-semibold text-gold-700">
                                &nbsp;Hasil untuk "{{ $search }}" ({{ $coaches->total() }})
                            </span>
                        @endif
                    </p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $coaches->total() }} Coach
                    </span>
                </div>
            </div>

            {{-- WRAPPER TABEL SCROLLABLE --}}
            <div class="w-full overflow-x-auto overflow-y-hidden custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- NO --}}
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[5%]">
                                No
                            </th>

                            {{-- FOTO --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[10%]">
                                Foto
                            </th>

                            {{-- NAMA --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[20%]">
                                Nama
                            </th>

                            {{-- NO HP --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[15%]">
                                No. HP
                            </th>

                            {{-- ALAMAT --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[20%]">
                                Alamat
                            </th>

                            {{-- DESKRIPSI --}}
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[20%]">
                                Deskripsi
                            </th>

                            {{-- AKSI --}}
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[10%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($coaches as $coach)
                            @php
                                $currentFotoPath = $coach->foto ?? null;
                                $currentFotoUrl = $currentFotoPath
                                    ? Storage::url($currentFotoPath)
                                    : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150 h-16">
                                {{-- NO --}}
                                <td class="p-3 align-middle text-center text-text-muted text-sm font-semibold">
                                    {{ $loop->iteration + ($coaches->currentPage() - 1) * $coaches->perPage() }}
                                </td>

                                {{-- FOTO (KOTAK/ROUNDED-LG) --}}
                                <td class="p-3 align-middle">
                                    <div
                                        class="w-12 h-12 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 overflow-hidden shrink-0">
                                        <img src="{{ $currentFotoUrl }}" alt="Foto {{ $coach->nama }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';">
                                    </div>
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main line-clamp-1"
                                        title="{{ $coach->nama }}">
                                        {{ $coach->nama }}
                                    </div>
                                </td>

                                {{-- NO HP --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm text-text-main truncate">
                                        {{ $coach->no_hp }}
                                    </div>
                                </td>

                                {{-- ALAMAT --}}
                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted line-clamp-2" title="{{ $coach->alamat }}">
                                        {{ \Illuminate\Support\Str::limit($coach->alamat, 60) }}
                                    </div>
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted line-clamp-2" title="{{ $coach->deskripsi }}">
                                        {{ $coach->deskripsi ? \Illuminate\Support\Str::limit($coach->deskripsi, 60) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- DETAIL --}}
                                        <button type="button" @click="openDetailId = {{ $coach->id }}"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors"
                                            title="Detail Coach">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        {{-- EDIT --}}
                                        <button type="button" @click="openEditId = {{ $coach->id }}"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors"
                                            title="Edit Coach">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        {{-- HAPUS --}}
                                        <form id="delete-coach-{{ $coach->id }}"
                                            action="{{ route('admin.coaches.destroy', $coach) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors"
                                                title="Hapus Coach"
                                                onclick="confirmDeleteCoach({{ $coach->id }}, '{{ $coach->nama }}')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    Belum ada data coach yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $coaches->appends(['search' => $search])->links() }}
            </div>
        </x-ui.card>

        {{-- MODAL CREATE --}}
        @include('admin.coach.modals.create')

        {{-- MODAL DETAIL + EDIT PER COACH (Looping di luar tabel agar aman/popup) --}}
        @foreach ($coaches as $coach)
            @include('admin.coach.modals.detail', ['coach' => $coach])
            @include('admin.coach.modals.edit', ['coach' => $coach])
        @endforeach

        {{-- CUSTOM SCROLLBAR + X-CLOAK --}}
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

    {{-- SCRIPT KONFIRMASI HAPUS --}}
    <script>
        function confirmDeleteCoach(coachId, coachName) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin menghapus coach ${coachName}?`)) {
                    document.getElementById('delete-coach-' + coachId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Coach?',
                html: `Anda yakin ingin menghapus data coach <strong>${coachName}</strong>?<br>Tindakan ini tidak dapat dibatalkan.`,
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
                    document.getElementById('delete-coach-' + coachId).submit();
                }
            });
        }
    </script>
</x-layouts.admin>

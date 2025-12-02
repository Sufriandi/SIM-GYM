{{-- resources/views/admin/coach/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    $pageTitle = $pageTitle ?? 'Daftar Coach';

    // Buka modal create otomatis jika ada error dan bukan request PUT
    $openCreateOnLoad = ($errors->any() && old('_method') !== 'PUT') ? 'true' : 'false';

    $search = request('search'); // nilai pencarian saat ini
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Kelola data coach yang terdaftar di BETA GYM."
>
    {{-- STATE UTAMA UNTUK MODAL & SEARCH --}}
    <div
        x-data="{
            openCreate: {{ $openCreateOnLoad }},
            openDetailId: null,
            openEditId: null,
            search: '{{ request('search') }}',
        }"
    >
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar coach aktif dan pengelolaan datanya."
        />

        {{-- GARIS DIBAWAH JUDUL --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- BARIS PENCARIAN + TOMBOL TAMBAH (layout mengikuti inventaris, tanpa filter) --}}
        <div class="mt-6 mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- SEARCH ala Inventaris, hanya teks pencarian --}}
            <div class="relative w-full max-w-md">
                <form action="{{ route('admin.coaches.index') }}" method="GET" id="searchForm">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]"
                    >
                        {{-- Icon Search --}}
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        {{-- Input Text --}}
                        <input
                            type="text"
                            id="searchInput"
                            name="search"
                            x-model="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama coach..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                                   placeholder:text-text-muted/50 py-2 pl-3 pr-4 rounded-r-full
                                   focus:ring-0 focus:outline-none focus-visible:outline-none"
                            autocomplete="off"
                        >

                        {{-- Hidden submit biar Enter tetap jalan (optional) --}}
                        <button type="submit" class="hidden">Cari</button>
                    </div>
                </form>
            </div>

            {{-- TOMBOL TAMBAH COACH --}}
            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Coach
            </x-ui.button-primary>
        </div>

        {{-- CARD TABEL COACH (layout mirip inventaris) --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            {{-- HEADER CARD --}}
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Coach</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Semua coach yang terdaftar dalam sistem.
                    </p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $coaches->total() }} Coach
                    </span>
                </div>
            </div>

            {{-- WRAPPER TABEL: tanpa overflow internal supaya tidak ada scrollbar di dalam card --}}
            <div class="w-full">
                <table class="table-fixed w-full border-collapse text-xs md:text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[2%] min-w-[10px]">
                                No
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[8%] min-w-[80px]">
                                Foto
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[18%] min-w-[150px]">
                                Nama
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%] min-w-[120px]">
                                No. HP
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%] min-w-[150px]">
                                Alamat
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[27%] min-w-[150px]">
                                Deskripsi
                            </th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%] min-w-[120px]">
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

                            <tr
                                x-show="
                                    !search
                                    || @js(strtolower($coach->nama)).startsWith(search.toLowerCase())
                                "
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                            >
                                {{-- NO --}}
                                <td class="p-3 align-middle text-sm font-medium text-text-main w-[2%]">
                                    {{ $loop->iteration + ($coaches->currentPage() - 1) * $coaches->perPage() }}
                                </td>

                                {{-- FOTO --}}
                                <td class="p-3 align-middle w-[8%] min-w-[80px]">
                                    <img
                                        src="{{ $currentFotoUrl }}"
                                        alt="Foto {{ $coach->nama }}"
                                        class="w-12 h-12 rounded-lg object-cover border border-brand-borderSoft shadow-sm"
                                        onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';"
                                    >
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-middle w-[18%] min-w-[150px]">
                                    <div class="text-sm font-semibold text-text-main max-w-[120px] truncate">
                                        {{ $coach->nama }}
                                    </div>
                                </td>

                                {{-- NO HP --}}
                                <td class="p-3 align-middle w-[15%] min-w-[120px]">
                                    <div class="text-sm text-text-main max-w-[160px] truncate">
                                        {{ $coach->no_hp }}
                                    </div>
                                </td>

                                {{-- ALAMAT --}}
                                <td class="p-3 align-middle w-[20%] min-w-[150px]">
                                    <div class="text-xs text-text-muted max-w-[160px] truncate">
                                        {{ \Illuminate\Support\Str::limit($coach->alamat, 80) }}
                                    </div>
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-middle w-[27%] min-w-[150px]">
                                    <div class="text-xs text-text-muted max-w-[180px] truncate">
                                        {{ $coach->deskripsi ? \Illuminate\Support\Str::limit($coach->deskripsi, 80) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle w-[12%] min-w-[120px]">
                                    <div class="flex items-center justify-center gap-3 mr-7">
                                        {{-- DETAIL --}}
                                        <button
                                            type="button"
                                            @click="openDetailId = {{ $coach->id }}"
                                            title="Detail Coach"
                                            class="relative group p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors duration-150"
                                        >
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-info
                                                       opacity-0 group-hover:opacity-100
                                                       transition-opacity duration-150"
                                            >
                                                Detail
                                            </span>
                                        </button>

                                        {{-- EDIT --}}
                                        <button
                                            type="button"
                                            @click="openEditId = {{ $coach->id }}"
                                            title="Edit Coach"
                                            class="relative group p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150"
                                        >
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-yellow-600
                                                       opacity-0 group-hover:opacity-100
                                                       transition-opacity duration-150"
                                            >
                                                Edit
                                            </span>
                                        </button>

                                        {{-- HAPUS --}}
                                        <form
                                            id="delete-coach-{{ $coach->id }}"
                                            action="{{ route('admin.coaches.destroy', $coach) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                title="Hapus Coach"
                                                class="relative group p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors duration-150"
                                                onclick="confirmDeleteCoach({{ $coach->id }}, '{{ $coach->nama }}')"
                                            >
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                <span
                                                    class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                           text-[10px] font-medium text-danger
                                                           opacity-0 group-hover:opacity-100
                                                           transition-opacity duration-150"
                                                >
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

        {{-- MODAL DETAIL + EDIT PER COACH --}}
        @foreach ($coaches as $coach)
            @include('admin.coach.modals.detail', ['coach' => $coach])
            @include('admin.coach.modals.edit', ['coach' => $coach])
        @endforeach

        {{-- CUSTOM SCROLLBAR + X-CLOAK --}}
        <style>
            [x-cloak] { display: none !important; }

            .custom-scrollbar::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: #F5E6D6; /* brand.shell */
                border-radius: 999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #D4A757; /* gold-500 */
                border-radius: 999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #A67C39; /* gold-700 */
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
                text: `Anda yakin ingin menghapus data coach ${coachName}? Tindakan ini tidak dapat dibatalkan.`,
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

        // Live search pakai x-model + x-show (filter di frontend),
        // tidak ada debounce / reload halaman.
    </script>
</x-layouts.admin>

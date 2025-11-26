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
    {{-- FLASH MESSAGE (opsional, kalau dipakai di controller) --}}
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

    {{-- STATE UTAMA UNTUK MODAL CREATE & Search --}}
    <div x-data="{ 
        openCreate: {{ $openCreateOnLoad }},
        search: '{{ request('search') }}'
     }">

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar coach aktif dan pengelolaan datanya."
        >
        </x-ui.section-header>

        {{-- GARIS DIBAWAH JUDUL (SAMA KAYA PRODUK) --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

{{-- BARIS: SEARCH + TOMBOL TAMBAH --}}
<div class="mt-6 mb-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">

    {{-- SEARCH SECTION - ENHANCED & COMPACT --}}
    <div class="w-full md:w-auto md:flex-1 md:max-w-md">
        
        {{-- LABEL WITH GRADIENT - MORE COMPACT --}}
        <div class="flex items-center gap-1.5 mb-1.5">
            <div class="p-1 bg-gradient-to-br from-gold-500 to-gold-600 rounded-md shadow-sm">
                <i data-lucide="search" class="w-3 h-3 text-white"></i>
            </div>
            <label class="text-[10px] font-bold tracking-wider text-text-main uppercase">
                Cari Coach
            </label>
        </div>

        <form id="searchForm" action="{{ route('admin.coaches.index') }}" method="GET">
            
            {{-- SEARCH INPUT CONTAINER --}}
            <div class="relative">
                
                {{-- MAIN INPUT WRAPPER - REFINED --}}
                <div class="relative flex items-center bg-brand-shell/80 backdrop-blur-sm
                            px-3.5 py-2.5 rounded-xl border border-brand-borderSoft
                            shadow-sm hover:shadow-md
                            focus-within:border-gold-500 focus-within:shadow-lg focus-within:ring-2 focus-within:ring-gold-500/20
                            transition-all duration-200 ease-out
                            overflow-hidden">

                    {{-- ANIMATED SEARCH ICON --}}
                    <div class="flex-shrink-0 w-4 h-4 text-text-muted 
                                transition-all duration-200">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>

                    {{-- INPUT FIELD - NO OVERFLOW --}}
                    <input type="text"
                        name="search"
                        x-model="search"
                        placeholder="Cari nama atau no. HP..."
                        value="{{ $search }}"
                        class="flex-1 ml-2.5 text-sm font-medium text-text-main placeholder:text-text-muted/50
                               bg-transparent border-none focus:outline-none focus:ring-0
                               transition-all duration-200 pr-2"
                        style="border:none;background:transparent;box-shadow:none;max-width:100%;">

                    {{-- CLEAR BUTTON (when typing) --}}
                    @if ($search)
                        <button type="button"
                                onclick="document.querySelector('input[name=search]').value=''; document.getElementById('searchForm').submit();"
                                class="flex-shrink-0 ml-1.5 p-1 rounded-full bg-danger/10 hover:bg-danger/20
                                       text-danger transition-all duration-150
                                       hover:scale-110 active:scale-95">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif

                    {{-- SUBMIT BUTTON - COMPACT --}}
                    <button type="submit"
                            class="flex-shrink-0 ml-2 px-3 py-1 rounded-lg
                                   bg-gradient-to-r from-gold-500 to-gold-600
                                   text-white text-[10px] font-bold uppercase tracking-wide
                                   shadow-sm hover:shadow-md hover:from-gold-600 hover:to-gold-700
                                   transform hover:scale-105 active:scale-95
                                   transition-all duration-150">
                        Cari
                    </button>
                </div>
            </div>

            {{-- SEARCH INFO / RESULTS COUNT - MORE REFINED --}}
            @if ($search)
            <div class="mt-2 flex items-center gap-2 px-3 py-1.5 bg-gold-50/80 dark:bg-gold-500/5 
                        rounded-lg border border-gold-200/50 dark:border-gold-500/20">
                
                {{-- INFO ICON --}}
                <div class="flex-shrink-0 w-3.5 h-3.5 text-gold-600">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                </div>
                
                {{-- TEXT - COMPACT --}}
                <p class="text-[11px] text-gold-800 dark:text-gold-400 flex-1">
                    Hasil untuk <span class="font-bold">"{{ $search }}"</span>
                    — 
                    <span class="font-bold">{{ $coaches->total() }}</span> coach
                </p>

                {{-- RESET LINK - SMALLER --}}
                <a href="{{ route('admin.coaches.index') }}"
                   class="text-[10px] font-semibold text-gold-600 hover:text-gold-700
                          underline decoration-dotted hover:decoration-solid transition whitespace-nowrap">
                    Tampilkan Semua
                </a>
            </div>
            @endif

        </form>
    </div>

    {{-- BUTTON TAMBAH --}}
    <div>
        <x-ui.button-primary type="button" @click="openCreate = true">
            <i data-lucide="plus" class="w-5 h-5 mr-1"></i>
            Tambah Coach
        </x-ui.button-primary>
    </div>
</div>

        {{-- CARD TABEL COACH --}}
        <x-ui.card
            title="Daftar Coach"
            subtitle="Semua coach yang terdaftar dalam sistem."
            class="border-brand-borderSoft"
        >
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[900px]">
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

                            {{-- STATE KHUSUS PER-BARIS UNTUK MODAL EDIT, DETAIL & PREVIEW FOTO --}}
                            <tr
                                 x-show="
                                    !search 
                                    || @js(strtolower($coach->nama)).includes(search.toLowerCase())
                                    || @js(strtolower($coach->no_hp)).includes(search.toLowerCase())
                                    || @js(strtolower($coach->alamat)).includes(search.toLowerCase())
                                    || @js(strtolower($coach->deskripsi ?? '')).includes(search.toLowerCase())
                                "
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-data="{ openEdit: false, openDetail: false, imageUrl: '{{ $currentFotoUrl }}' }"
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
                                    <div class="text-sm font-semibold text-text-main max-w-[100px] truncate">
                                        {{ $coach->nama }}
                                    </div>
                                </td>

                                {{-- NO HP --}}
                                <td class="p-3 align-middle w-[15%] min-w-[120px]">
                                    <div class="text-sm text-text-main truncate">
                                        {{ $coach->no_hp }}
                                    </div>
                                </td>

                                {{-- ALAMAT --}}
                                <td class="p-3 align-middle w-[20%] min-w-[150px]">
                                    <div class="text-xs text-text-muted max-w-[150px] truncate">
                                        {{ \Illuminate\Support\Str::limit($coach->alamat, 80) }}
                                    </div>
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-middle w-[27%] min-w-[150px]">
                                    <div class="text-xs text-text-muted max-w-[150px] truncate">
                                        {{ $coach->deskripsi ? \Illuminate\Support\Str::limit($coach->deskripsi, 80) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI (IKON) --}}
                                <td class="p-3 align-middle w-[12%] min-w-[120px]">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- DETAIL ICON - biru soft --}}
                                        <button
                                            type="button"
                                            @click="openDetail = true"
                                            title="Detail Coach"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150"
                                        >
                                            <i data-lucide="eye" class="w-6 h-6"></i>
                                        </button>

                                        {{-- EDIT ICON --}}
                                        <button
                                            type="button"
                                            @click="openEdit = true"
                                            title="Edit Coach"
                                            class="p-2 rounded-full text-primary-dark hover:bg-primary-soft/50 transition-colors duration-150"
                                        >
                                            <i data-lucide="square-pen" class="w-6 h-6"></i>
                                        </button>

                                        {{-- HAPUS ICON --}}
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
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150"
                                                onclick="confirmDeleteCoach({{ $coach->id }}, '{{ $coach->nama }}')"
                                            >
                                                <i data-lucide="trash-2" class="w-6 h-6"></i>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- ======================= --}}
                                    {{-- MODAL EDIT DATA COACH   --}}
                                    {{-- ======================= --}}
                                    <div
                                        x-show="openEdit"
                                        x-cloak
                                        x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
                                    >
                                        <div
                                            @click.away="openEdit = false"
                                            class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
                                        >
                                            {{-- HEADER MODAL --}}
                                            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                                                <div>
                                                    <h2 class="text-xl font-semibold text-text-main">
                                                        Edit Coach
                                                    </h2>
                                                    <p class="text-sm text-text-muted mt-0.5">
                                                        {{ $coach->nama }}
                                                    </p>
                                                </div>
                                                <button type="button"
                                                        class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                                                        @click="openEdit = false">
                                                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                                                </button>
                                            </div>

                                            {{-- ISI MODAL --}}
                                            <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.coaches.update', $coach) }}"
                                                    enctype="multipart/form-data"
                                                    class="space-y-5"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                        {{-- PANEL KIRI: FOTO + INFO --}}
                                                        <div class="md:col-span-1">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                                                <div class="w-24 h-24 rounded-full border-2 border-dashed border-brand-borderSoft overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                                                    <img
                                                                        :src="imageUrl"
                                                                        alt="Foto {{ $coach->nama }}"
                                                                        class="object-cover w-full h-full"
                                                                        :style="{ display: imageUrl.includes('No+Foto') ? 'none' : 'block' }"
                                                                    >
                                                                    <span
                                                                        x-show="imageUrl.includes('No+Foto')"
                                                                        class="text-xs text-text-muted text-center p-2"
                                                                    >
                                                                        No Foto
                                                                    </span>
                                                                </div>

                                                                <div class="text-center">
                                                                    <p class="text-sm font-semibold text-text-main">
                                                                        {{ $coach->nama }}
                                                                    </p>
                                                                    <p class="text-[11px] text-text-muted">
                                                                        {{ $coach->no_hp }}
                                                                    </p>
                                                                </div>

                                                                <p class="text-[11px] text-text-muted text-center">
                                                                    Perbarui data profil coach di form sebelah kanan.
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- PANEL KANAN: FORM --}}
                                                        <div class="md:col-span-2">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    {{-- NAMA --}}
                                                                    <div>
                                                                        <x-ui.label for="nama_{{ $coach->id }}">Nama Coach</x-ui.label>
                                                                        <input
                                                                            type="text"
                                                                            id="nama_{{ $coach->id }}"
                                                                            name="nama"
                                                                            value="{{ old('nama', $coach->nama) }}"
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                            required
                                                                        >
                                                                    </div>

                                                                    {{-- NO HP --}}
                                                                    <div>
                                                                        <x-ui.label for="no_hp_{{ $coach->id }}">Nomor HP</x-ui.label>
                                                                        <input
                                                                            type="text"
                                                                            id="no_hp_{{ $coach->id }}"
                                                                            name="no_hp"
                                                                            value="{{ old('no_hp', $coach->no_hp) }}"
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                            required
                                                                        >
                                                                    </div>
                                                                </div>

                                                                {{-- ALAMAT --}}
                                                                <div>
                                                                    <x-ui.label for="alamat_{{ $coach->id }}">Alamat</x-ui.label>
                                                                    <textarea
                                                                        id="alamat_{{ $coach->id }}"
                                                                        name="alamat"
                                                                        rows="3"
                                                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                        required
                                                                    >{{ old('alamat', $coach->alamat) }}</textarea>
                                                                </div>

                                                                {{-- DESKRIPSI --}}
                                                                <div>
                                                                    <x-ui.label for="deskripsi_{{ $coach->id }}">Deskripsi / Keahlian</x-ui.label>
                                                                    <textarea
                                                                        id="deskripsi_{{ $coach->id }}"
                                                                        name="deskripsi"
                                                                        rows="3"
                                                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                    >{{ old('deskripsi', $coach->deskripsi) }}</textarea>
                                                                </div>

                                                                {{-- FOTO --}}
                                                                <div class="space-y-2">
                                                                    <x-ui.label for="foto_{{ $coach->id }}">Foto Profil (opsional)</x-ui.label>

                                                                    <input
                                                                        type="file"
                                                                        id="foto_{{ $coach->id }}"
                                                                        name="foto"
                                                                        accept="image/*"
                                                                        class="block w-full text-sm text-text-main
                                                                               file:mr-4 file:py-2 file:px-4
                                                                               file:rounded-full file:border-0
                                                                               file:text-sm file:font-semibold
                                                                               file:bg-gold-600 file:text-white
                                                                               hover:file:bg-gold-700"
                                                                        @change="
                                                                            const file = $event.target.files[0];
                                                                            if (file) {
                                                                                const reader = new FileReader();
                                                                                reader.onload = (e) => { imageUrl = e.target.result; };
                                                                                reader.readAsDataURL(file);
                                                                            } else {
                                                                                imageUrl = '{{ $currentFotoUrl }}';
                                                                            }
                                                                        "
                                                                    >
                                                                    <p class="text-[11px] text-text-muted">
                                                                        Maksimal 2MB. Jika diisi, foto lama akan diganti.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-end gap-2 pt-3">
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

                                   {{-- MODAL DETAIL DATA COACH --}}
                                    <div
                                        x-show="openDetail"
                                        x-cloak
                                        x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
                                    >
                                        <div
                                            @click.away="openDetail = false"
                                            class="relative w-full max-w-2xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
                                        >
                                            {{-- HEADER --}}
                                            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                                                <div>
                                                    <h2 class="text-xl font-semibold text-text-main">Detail Coach</h2>
                                                    <p class="text-sm text-text-muted mt-0.5">
                                                        Informasi lengkap mengenai profil coach.
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="openDetail = false"
                                                    class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                                                >
                                                    <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
                                                </button>
                                            </div>

                                            {{-- ISI MODAL --}}
                                            <div class="px-6 pb-6 pt-4 space-y-5 max-h-[80vh] overflow-y-auto custom-scrollbar">
                                                {{-- FOTO + NAMA --}}
                                                <div class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-brand-surface-50 border border-brand-borderSoft">
                                                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-brand-borderSoft shadow">
                                                        <img
                                                            src="{{ $currentFotoUrl }}"
                                                            alt="Foto {{ $coach->nama }}"
                                                            class="w-full h-full object-cover"
                                                        >
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-base font-semibold text-text-main">
                                                            {{ $coach->nama }}
                                                        </p>
                                                        <p class="text-xs text-text-muted mt-1">
                                                            Nomor HP: {{ $coach->no_hp }}
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- KONTAK + ALAMAT --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {{-- KONTAK --}}
                                                    <div class="p-4 rounded-2xl bg-brand-surface-50 border border-brand-borderSoft">
                                                        <h3 class="text-sm font-semibold text-text-main mb-2">Kontak</h3>
                                                        <p class="text-xs text-text-muted mb-1">Nomor HP:</p>
                                                        <p class="text-sm text-text-main break-words">
                                                            {{ $coach->no_hp }}
                                                        </p>
                                                    </div>

                                                    {{-- ALAMAT --}}
                                                    <div class="p-4 rounded-2xl bg-brand-surface-50 border border-brand-borderSoft">
                                                        <h3 class="text-sm font-semibold text-text-main mb-2">Alamat</h3>
                                                        <p
                                                            class="text-sm text-text-main whitespace-pre-line break-words max-h-32 overflow-y-auto custom-scrollbar"
                                                        >
                                                            {{ $coach->alamat }}
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- DESKRIPSI / KEAHLIAN --}}
                                                <div class="p-4 rounded-2xl bg-brand-surface-50 border border-brand-borderSoft">
                                                    <h3 class="text-sm font-semibold text-text-main mb-2">Deskripsi / Keahlian</h3>
                                                    <p
                                                        class="text-sm text-text-main whitespace-pre-line break-words max-h-40 overflow-y-auto custom-scrollbar"
                                                    >
                                                        {{ $coach->deskripsi ?: '-' }}
                                                    </p>
                                                </div>

                                                <div class="flex justify-end pt-2">
                                                    <x-ui.button-secondary type="button" @click="openDetail = false">
                                                        Tutup
                                                    </x-ui.button-secondary>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-text-muted italic">
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

        {{-- ======================== --}}
        {{-- MODAL TAMBAH COACH      --}}
        {{-- ======================== --}}
        <div
            x-show="openCreate"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openCreate = false"
                class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
                x-data="{ createImageUrl: null }"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">
                            Tambah Coach
                        </h2>
                        <p class="text-sm text-text-muted mt-0.5">
                            Data Coach Baru
                        </p>
                    </div>
                    <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openCreate = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                    <form
                        method="POST"
                        action="{{ route('admin.coaches.store') }}"
                        enctype="multipart/form-data"
                        class="space-y-5"
                    >
                        @csrf

                        {{-- ERROR VALIDASI UNTUK CREATE --}}
                        @if ($errors->any() && old('_method') !== 'PUT')
                            <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                                <p class="text-sm font-semibold">Ada kesalahan input:</p>
                                <ul class="list-disc list-inside text-xs mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- PANEL KIRI: PREVIEW AVATAR --}}
                            <div class="md:col-span-1">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                    <div class="w-24 h-24 rounded-full border-2 border-dashed border-brand-borderSoft overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                        <img
                                            x-show="createImageUrl"
                                            :src="createImageUrl"
                                            alt="Preview Foto Coach"
                                            class="object-cover w-full h-full"
                                        >
                                        <span
                                            x-show="!createImageUrl"
                                            class="text-xs text-text-muted text-center p-2"
                                        >
                                            Preview Foto Coach
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-text-muted text-center">
                                        Foto yang akan diupload.
                                    </p>
                                </div>
                            </div>

                            {{-- PANEL KANAN: FORM --}}
                            <div class="md:col-span-2">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- NAMA --}}
                                        <div>
                                            <x-ui.label for="nama_create">Nama Coach</x-ui.label>
                                            <input
                                                type="text"
                                                id="nama_create"
                                                name="nama"
                                                value="{{ old('nama') }}"
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                required
                                            >
                                        </div>

                                        {{-- NO HP --}}
                                        <div>
                                            <x-ui.label for="no_hp_create">Nomor HP</x-ui.label>
                                            <input
                                                type="text"
                                                id="no_hp_create"
                                                name="no_hp"
                                                value="{{ old('no_hp') }}"
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                required
                                            >
                                        </div>
                                    </div>

                                    {{-- ALAMAT --}}
                                    <div>
                                        <x-ui.label for="alamat_create">Alamat</x-ui.label>
                                        <textarea
                                            id="alamat_create"
                                            name="alamat"
                                            rows="3"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                            required
                                        >{{ old('alamat') }}</textarea>
                                    </div>

                                    {{-- DESKRIPSI --}}
                                    <div>
                                        <x-ui.label for="deskripsi_create">Deskripsi / Keahlian</x-ui.label>
                                        <textarea
                                            id="deskripsi_create"
                                            name="deskripsi"
                                            rows="3"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        >{{ old('deskripsi') }}</textarea>
                                    </div>

                                    {{-- FOTO --}}
                                    <div class="space-y-2">
                                        <x-ui.label for="foto_create">Foto Profil (opsional)</x-ui.label>
                                        <input
                                            type="file"
                                            id="foto_create"
                                            name="foto"
                                            accept="image/*"
                                            class="block w-full text-sm text-text-main
                                                   file:mr-4 file:py-2 file:px-4
                                                   file:rounded-full file:border-0
                                                   file:text-sm file:font-semibold
                                                   file:bg-gold-600 file:text-white
                                                   hover:file:bg-gold-700"
                                            @change="
                                                const file = $event.target.files[0];
                                                if (file) {
                                                    const reader = new FileReader();
                                                    reader.onload = (e) => { createImageUrl = e.target.result; };
                                                    reader.readAsDataURL(file);
                                                } else {
                                                    createImageUrl = null;
                                                }
                                            "
                                        >
                                        <p class="text-[11px] text-text-muted mt-1">
                                            Maksimal 2MB. Format yang didukung: JPG, PNG, dll.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3">
                            <x-ui.button-secondary type="button" @click="openCreate = false">
                                Batal
                            </x-ui.button-secondary>
                            <x-ui.button-primary type="submit">
                                Simpan Coach
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
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
        </script>

        <script>
    let typingTimer;
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            searchForm.submit();
        }, 300); // delay 300ms biar ga spam request
    });
</script>

        {{-- CUSTOM SCROLLBAR --}}
        <style>
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
</x-layouts.admin>

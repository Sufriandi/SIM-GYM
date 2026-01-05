{{-- resources/views/admin/notifikasi/index.blade.php --}}

@php
    $pageTitle = $pageTitle ?? 'Notifikasi';
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Semua notifikasi yang masuk untuk admin."
>
    <div x-data="{ search: @js($search ?? ''), filter: @js($filter ?? 'all') }">

        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Kelola notifikasi: baca, filter, dan hapus."
        />

        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- Toolbar: Search + Filter + Actions --}}
        <div class="mt-6 mb-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            {{-- Search --}}
            <div class="relative w-full max-w-md">
                <form action="{{ route('admin.notifikasi.index') }}" method="GET" id="searchForm">
                    <input type="hidden" name="filter" :value="filter">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]"
                    >
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input
                            type="text"
                            name="search"
                            x-model="search"
                            value="{{ $search }}"
                            placeholder="Cari notifikasi..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                                   placeholder:text-text-muted/50 py-2 pl-3 pr-4 rounded-r-full
                                   focus:ring-0 focus:outline-none focus-visible:outline-none"
                            autocomplete="off"
                        >

                        <button type="submit" class="hidden">Cari</button>
                    </div>
                </form>
            </div>

            <div class="flex items-center gap-2 flex-wrap justify-end">

                {{-- Filter --}}
                <form action="{{ route('admin.notifikasi.index') }}" method="GET">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <select
                        name="filter"
                        class="h-[42px] px-4 rounded-full bg-brand-card border border-brand-borderSoft
                               text-sm text-text-main shadow-sm"
                        onchange="this.form.submit()"
                    >
                        <option value="all" {{ ($filter ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="unread" {{ ($filter ?? 'all') === 'unread' ? 'selected' : '' }}>Belum dibaca</option>
                    </select>
                </form>

                {{-- Mark all read --}}
                <form action="{{ route('admin.notifikasi.read_all') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="h-[42px] px-4 rounded-full border border-brand-borderSoft bg-brand-card shadow-sm
                               hover:bg-brand-gunmetal/10 transition-colors text-sm font-semibold text-text-main"
                    >
                        <i data-lucide="check-check" class="w-4 h-4 inline-block mr-2"></i>
                        Tandai semua dibaca
                    </button>
                </form>

                {{-- Hapus semua (Swal confirm) --}}
                <form id="delete-all-notif" action="{{ route('admin.notifikasi.hide_all') }}" method="POST" class="inline-block">
                    @csrf
                    <button
                        type="button"
                        class="h-[42px] px-4 rounded-full border border-danger/30 bg-danger-soft/40 shadow-sm
                               hover:bg-danger-soft/60 transition-colors text-sm font-semibold text-danger"
                        onclick="confirmDeleteAllNotif({{ (int)($notifications->total() ?? 0) }})"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4 inline-block mr-2"></i>
                        Hapus semua
                    </button>
                </form>
            </div>
        </div>

        {{-- Card list --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Notifikasi</h3>
                    <p class="text-xs text-text-muted mt-0.5">Klik item untuk membuka detail terkait.</p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $notifications->total() }} item
                    </span>
                </div>
            </div>

            <div class="divide-y divide-brand-borderSoft/80">
                @forelse($notifications as $n)
                    @php
                        $isRead = in_array($n->id, $readIds ?? [], true);
                        $time = $n->created_at ? $n->created_at->format('d-m-Y H:i') : '-';
                        $title = $n->title ?? 'Notifikasi';
                        $body  = $n->body ?? '-';
                    @endphp

                    <div class="px-6 py-4 hover:bg-brand-surface-50 transition-colors">
                        <div class="flex items-start gap-3">
                            {{-- dot --}}
                            <div class="mt-2 flex-shrink-0">
                                <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $isRead ? 'bg-brand-borderSoft' : 'bg-accent-500' }}"></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <a
                                        href="{{ route('admin.notifikasi.go', $n->id) }}"
                                        class="text-sm font-bold text-text-main hover:text-gold-400 transition-colors truncate"
                                        title="Buka notifikasi"
                                    >
                                        {{ $title }}
                                    </a>
                                    <div class="text-[11px] text-text-muted flex-shrink-0">
                                        {{ $time }}
                                    </div>
                                </div>

                                <div class="text-xs text-text-muted mt-1 line-clamp-2">
                                    {{ $body }}
                                </div>

                                <div class="mt-3 flex items-center gap-2 flex-wrap">
                                    @if(!$isRead)
                                        <form action="{{ route('admin.notifikasi.read_one', $n->id) }}" method="POST">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="px-3 py-1.5 rounded-full text-xs font-semibold
                                                       bg-brand-card border border-brand-borderSoft shadow-sm
                                                       hover:bg-brand-gunmetal/10 transition-colors"
                                            >
                                                Tandai dibaca
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Hapus (Swal confirm) --}}
                                    <form
                                        id="delete-notif-{{ $n->id }}"
                                        action="{{ route('admin.notifikasi.hide_one', $n->id) }}"
                                        method="POST"
                                        class="inline-block"
                                    >
                                        @csrf
                                        <button
                                            type="button"
                                            class="px-3 py-1.5 rounded-full text-xs font-semibold
                                                   text-danger border border-danger/30 bg-danger-soft/40
                                                   hover:bg-danger-soft/60 transition-colors"
                                            onclick="confirmDeleteNotif({{ $n->id }}, @js($title))"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4 inline-block mr-1"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>

                @empty
                    <div class="p-10 text-center">
                        <i data-lucide="bell-off" class="w-10 h-10 text-text-muted mx-auto mb-3"></i>
                        <p class="text-sm text-text-muted">Belum ada notifikasi.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6 px-6 pb-6">
                {{ $notifications->links() }}
            </div>
        </x-ui.card>

        <style>
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        </style>
    </div>

    {{-- SCRIPT KONFIRMASI HAPUS (SWEETALERT: style sama seperti coach) --}}
    <script>
        function confirmDeleteNotif(notifId, notifTitle) {
            const formId = 'delete-notif-' + notifId;
            const formEl = document.getElementById(formId);
            if (!formEl) return;

            if (typeof Swal === 'undefined') {
                if (confirm(`Hapus notifikasi ini?\n\n${notifTitle}`)) formEl.submit();
                return;
            }

            Swal.fire({
                title: 'Hapus Notifikasi?',
                text: `Anda yakin ingin menghapus notifikasi "${notifTitle}"? Tindakan ini tidak dapat dibatalkan.`,
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
                    formEl.submit();
                }
            });
        }

        function confirmDeleteAllNotif(total) {
            const formEl = document.getElementById('delete-all-notif');
            if (!formEl) return;

            if (total <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Tidak ada notifikasi',
                        text: 'Tidak ada notifikasi yang bisa dihapus.',
                        icon: 'info',
                        confirmButtonColor: '#6C5A46',
                        background: '#21160F',
                        color: '#F8F2E7',
                    });
                }
                return;
            }

            if (typeof Swal === 'undefined') {
                if (confirm(`Hapus semua notifikasi (${total} item)?`)) formEl.submit();
                return;
            }

            Swal.fire({
                title: 'Hapus Semua Notifikasi?',
                text: `Anda yakin ingin menghapus semua notifikasi (${total} item)? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) {
                    formEl.submit();
                }
            });
        }
    </script>
</x-layouts.admin>

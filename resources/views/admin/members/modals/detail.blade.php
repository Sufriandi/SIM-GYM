{{-- resources/views/admin/members/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    // Set Bahasa Indonesia
    Carbon::setLocale('id');

    $user = $member->user;

    $displayName = $user?->name ?? '-';
    $foto = !empty($user?->foto)
        ? Storage::url($user->foto)
        : 'https://placehold.co/200x200/3A2D2A/F5E6D6?text=No+Foto';

    $tglDaftar = $member->tanggal_daftar ? Carbon::parse($member->tanggal_daftar)->translatedFormat('d F Y') : '-';

    // AMBIL DATA TRANSAKSI AKTIF
    $activeTx = $member->active_membership_transaction;

    $durasiMulai = $activeTx ? Carbon::parse($activeTx->tanggal_mulai)->translatedFormat('d F Y') : '-';
    $durasiAkhir = $activeTx ? Carbon::parse($activeTx->tanggal_akhir)->translatedFormat('d F Y') : '-';

    $statusKey = $member->status_membership ?? 'belum_aktif';
    $statusVariant = match ($statusKey) {
        'aktif' => 'success',
        'expired' => 'danger',
        'belum_aktif' => 'warning',
        'default' => 'neutral',
    };
    $statusLabel = Str::upper(str_replace('_', ' ', $statusKey));
    $paketAktifNama = $member->nama_paket_aktif ?? null;

    $jenisKelamin = !empty($user?->jenis_kelamin) ? ucfirst($user->jenis_kelamin) : '-';

    $alamatRaw = $user?->alamat ?? '';
    $alamatTrimmed = trim($alamatRaw);
    $alamatHtml = $alamatTrimmed !== '' ? nl2br(e($alamatTrimmed)) : '-';
@endphp

<style>
    /* CSS untuk menyembunyikan scrollbar tapi tetap bisa di-scroll */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div x-show="openDetailId === {{ $member->id }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null" @keydown.escape.window="openDetailId = null">

    <div
        class="relative w-full max-w-5xl max-h-[90vh] overflow-y-auto hide-scrollbar rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        {{-- HEADER --}}
        <div
            class="sticky top-0 z-20 bg-brand-shell/95 backdrop-blur-md flex items-center justify-between px-6 py-4 border-b border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Data diambil dari tabel <b>users</b> & <b>members</b>.
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                {{-- KIRI: INFO --}}
                <div class="lg:col-span-8 space-y-4">

                    {{-- SECTION MEMBERSHIP --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-text-main">Ringkasan Membership</h3>
                                <p class="text-xs text-text-muted mt-0.5">
                                    Status membership saat ini.
                                </p>
                            </div>
                            <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4 text-sm">
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Tanggal Daftar
                                </p>
                                <p class="text-base text-text-main font-medium">{{ $tglDaftar }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Paket Aktif</p>
                                <p class="text-base text-text-main">
                                    @if ($paketAktifNama)
                                        <span class="font-semibold text-gold-700">{{ $paketAktifNama }}</span>
                                    @else
                                        <span class="text-text-muted italic">—</span>
                                    @endif
                                </p>
                            </div>

                            <div
                                class="min-w-0 sm:col-span-2 border-t border-brand-borderSoft/50 pt-3 mt-1 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Mulai</p>
                                    <p
                                        class="text-sm {{ $activeTx ? 'text-text-main font-medium' : 'text-text-muted italic' }}">
                                        {{ $durasiMulai }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Berakhir</p>
                                    <p
                                        class="text-sm {{ $activeTx ? 'text-text-main font-medium' : 'text-text-muted italic' }}">
                                        {{ $durasiAkhir }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION PROFIL --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-sm font-semibold text-text-main">Profil User</h3>

                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4 text-sm">
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Nama</p>
                                <p class="text-base font-semibold text-text-main truncate">{{ $displayName }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Jenis Kelamin</p>
                                <p class="text-base text-text-main">{{ $jenisKelamin }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Username</p>
                                <p class="text-base text-text-main">{{ $user?->username ?? '-' }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">No. HP / Email</p>
                                <div class="flex flex-col">
                                    <span class="text-text-main">{{ $user?->no_hp ?? '-' }}</span>
                                    <span class="text-xs text-text-muted truncate">{{ $user?->email ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t border-brand-borderSoft/70">
                            <p class="text-[11px] text-text-muted mb-1">Alamat</p>
                            <p class="text-sm text-text-main leading-relaxed">
                                {!! $alamatHtml !!}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KANAN: FOTO --}}
                {{-- PERBAIKAN: sticky top-24 agar foto diam di atas saat discroll, z-0 agar dibawah header --}}
                <div class="lg:col-span-4 sticky top-24 z-0">
                    {{-- PERBAIKAN: h-fit agar tinggi kartu mengikuti konten saja (tidak melar ke bawah) --}}
                    <div
                        class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-6 h-fit flex flex-col items-center text-center">
                        <h3 class="text-sm font-semibold text-text-main self-start w-full text-left mb-4">Foto Profil
                        </h3>

                        <div
                            class="w-32 h-32 rounded-2xl overflow-hidden bg-brand-surface-50 border border-brand-borderSoft shadow-lg mb-4">
                            <img src="{{ $foto }}" alt="Foto {{ $displayName }}"
                                class="w-full h-full object-cover">
                        </div>

                        <div class="space-y-1 mb-6">
                            <p class="text-base font-semibold text-text-main">{{ $displayName }}</p>
                            <p class="text-xs text-text-muted">{{ $user?->username ?? '-' }}</p>
                        </div>

                        <div class="mt-auto w-full">
                            <x-ui.button-secondary type="button" class="w-full justify-center"
                                @click="openDetailId = null">
                                Tutup
                            </x-ui.button-secondary>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

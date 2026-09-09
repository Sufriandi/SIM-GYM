{{-- resources/views/admin/members/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    // Set Bahasa Indonesia
    Carbon::setLocale('id');

    $user = $member->user;

    $displayName = $user?->name ?? '-';
    // Gunakan placeholder ukuran lebih kecil agar sesuai beban loading
    $foto = !empty($user?->foto)
        ? Storage::url($user->foto)
        : 'https://placehold.co/300x300/3A2D2A/F5E6D6?text=No+Foto';

    $tglDaftar = $member->tanggal_daftar ? Carbon::parse($member->tanggal_daftar)->translatedFormat('d F Y') : '-';

    // AMBIL DATA TRANSAKSI AKTIF (Gunakan data pre-joined agar zero-query)
    $durasiMulai = !empty($member->latest_tm_tanggal_mulai)
        ? Carbon::parse($member->latest_tm_tanggal_mulai)->translatedFormat('d F Y')
        : '-';

    $durasiAkhir = !empty($member->latest_tm_tanggal_akhir)
        ? Carbon::parse($member->latest_tm_tanggal_akhir)->translatedFormat('d F Y')
        : '-';

    $hasTx = !empty($member->latest_tm_id) || ($durasiMulai !== '-');

    $statusKey = $member->computed_status ?? $member->status_membership ?? 'belum_aktif';
    $statusVariant = match ($statusKey) {
        'aktif' => 'success',
        'expired' => 'danger',
        'belum_aktif' => 'warning',
        default => 'neutral',
    };
    $statusLabel = Str::upper(str_replace('_', ' ', $statusKey));
    $paketAktifNama = isset($member->computed_status) ? $member->computed_paket : ($member->nama_paket_aktif ?? null);

    $jenisKelamin = !empty($user?->jenis_kelamin) ? ucfirst($user->jenis_kelamin) : '-';

    $alamatRaw = $user?->alamat ?? '';
    $alamatTrimmed = trim($alamatRaw);
    $alamatHtml = $alamatTrimmed !== '' ? nl2br(e($alamatTrimmed)) : '-';
@endphp

<div x-show="openDetailId === {{ $member->id }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null" @keydown.escape.window="openDetailId = null">

    <div
        class="relative w-full max-w-5xl rounded-3xl shadow-2xl border border-brand-borderSoft 
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
                max-h-[90vh] overflow-y-auto custom-scrollbar">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Data diambil dari tabel <b>users</b> & <b>members</b>.
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

                {{-- KIRI: INFO DATA (2/3 lebar) --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- SECTION MEMBERSHIP --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-sm font-semibold text-text-main">Ringkasan Membership</h3>
                                <p class="text-xs text-text-muted mt-0.5">Status membership saat ini.</p>
                            </div>
                            <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Tanggal Daftar
                                </p>
                                <p class="text-base text-text-main font-medium">{{ $tglDaftar }}</p>
                            </div>

                            <div>
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Paket Aktif</p>
                                <p class="text-base text-text-main">
                                    @if ($paketAktifNama)
                                        <span class="font-semibold text-gold-700">{{ $paketAktifNama }}</span>
                                    @else
                                        <span class="text-text-muted italic">—</span>
                                    @endif
                                </p>
                            </div>

                            <div class="sm:col-span-2 border-t border-brand-borderSoft/50 pt-3 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Mulai</p>
                                    <p
                                        class="text-sm {{ $hasTx ? 'text-text-main font-medium' : 'text-text-muted italic' }}">
                                        {{ $durasiMulai }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-text-muted mb-0.5 uppercase tracking-wider">Berakhir</p>
                                    <p
                                        class="text-sm {{ $hasTx ? 'text-text-main font-medium' : 'text-text-muted italic' }}">
                                        {{ $durasiAkhir }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION PROFIL --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-sm font-semibold text-text-main mb-3">Profil User</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase">Nama</p>
                                <p class="text-base font-semibold text-text-main truncate">{{ $displayName }}</p>
                            </div>

                            <div>
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase">Jenis Kelamin</p>
                                <p class="text-base text-text-main">{{ $jenisKelamin }}</p>
                            </div>

                            <div>
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase">Username</p>
                                <p class="text-base text-text-main">{{ $user?->username ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase">No. HP / Email</p>
                                <div class="flex flex-col">
                                    <span class="text-text-main">{{ $user?->no_hp ?? '-' }}</span>
                                    <span class="text-xs text-text-muted truncate">{{ $user?->email ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t border-brand-borderSoft/70">
                            <p class="text-[11px] text-text-muted mb-1 uppercase">Alamat</p>
                            <p class="text-sm text-text-main leading-relaxed">
                                {!! $alamatHtml !!}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KANAN: FOTO (1/3 lebar) --}}
                <div class="space-y-4">
                    {{-- Tambahkan items-center agar foto di tengah --}}
                    <div
                        class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-5 flex flex-col items-center gap-4">
                        <div class="w-full text-left">
                            <h3 class="text-base font-semibold text-text-main">Foto Profil</h3>
                            <p class="text-xs text-text-muted mt-0.5">Tampilan foto profil member.</p>
                        </div>

                        {{-- CONTAINER FOTO: Ukuran fixed (w-56 h-56) agar tidak terlalu besar --}}
                        <div
                            class="w-56 h-56 aspect-square rounded-2xl overflow-hidden bg-brand-surface-50 
                                    flex items-center justify-center shadow-[0_8px_24px_rgba(0,0,0,0.12)] border border-brand-borderSoft">
                            <img src="{{ $foto }}" alt="Foto {{ $displayName }}"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null; this.src='https://placehold.co/300x300/3A2D2A/F5E6D6?text=No+Foto';">
                        </div>

                        {{-- Teks nama & username dibawah foto sudah dihapus --}}
                    </div>
                </div>

            </div>

            {{-- FOOTER TOMBOL TUTUP (Mobile Only) --}}
            <div class="mt-6 flex justify-end lg:hidden">
                <x-ui.button-secondary type="button" @click="openDetailId = null">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>

{{-- resources/views/admin/members/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $user = $member->user;

    $displayName = $user?->name ?? '-';
    $foto = !empty($user?->foto)
        ? Storage::url($user->foto)
        : 'https://placehold.co/200x200/3A2D2A/F5E6D6?text=No+Foto';

    $tglDaftar = $member->tanggal_daftar ? Carbon::parse($member->tanggal_daftar)->translatedFormat('d F Y') : '-';

    // status & paket aktif idealnya dari accessor/relasi transaksi membership (bukan dari kolom members)
    $statusKey = $member->status_membership ?? 'belum_aktif';
    $statusVariant = match ($statusKey) {
        'aktif' => 'success',
        'expired' => 'danger',
        'belum_aktif' => 'warning',
        default => 'neutral',
    };
    $statusLabel = Str::upper(str_replace('_', ' ', $statusKey));
    $paketAktifNama = $member->nama_paket_aktif ?? null;

    $jenisKelamin = !empty($user?->jenis_kelamin) ? ucfirst($user->jenis_kelamin) : '-';

    $alamatRaw = $user?->alamat ?? '';
    $alamatTrimmed = trim($alamatRaw);
    $alamatHtml = $alamatTrimmed !== '' ? nl2br(e($alamatTrimmed)) : '-';
@endphp

<div x-show="openDetailId === {{ $member->id }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null" @keydown.escape.window="openDetailId = null" @wheel.prevent @touchmove.prevent>
    <div
        class="relative w-full max-w-5xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Profil tersimpan di <b>users</b>, tanggal daftar tersimpan di <b>members</b>.
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY (tanpa scroll) --}}
        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                {{-- KIRI: INFO --}}
                <div class="lg:col-span-8 space-y-4">
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-text-main">Ringkasan Membership</h3>
                                <p class="text-xs text-text-muted mt-0.5">
                                    Status/paket aktif mengikuti transaksi membership (accessor/relasi).
                                </p>
                            </div>
                            <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Tanggal Daftar</p>
                                <p class="text-base text-text-main">{{ $tglDaftar }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Paket Aktif</p>
                                <p class="text-base text-text-main">
                                    @if ($paketAktifNama)
                                        <span class="font-semibold text-gold-700">{{ $paketAktifNama }}</span>
                                    @else
                                        <span class="text-text-muted italic">—</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                        <h3 class="text-sm font-semibold text-text-main">Profil User</h3>
                        <p class="text-xs text-text-muted mt-0.5">Diambil dari tabel <b>users</b>.</p>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Nama</p>
                                <p class="text-base font-semibold text-text-main">{{ $displayName }}</p>
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
                                <p class="text-[11px] text-text-muted mb-0.5">Email</p>
                                <p class="text-base text-text-main">{{ $user?->email ?? '-' }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">No. HP</p>
                                <p class="text-base text-text-main">{{ $user?->no_hp ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-brand-borderSoft/70">
                            <p class="text-[11px] text-text-muted mb-1">Alamat</p>
                            <p class="text-sm text-text-main leading-relaxed break-words whitespace-normal">
                                {!! $alamatHtml !!}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KANAN: FOTO --}}
                <div class="lg:col-span-4">
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-4 h-full">
                        <h3 class="text-sm font-semibold text-text-main">Foto Profil</h3>
                        <p class="text-xs text-text-muted mt-0.5">Diambil dari <b>users.foto</b>.</p>

                        <div class="mt-4 flex flex-col items-center gap-3">
                            <div
                                class="w-28 h-28 rounded-2xl overflow-hidden bg-brand-surface-50 border border-brand-borderSoft shadow-[0_10px_28px_rgba(0,0,0,0.18)]">
                                <img src="{{ $foto }}" alt="Foto {{ $displayName }}"
                                    class="w-full h-full object-cover">
                            </div>

                            <div class="text-center space-y-1">
                                <p class="text-sm font-semibold text-text-main">{{ $displayName }}</p>
                                <p class="text-xs text-text-muted">Username: {{ $user?->username ?? '-' }}</p>
                                <p class="text-xs text-text-muted">No HP: {{ $user?->no_hp ?? '-' }}</p>
                            </div>

                            <div class="pt-3 w-full flex justify-center">
                                <x-ui.button-secondary type="button"
                                    @click="openDetailId = null">Tutup</x-ui.button-secondary>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

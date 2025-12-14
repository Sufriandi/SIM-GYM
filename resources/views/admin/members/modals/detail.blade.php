{{-- resources/views/admin/members/modals/detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $user = $member->user;

    $displayName = $user->name ?? '-';

    $foto = !empty($user?->foto)
        ? Storage::url($user->foto)
        : 'https://placehold.co/200x200/3A2D2A/F5E6D6?text=No+Foto';

    $tglDaftar = $member->tanggal_daftar ? Carbon::parse($member->tanggal_daftar)->translatedFormat('d F Y') : '-';
    $mulaiMembership = $member->tanggal_mulai ? Carbon::parse($member->tanggal_mulai)->translatedFormat('d F Y') : '-';
    $akhirMembership = $member->tanggal_akhir ? Carbon::parse($member->tanggal_akhir)->translatedFormat('d F Y') : '-';

    $jenisKelamin = !empty($user?->jenis_kelamin) ? ucfirst($user->jenis_kelamin) : '-';

    // Status derived
    $today = Carbon::today();
    if (empty($member->tanggal_mulai) || empty($member->tanggal_akhir)) {
        $statusKey = 'belum_ada_periode';
    } else {
        $mulai = Carbon::parse($member->tanggal_mulai)->startOfDay();
        $akhir = Carbon::parse($member->tanggal_akhir)->endOfDay();

        if ($today->lt($mulai)) {
            $statusKey = 'belum_aktif';
        } elseif ($today->betweenIncluded($mulai, $akhir)) {
            $statusKey = 'aktif';
        } else {
            $statusKey = 'expired';
        }
    }

    $statusVariant = match ($statusKey) {
        'aktif' => 'success',
        'expired' => 'danger',
        'belum_aktif' => 'warning',
        'belum_ada_periode' => 'neutral',
        default => 'neutral',
    };

    $statusLabel = Str::upper(str_replace('_', ' ', $statusKey));

    $alamatRaw = $user->alamat ?? '';
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
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Profil (users) dan periode membership (members).
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- KIRI: PERIODE MEMBER --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-1">Periode Membership</h3>
                        <p class="text-xs text-text-muted mb-3">
                            Diambil dari tabel <span class="font-semibold">members</span>.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Tanggal Daftar</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $tglDaftar }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Status</p>
                                <p class="mt-0.5">
                                    <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Mulai Membership</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $mulaiMembership }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Akhir Membership</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $akhirMembership }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-1">Profil Member</h3>
                        <p class="text-xs text-text-muted mb-3">
                            Diambil dari tabel <span class="font-semibold">users</span>.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Nama</p>
                                <p
                                    class="text-base font-semibold text-text-main break-words whitespace-normal leading-snug">
                                    {{ $displayName }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Jenis Kelamin</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $jenisKelamin }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Username</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $user->username ?? '-' }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Email</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $user->email ?? '-' }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">No. HP</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $user->no_hp ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-brand-borderSoft/70">
                            <p class="text-[11px] text-text-muted mb-1">Alamat</p>
                            <p class="mt-0.5 text-sm text-text-main leading-relaxed break-words whitespace-normal">
                                {!! $alamatHtml !!}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KANAN: FOTO --}}
                <div class="space-y-4">
                    <div
                        class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-3 flex flex-col gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-text-main">Foto Profil</h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Diambil dari kolom <span class="font-semibold">users.foto</span>.
                            </p>
                        </div>

                        <div class="flex flex-col items-center gap-3">
                            <div
                                class="w-28 h-28 rounded-full overflow-hidden bg-brand-surface-50 border-2 border-brand-borderSoft shadow-[0_10px_28px_rgba(0,0,0,0.18)]">
                                <img src="{{ $foto }}" alt="Foto {{ $displayName }}"
                                    class="w-full h-full object-cover">
                            </div>

                            <div class="text-center space-y-1">
                                <p class="text-sm font-semibold text-text-main">{{ $displayName }}</p>
                                <p class="text-xs text-text-muted">Username: {{ $user->username ?? '-' }}</p>
                                <p class="text-xs text-text-muted">No HP: {{ $user->no_hp ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end lg:hidden">
                <x-ui.button-secondary type="button" @click="openDetailId = null">Tutup</x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>

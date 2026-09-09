{{-- resources/views/admin/coach/modals/detail.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    $currentFotoPath = $coach->foto ?? null;
    $currentFotoUrl = $currentFotoPath
        ? Storage::url($currentFotoPath)
        : 'https://placehold.co/400x500/3A2D2A/F5E6D6?text=No+Foto';

    // Deskripsi (newline -> <br>, fallback "-")
    $deskripsiRaw = $coach->deskripsi ?? '';
    $deskripsiTrimmed = trim($deskripsiRaw);
    $deskripsiHtml = $deskripsiTrimmed !== ''
        ? nl2br(e($deskripsiTrimmed))
        : '-';

    // Alamat (newline -> <br>, fallback "-")
    $alamatRaw = $coach->alamat ?? '';
    $alamatTrimmed = trim($alamatRaw);
    $alamatHtml = $alamatTrimmed !== ''
        ? nl2br(e($alamatTrimmed))
        : '-';
@endphp

<div
    x-show="openDetailId === {{ $coach->id }}"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-sm"
    @click.self="openDetailId = null"
    @keydown.escape.window="openDetailId = null"
>
    <div
        class="relative w-full max-w-5xl my-auto rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Coach</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Informasi lengkap mengenai profil coach.
                </p>
            </div>

            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- KIRI: DATA COACH & DESKRIPSI --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- DATA COACH --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-1">
                            Data Coach
                        </h3>
                        <p class="text-xs text-text-muted mb-3">
                            Detail informasi profil coach yang terdaftar.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- NAMA --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Nama Coach</p>
                                <p class="text-base font-semibold text-text-main break-words whitespace-normal leading-snug">
                                    {{ $coach->nama }}
                                </p>
                            </div>

                            {{-- NO HP --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Nomor HP</p>
                                <p class="text-base text-text-main break-words whitespace-normal leading-snug">
                                    {{ $coach->no_hp }}
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

                    {{-- DESKRIPSI / KEAHLIAN --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-2">
                            Deskripsi / Keahlian Coach
                        </h3>

                        <div
                            class="mt-1 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft
                                   text-sm text-text-main min-h-[80px] break-words whitespace-normal leading-relaxed"
                        >
                            {!! $deskripsiHtml !!}
                        </div>
                    </div>
                </div>

                {{-- KANAN: FOTO COACH --}}
                <div class="space-y-4">
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-3 flex flex-col gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-text-main">
                                Foto Coach
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Tampilan foto profil terbaru dari coach.
                            </p>
                        </div>

                        <div
                            class="w-full h-64 rounded-2xl overflow-hidden bg-brand-surface-50
                                   flex items-center justify-center
                                   shadow-[0_12px_32px_rgba(0,0,0,0.18)]"
                        >
                            <img
                                src="{{ $currentFotoUrl }}"
                                alt="Foto {{ $coach->nama }}"
                                class="max-w-full max-h-full object-contain"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER MOBILE: TOMBOL TUTUP --}}
            <div class="mt-6 flex justify-end lg:hidden">
                <x-ui.button-secondary type="button" @click="openDetailId = null">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>

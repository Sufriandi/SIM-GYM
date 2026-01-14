{{-- resources/views/member/membership/index.blade.php --}}

@php
    use Illuminate\Support\Str;

    $pageTitle    = $pageTitle ?? 'Paket Membership';
    $pageSubtitle = $pageSubtitle ?? 'Pilih paket membership sesuai kebutuhan latihan Anda.';

    /** @var \Illuminate\Support\Collection $pakets */
    $pakets = $pakets ?? collect();

    $tipeLabels = [
        'single' => 'Single',
        'double' => 'Double',
        'triple' => 'Triple',
    ];
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-5xl mx-auto space-y-5 pt-2">

        {{-- HEADER --}}
        <x-ui.section-header :title="$pageTitle" :subtitle="$pageSubtitle" />

        <hr class="border-t border-brand-borderSoft">

        {{-- GRID PAKET --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($pakets as $paket)
                <x-ui.card
                    class="p-0 overflow-hidden
                           flex flex-col h-full
                           bg-brand-shell/60
                           border border-brand-borderSoft
                           hover:shadow-lg transition">

                    {{-- HEADER CARD --}}
                    <div class="px-5 py-4 border-b border-brand-borderSoft">
                        <h3 class="text-base font-semibold text-text-main leading-tight line-clamp-1">
                            {{ $paket->nama }}
                        </h3>

                        <p class="text-[11px] text-text-muted mt-1 tracking-wide">
                            {{ strtoupper($tipeLabels[$paket->tipe] ?? $paket->tipe) }}
                            &nbsp;•&nbsp;
                            {{ (int) $paket->durasi }} HARI
                        </p>
                    </div>

                    {{-- BODY (tombol dibuat mt-auto agar selalu sejajar) --}}
                    <div class="px-5 py-4 flex-1 flex flex-col gap-3">
                        <div class="text-xl font-bold text-gold-600 leading-tight">
                            Rp {{ number_format((int) $paket->harga, 0, ',', '.') }}
                        </div>

                        {{-- Konsistensi tinggi teks: clamp 3 baris + min-height 3 baris --}}
                        <p class="text-sm text-text-main leading-6 line-clamp-3 min-h-[4.5rem]">
                            {{ $paket->deskripsi ?: 'Membership gym reguler.' }}
                        </p>

                        {{-- Tombol selalu di bawah --}}
                        <div class="mt-auto pt-2 pb-1">
                            <a href="{{ route('membership.show', $paket->id) }}"
                               class="w-full inline-flex justify-center items-center gap-2
                                      px-4 py-2.5 rounded-full text-sm font-semibold
                                      bg-gold-600 text-white
                                      hover:bg-gold-500 transition">
                                Lihat Detail
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                </x-ui.card>
            @empty
                <div class="col-span-full text-center text-text-muted py-14 italic">
                    Belum ada paket membership yang tersedia.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.member>

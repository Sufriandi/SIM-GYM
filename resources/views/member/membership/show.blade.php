{{-- resources/views/member/membership/show.blade.php --}}

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    /** @var \App\Models\PaketMembership $paketMembership */
    $paket = $paketMembership;

    $pageTitle    = $paket->nama;
    $pageSubtitle = 'Detail paket membership';

    $tipeLabels = [
        'single' => 'Single',
        'double' => 'Double',
        'triple' => 'Triple',
    ];

    $tipe = strtoupper($tipeLabels[$paket->tipe] ?? ($paket->tipe ?? '-'));
    $harga = (int) $paket->harga;
    $durasi = (int) $paket->durasi;

    // Jika Anda belum punya field akses di DB, biarkan default.
    $akses = 'Full Gym';

    // URL checkout aman (tidak error kalau route belum ada)
    $checkoutUrl = Route::has('member.membership.checkout')
        ? route('member.membership.checkout', $paket->id)
        : '#';

    // Olah deskripsi: jika bentuknya bullet/multi-line, tampilkan sebagai list
    $rawDesc = trim((string) ($paket->deskripsi ?? ''));
    $lines = $rawDesc !== '' ? preg_split("/\r\n|\r|\n/", $rawDesc) : [];
    $lines = array_values(array_filter(array_map('trim', $lines), fn($v) => $v !== ''));

    $bulletLines = [];
    foreach ($lines as $l) {
        if (Str::startsWith($l, ['- ', '• ', '* '])) {
            $bulletLines[] = trim(ltrim($l, "-•* \t"));
        }
    }

    // Anggap bullet kalau minimal 2 baris bullet
    $hasBullets = count($bulletLines) >= 2;
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-5xl mx-auto pt-2 space-y-5">

        {{-- BACK --}}
        <a href="{{ route('member.membership.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold
                  text-text-muted hover:text-text-main transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Paket Membership
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- LEFT: DETAIL --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Title + short desc (tanpa card besar) --}}
                <div class="space-y-2">
                    <h1 class="text-3xl font-extrabold text-text-main leading-tight">
                        {{ $paket->nama }}
                    </h1>

                    <p class="text-sm text-text-muted max-w-2xl leading-relaxed">
                        {{ $rawDesc !== '' ? $rawDesc : 'Paket membership gym.' }}
                    </p>
                </div>

                {{-- Mini stats (compact pills) --}}
                <div class="flex flex-wrap gap-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-brand-borderSoft bg-white/60 px-4 py-2">
                        <i data-lucide="wallet" class="w-4 h-4 text-text-muted"></i>
                        <span class="text-xs text-text-muted">Harga</span>
                        <span class="text-sm font-semibold text-gold-600">
                            Rp {{ number_format($harga, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="inline-flex items-center gap-2 rounded-full border border-brand-borderSoft bg-white/60 px-4 py-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-text-muted"></i>
                        <span class="text-xs text-text-muted">Durasi</span>
                        <span class="text-sm font-semibold text-text-main">{{ $durasi }} hari</span>
                    </div>

                    <div class="inline-flex items-center gap-2 rounded-full border border-brand-borderSoft bg-white/60 px-4 py-2">
                        <i data-lucide="badge-check" class="w-4 h-4 text-text-muted"></i>
                        <span class="text-xs text-text-muted">Akses</span>
                        <span class="text-sm font-semibold text-text-main">{{ $akses }}</span>
                    </div>

                    <div class="inline-flex items-center gap-2 rounded-full border border-brand-borderSoft bg-white/60 px-4 py-2">
                        <i data-lucide="layers" class="w-4 h-4 text-text-muted"></i>
                        <span class="text-xs text-text-muted">Tipe</span>
                        <span class="text-sm font-semibold text-text-main">{{ $tipe }}</span>
                    </div>
                </div>

                {{-- Jika deskripsi bisa dijadikan bullet, tampilkan blok "Ringkasan" agar terlihat berisi --}}
                <div class="rounded-2xl border border-brand-borderSoft bg-brand-shell/40 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-text-main">Ringkasan Paket</h2>
                        <span class="text-xs text-text-muted">Informasi paket</span>
                    </div>

                    <div class="mt-3">
                        @if($hasBullets)
                            <ul class="space-y-2 text-sm text-text-main/80 leading-relaxed">
                                @foreach ($bulletLines as $b)
                                    <li class="flex gap-2">
                                        <span class="mt-1 inline-block w-1.5 h-1.5 rounded-full bg-gold-600"></span>
                                        <span>{{ $b }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-text-main/80 leading-relaxed">
                                Paket ini berlaku selama <span class="font-semibold text-text-main">{{ $durasi }} hari</span>
                                dengan akses <span class="font-semibold text-text-main">{{ $akses }}</span>.
                                Informasi tambahan dapat dilihat pada deskripsi paket.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT: PURCHASE SUMMARY (kecil, rapi, tidak memenuhi layar) --}}
            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-brand-borderSoft bg-white/65 p-5 shadow-sm lg:sticky lg:top-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs tracking-widest text-text-muted uppercase">
                                Total
                            </p>
                            <p class="mt-1 text-2xl font-extrabold text-gold-600 leading-tight">
                                Rp {{ number_format($harga, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-text-muted">Durasi</p>
                            <p class="text-sm font-semibold text-text-main">{{ $durasi }} hari</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('member.membership.checkout', $paketMembership->id) }}"
   class="w-full inline-flex justify-center items-center gap-2
          rounded-full px-5 py-3 bg-gold-600 text-white text-sm font-semibold
          hover:bg-gold-500 transition">
    Lanjut ke Checkout
    <i data-lucide="arrow-right" class="w-4 h-4"></i>
</a>
                    </div>

                    <hr class="my-4 border-brand-borderSoft">

                    <div class="text-xs text-text-muted leading-relaxed">
                        Pastikan paket yang dipilih sudah sesuai. Anda dapat kembali untuk memilih paket lain.
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.member>

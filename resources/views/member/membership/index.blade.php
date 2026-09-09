{{-- resources/views/member/membership/index.blade.php --}}
@php
    use Illuminate\Support\Str;

    $pageTitle    = $pageTitle ?? 'Paket Membership';
    $pageSubtitle = $pageSubtitle ?? 'Pilih paket membership terbaik sesuai kebutuhan dan ritme latihan Anda.';
    $pakets       = $pakets ?? collect();

    $recommendedId = $recommendedId ?? null;
    $longestId     = $longestId ?? null;

    $tipeTabs = [
        'all'    => ['label' => 'Semua Paket', 'icon' => 'layers'],
        'single' => ['label' => 'Individu (Single)', 'icon' => 'user'],
        'double' => ['label' => 'Couple (2 Orang)', 'icon' => 'users'],
        'triple' => ['label' => 'Grup (3 Orang)', 'icon' => 'users-triple'],
    ];
@endphp

<x-layouts.member :title="$pageTitle . ' – BETA GYM'" :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">

    {{-- Local styles: Adaptive Card Shadows & Smooth Transitions (Mengikuti tema default member, tidak gelap) --}}
    <style>
        .member-pricing-card {
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .member-pricing-card:hover {
            transform: translateY(-5px);
            border-color: rgba(212, 167, 87, 0.65);
            box-shadow: 0 16px 40px -10px rgba(32, 25, 17, 0.15);
        }
        .dark .member-pricing-card:hover {
            border-color: rgba(212, 167, 87, 0.75);
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.60);
        }
        .member-pricing-featured {
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .member-pricing-featured:hover {
            transform: translateY(-7px);
            border-color: rgba(234, 179, 8, 1);
            box-shadow: 0 20px 48px -8px rgba(212, 167, 87, 0.35);
        }
        .dark .member-pricing-featured:hover {
            border-color: rgba(251, 191, 36, 1);
            box-shadow: 0 24px 50px -8px rgba(212, 167, 87, 0.45);
        }
    </style>

    <div class="max-w-7xl mx-auto space-y-12 pt-2 pb-16" x-data="{ activeTab: 'all' }">

        {{-- ========================================================= --}}
        {{-- HEADER BANNER --}}
        {{-- ========================================================= --}}
        <div class="pb-6 border-b border-brand-borderSoft/40">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-gold-500/15 text-gold-600 dark:text-gold-400 border border-gold-500/30 text-xs font-bold uppercase tracking-wider mb-3 shadow-sm">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-gold-600 dark:text-gold-400"></i>
                    Pilihan Paket Membership
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-display font-bold text-brand-nav dark:text-white tracking-tight leading-tight">
                    INVESTASI TERBAIK <br>
                    <span class="text-transparent bg-clip-text bg-brand-gold">UNTUK TUBUH ANDA.</span>
                </h1>
                <p class="text-text-muted dark:text-brand-silver/80 text-xs sm:text-sm mt-2.5 leading-relaxed max-w-2xl">
                    Pilih paket membership yang paling pas untuk ritme latihan Anda. Akses penuh ke seluruh fasilitas gym modern tanpa biaya tersembunyi.
                </p>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- FILTER TABS & PRICING CARDS SECTION --}}
        {{-- ========================================================= --}}
        <section class="space-y-8">

            {{-- FILTER TABS (INSTAN CLIENT-SIDE, TEMA DEFAULT MEMBER) --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="inline-flex flex-wrap items-center gap-1.5 p-1.5 rounded-2xl bg-brand-card border border-brand-borderSoft shadow-sm">
                    @foreach($tipeTabs as $key => $tab)
                        <button type="button"
                                @click="activeTab = '{{ $key }}'"
                                class="px-4 py-2.5 rounded-xl text-xs tracking-wide transition-all duration-200 flex items-center gap-2 cursor-pointer select-none"
                                :class="activeTab === '{{ $key }}'
                                    ? 'bg-gold-500 text-brand-nav font-bold shadow-md shadow-gold-500/20'
                                    : 'text-text-muted dark:text-brand-silver hover:text-brand-nav dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 font-medium'">
                            @if($tab['icon'] === 'user')
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            @elseif($tab['icon'] === 'users')
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            @elseif($tab['icon'] === 'users-triple')
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            @else
                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                            @endif
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Total Paket Indicator --}}
                <div class="text-xs text-text-muted dark:text-brand-silver flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Menampilkan {{ $pakets->count() }} pilihan paket resmi</span>
                </div>
            </div>

            {{-- CARDS GRID --}}
            @if($pakets->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center bg-brand-card border border-dashed border-brand-borderSoft rounded-3xl p-10 shadow-sm">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4 bg-brand-surface-100 dark:bg-white/5 border border-brand-borderSoft text-text-muted">
                        <i data-lucide="package-x" class="w-10 h-10"></i>
                    </div>
                    <h3 class="text-xl font-bold font-display text-brand-nav dark:text-white mb-2">Paket Belum Tersedia</h3>
                    <p class="text-text-muted text-sm max-w-md">
                        Saat ini belum ada paket membership yang dipublikasikan.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-7 items-stretch">
                    @foreach($pakets as $paket)
                        @php
                            $harga = (int) ($paket->harga ?? 0);
                            $durasi = (int) ($paket->durasi ?? 0);
                            $rawTipe = strtolower(trim((string) ($paket->tipe ?? 'single')));

                            $isFeatured = ($recommendedId && $paket->id === $recommendedId);
                            $isLongest = ($longestId && $paket->id === $longestId && !$isFeatured);

                            $actionUrl = route('member.membership.checkout', $paket->id);
                            $detailUrl = route('member.membership.show', $paket->id);

                            // Tipe badge color
                            if ($rawTipe === 'double') {
                                $tipeBadgeClass = 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/25';
                                $tipeLabel = 'Couple (2 Orang)';
                            } elseif ($rawTipe === 'triple') {
                                $tipeBadgeClass = 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/25';
                                $tipeLabel = 'Grup (3 Orang)';
                            } else {
                                $tipeBadgeClass = $isFeatured ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-500/35' : 'bg-gold-500/10 text-gold-700 dark:text-gold-400 border-gold-500/25';
                                $tipeLabel = 'Individu';
                            }
                        @endphp

                        <article x-show="activeTab === 'all' || activeTab === '{{ $rawTipe }}'"
                                 x-transition:enter="transition-opacity ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="group rounded-3xl overflow-hidden flex flex-col justify-between relative
                                        {{ $isFeatured
                                            ? 'bg-gradient-to-b from-amber-500/10 via-brand-card to-brand-card border-2 border-gold-500 shadow-xl shadow-gold-500/10 member-pricing-featured'
                                            : 'bg-brand-card border border-brand-borderSoft/80 shadow-md member-pricing-card' }}">

                            {{-- Header Ribbon untuk Paket Populer / Terpanjang --}}
                            @if($isFeatured)
                                <div class="bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-brand-nav text-[11px] font-black uppercase tracking-[0.2em] text-center py-2.5 px-4 shadow-sm flex items-center justify-center gap-2 relative z-10">
                                    <i data-lucide="crown" class="w-4 h-4 fill-brand-nav text-brand-nav"></i>
                                    Paling Populer
                                </div>
                            @elseif($isLongest)
                                <div class="bg-brand-shell/80 dark:bg-white/10 text-gold-700 dark:text-gold-400 text-[10px] font-black uppercase tracking-widest text-center py-2 px-4 border-b border-brand-borderSoft dark:border-white/10 flex items-center justify-center gap-1.5 relative z-10">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    Durasi Terpanjang
                                </div>
                            @endif

                            <div class="p-6 sm:p-7 flex-1 flex flex-col relative z-10">

                                {{-- Badge Tipe & Nama Paket --}}
                                <div class="mb-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $tipeBadgeClass }} mb-2.5">
                                        @if($rawTipe === 'single')
                                            <i data-lucide="user" class="w-3 h-3"></i>
                                        @elseif($rawTipe === 'double')
                                            <i data-lucide="users" class="w-3 h-3"></i>
                                        @else
                                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        @endif
                                        {{ $tipeLabel }}
                                    </span>
                                    <h3 class="text-xl font-bold font-heading text-brand-nav dark:text-white group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors line-clamp-1" title="{{ $paket->nama }}">
                                        {{ $paket->nama }}
                                    </h3>
                                </div>

                                {{-- Blok Harga (Warna Tema Default, Tidak Gelap) --}}
                                <div class="py-4 my-2 border-y border-brand-borderSoft/50 dark:border-white/10 bg-brand-shell/30 dark:bg-black/25 -mx-6 sm:-mx-7 px-6 sm:px-7">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-sm font-bold text-gold-600 dark:text-gold-400">Rp</span>
                                        <span class="text-3xl sm:text-4xl font-black font-display tracking-tight text-brand-nav dark:text-white group-hover:text-gold-600 dark:group-hover:text-gold-300 transition-colors">
                                            {{ number_format($harga, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5 mt-2.5 text-xs text-text-muted dark:text-brand-silver/70">
                                        <span class="inline-flex items-center gap-1.5 font-medium">
                                            <i data-lucide="clock-3" class="w-3.5 h-3.5 text-gold-600 dark:text-gold-400 shrink-0"></i>
                                            Masa aktif: <strong class="text-brand-nav dark:text-white">{{ $durasi }} hari</strong>
                                        </span>
                                    </div>
                                </div>

                                {{-- Deskripsi Paket --}}
                                <p class="text-xs text-text-muted dark:text-brand-silver/85 leading-relaxed min-h-[38px] line-clamp-2 my-4">
                                    {{ $paket->deskripsi ?: 'Membership resmi BETA GYM dengan akses lengkap seluruh fasilitas.' }}
                                </p>

                                {{-- Fasilitas & Keuntungan Checklist --}}
                                <ul class="space-y-3 text-xs text-text-main dark:text-brand-silver/90 mb-6 flex-1">
                                    <li class="flex items-start gap-2.5">
                                        <span class="w-4 h-4 rounded-full bg-gold-500/20 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0 mt-0.5 border border-gold-500/30">
                                            <i data-lucide="check" class="w-2.5 h-2.5 stroke-[3]"></i>
                                        </span>
                                        <span>Akses bebas seluruh area gym</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="w-4 h-4 rounded-full bg-gold-500/20 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0 mt-0.5 border border-gold-500/30">
                                            <i data-lucide="check" class="w-2.5 h-2.5 stroke-[3]"></i>
                                        </span>
                                        <span>Loker penyimpanan pribadi yang aman</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="w-4 h-4 rounded-full bg-gold-500/20 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0 mt-0.5 border border-gold-500/30">
                                            <i data-lucide="check" class="w-2.5 h-2.5 stroke-[3]"></i>
                                        </span>
                                        <span>Kartu absensi digital member</span>
                                    </li>
                                </ul>

                                {{-- Tombol Aksi --}}
                                <div class="mt-auto pt-2 space-y-2">
                                    <a href="{{ $actionUrl }}"
                                       class="w-full py-3.5 px-5 rounded-2xl text-xs font-heading uppercase tracking-wider
                                              flex items-center justify-center gap-2 transition-all duration-300 shadow-md
                                              {{ $isFeatured
                                                  ? 'bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 hover:from-amber-300 hover:to-yellow-300 text-brand-nav font-black shadow-md hover:scale-[1.02]'
                                                  : 'bg-brand-nav hover:bg-gold-500 text-white hover:text-brand-nav dark:bg-gold-500 dark:text-brand-nav font-bold hover:scale-[1.02]' }}">
                                        <span>Pilih & Lanjutkan</span>
                                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                                    </a>

                                    <a href="{{ $detailUrl }}"
                                       class="w-full py-2 px-4 rounded-xl text-[11px] font-semibold text-text-muted hover:text-gold-600 dark:hover:text-gold-400 text-center block transition-colors">
                                        Lihat Detail Paket
                                    </a>
                                </div>

                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

        </section>

        {{-- ========================================================= --}}
        {{-- KEUNGGULAN FASILITAS GYM --}}
        {{-- ========================================================= --}}
        <section class="pt-8 border-t border-brand-borderSoft/40">
            <div class="max-w-2xl mb-8">
                <span class="text-gold-600 dark:text-gold-400 font-bold tracking-widest uppercase text-xs font-heading">Fasilitas Lengkap</span>
                <h2 class="text-2xl sm:text-3xl font-display font-bold text-brand-nav dark:text-white mt-1.5">
                    KENAPA BERLATIH DI <span class="text-transparent bg-clip-text bg-brand-gold">BETA GYM?</span>
                </h2>
                <p class="text-text-muted dark:text-brand-silver/80 text-xs sm:text-sm mt-2 leading-relaxed">
                    Kenyamanan dan kelengkapan fasilitas Anda adalah komitmen utama kami.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Card 1: Peralatan Gym --}}
                <div class="bg-brand-card border border-brand-borderSoft/80 rounded-2xl p-6 flex flex-col items-start gap-4 shadow-sm hover:shadow-md hover:border-gold-500/50 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                        <i data-lucide="dumbbell" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-brand-nav dark:text-white text-base mb-1.5">Peralatan Gym Lengkap</h3>
                        <p class="text-text-muted dark:text-brand-silver/75 text-xs leading-relaxed">
                            Variasi mesin beban, plate-loaded, cable machine, hingga dumbbell set lengkap untuk seluruh target latihan.
                        </p>
                    </div>
                </div>

                {{-- Card 2: Loker & Ruang Ganti --}}
                <div class="bg-brand-card border border-brand-borderSoft/80 rounded-2xl p-6 flex flex-col items-start gap-4 shadow-sm hover:shadow-md hover:border-gold-500/50 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-6 h-6 text-gold-600 dark:text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-brand-nav dark:text-white text-base mb-1.5">Loker & Ruang Ganti</h3>
                        <p class="text-text-muted dark:text-brand-silver/75 text-xs leading-relaxed">
                            Tersedia loker pribadi untuk menyimpan barang bawaan dengan aman serta area ruang ganti yang bersih.
                        </p>
                    </div>
                </div>

                {{-- Card 3: Coach Independen --}}
                <div class="bg-brand-card border border-brand-borderSoft/80 rounded-2xl p-6 flex flex-col items-start gap-4 shadow-sm hover:shadow-md hover:border-gold-500/50 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-brand-nav dark:text-white text-base mb-1.5">Coach Independen Handal</h3>
                        <p class="text-text-muted dark:text-brand-silver/75 text-xs leading-relaxed">
                            Hubungi langsung coach profesional pilihan Anda untuk program latihan privat sesuai target Anda.
                        </p>
                    </div>
                </div>

                {{-- Card 4: Aplikasi Digital --}}
                <div class="bg-brand-card border border-brand-borderSoft/80 rounded-2xl p-6 flex flex-col items-start gap-4 shadow-sm hover:shadow-md hover:border-gold-500/50 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-600 dark:text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-6 h-6 text-gold-600 dark:text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="3" ry="3"/>
                            <circle cx="12" cy="18" r="1" fill="currentColor"/>
                            <line x1="9" y1="6" x2="15" y2="6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-brand-nav dark:text-white text-base mb-1.5">Aplikasi Digital Member</h3>
                        <p class="text-text-muted dark:text-brand-silver/75 text-xs leading-relaxed">
                            Cek masa aktif, riwayat latihan, belanja di marketplace, hingga presensi kehadiran secara digital.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>

</x-layouts.member>

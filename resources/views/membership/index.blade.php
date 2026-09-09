{{-- resources/views/membership/index.blade.php --}}
@php
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Membership';
    $pakets = $pakets ?? collect();

    $registerUrl = route('register');
    $loginUrl = route('login');

    $tipeTabs = [
        'all'    => ['label' => 'Semua Paket', 'icon' => 'layers'],
        'single' => ['label' => 'Individu (Single)', 'icon' => 'user'],
        'double' => ['label' => 'Couple (2 Orang)', 'icon' => 'users'],
        'triple' => ['label' => 'Grup (3 Orang)', 'icon' => 'users-triple'],
    ];
@endphp

<x-layouts.guest :title="$pageTitle . ' – BETA GYM'">

    {{-- Local styles for luxury dark gold cards & distinct elevated surfaces --}}
    <style>
        .pricing-card {
            background: linear-gradient(180deg, #1b202c 0%, #141721 50%, #0e1017 100%);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.70), inset 0 1px 0 rgba(255, 255, 255, 0.12);
            transition: transform .28s cubic-bezier(0.16, 1, 0.3, 1), border-color .28s ease, box-shadow .28s ease, background-color .28s ease;
        }
        .pricing-card:hover {
            transform: translateY(-6px);
            border-color: rgba(212, 167, 87, 0.75);
            box-shadow: 0 24px 50px -10px rgba(212, 167, 87, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .pricing-card-featured {
            background: radial-gradient(ellipse at 50% 0%, rgba(245, 158, 11, 0.24) 0%, transparent 70%), linear-gradient(180deg, #241d13 0%, #191717 40%, #101217 100%);
            border: 2px solid rgba(245, 158, 11, 0.85);
            box-shadow: 0 0 35px -5px rgba(245, 158, 11, 0.35), 0 20px 50px rgba(0, 0, 0, 0.85), inset 0 1px 0 rgba(254, 240, 138, 0.45);
            transition: transform .28s cubic-bezier(0.16, 1, 0.3, 1), border-color .28s ease, box-shadow .28s ease;
        }
        .pricing-card-featured:hover {
            transform: translateY(-8px);
            border-color: rgba(251, 191, 36, 1);
            box-shadow: 0 0 50px 0 rgba(245, 158, 11, 0.50), 0 30px 65px rgba(0, 0, 0, 0.90), inset 0 1px 0 rgba(255, 255, 255, 0.55);
        }
        .facility-card {
            background: linear-gradient(180deg, #1e2432 0%, #161a24 100%);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.10);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .facility-card:hover {
            transform: translateY(-4px);
            border-color: rgba(212, 167, 87, 0.60);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.65), 0 0 20px rgba(212, 167, 87, 0.15);
        }
        .faq-card {
            background: linear-gradient(180deg, #1d222e 0%, #151923 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.50), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
    </style>

    <div class="dark bg-brand-bg text-brand-text min-h-screen" x-data="{ activeTab: 'all' }">

        {{-- BACKGROUND ATMOSPHERE --}}
        <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden bg-brand-dark">
            <div class="absolute top-[-10%] right-[-10%] w-[750px] h-[750px] bg-gold-500/7 rounded-full blur-[170px]"></div>
            <div class="absolute top-[35%] left-[-10%] w-[550px] h-[550px] bg-brand-surface-200/10 rounded-full blur-[150px]"></div>
            <div class="absolute bottom-[-10%] right-[10%] w-[650px] h-[650px] bg-gold-500/6 rounded-full blur-[180px]"></div>
            <div class="absolute inset-0 opacity-[0.03]"
                 style="background-image: linear-gradient(to right, #888 1px, transparent 1px), linear-gradient(to bottom, #888 1px, transparent 1px); background-size: 50px 50px;">
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- HERO SECTION --}}
        {{-- ========================================================= --}}
        <section class="relative pt-28 md:pt-36 pb-8 z-10">
            <div class="container mx-auto px-6">

                {{-- Header Content --}}
                <div class="pb-8 border-b border-white/[0.08]">
                    <div class="max-w-3xl">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white tracking-tight leading-[1.05]">
                            INVESTASI TERBAIK <br>
                            <span class="text-transparent bg-clip-text bg-brand-gold">UNTUK TUBUH ANDA.</span>
                        </h1>
                        <p class="text-brand-silver/85 text-sm md:text-base mt-4 leading-relaxed max-w-2xl">
                            Pilih paket membership yang paling pas untuk ritme latihan Anda. Akses penuh ke seluruh fasilitas gym modern tanpa biaya tersembunyi.
                        </p>
                    </div>
                </div>

            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- FILTER TABS & PRICING CARDS SECTION --}}
        {{-- ========================================================= --}}
        <section class="pt-6 pb-24 z-10 relative">
            <div class="container mx-auto px-6">

                {{-- FILTER TABS (INSTAN CLIENT-SIDE, TANPA RELOAD) --}}
                <div class="mb-10 sm:mb-12">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="inline-flex flex-wrap items-center gap-1.5 p-1.5 rounded-2xl bg-[#13161e] border border-white/10 shadow-lg">
                            @foreach($tipeTabs as $key => $tab)
                                <button type="button"
                                        @click="activeTab = '{{ $key }}'"
                                        class="px-4 py-2.5 rounded-xl text-xs tracking-wide transition-all duration-200 flex items-center gap-2 cursor-pointer select-none"
                                        :class="activeTab === '{{ $key }}'
                                            ? 'bg-gold-500 text-brand-nav font-bold shadow-md shadow-gold-500/25'
                                            : 'text-brand-silver/80 hover:text-white hover:bg-white/5 font-medium'">
                                    @if($tab['icon'] === 'user')
                                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    @elseif($tab['icon'] === 'users')
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                    @elseif($tab['icon'] === 'users-triple')
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    @else
                                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                    @endif
                                    <span>{{ $tab['label'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Total Paket Indicator --}}
                        <div class="text-xs text-brand-silver/60 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Menampilkan {{ $pakets->count() }} pilihan paket resmi</span>
                        </div>
                    </div>
                </div>

                {{-- CARDS GRID --}}
                @if($pakets->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-center pricing-card rounded-3xl p-10">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4 bg-white/5 border border-white/10 text-brand-silver">
                            <i data-lucide="package-x" class="w-10 h-10"></i>
                        </div>
                        <h3 class="text-xl font-bold font-display text-white mb-2">Paket Belum Tersedia</h3>
                        <p class="text-brand-silver/80 text-sm max-w-md">
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

                                // Action link
                                if (auth()->check()) {
                                    $actionUrl = route('member.membership.checkout', $paket->id);
                                    $actionLabel = 'Pilih & Lanjutkan';
                                } else {
                                    $actionUrl = $registerUrl;
                                    $actionLabel = 'Daftar & Gabung';
                                }

                                // Tipe badge color
                                if ($rawTipe === 'double') {
                                    $tipeBadgeClass = 'bg-blue-500/10 text-blue-400 border-blue-500/25';
                                    $tipeLabel = 'Couple (2 Orang)';
                                } elseif ($rawTipe === 'triple') {
                                    $tipeBadgeClass = 'bg-purple-500/10 text-purple-400 border-purple-500/25';
                                    $tipeLabel = 'Grup (3 Orang)';
                                } else {
                                    $tipeBadgeClass = $isFeatured ? 'bg-amber-400/15 text-amber-300 border-amber-400/35' : 'bg-gold-500/10 text-gold-400 border-gold-500/25';
                                    $tipeLabel = 'Individu';
                                }
                            @endphp

                            <article x-show="activeTab === 'all' || activeTab === '{{ $rawTipe }}'"
                                     x-transition:enter="transition-opacity ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     class="group rounded-3xl overflow-hidden flex flex-col justify-between relative {{ $isFeatured ? 'pricing-card-featured' : 'pricing-card' }}">

                                {{-- Header Ribbon untuk Paket Populer / Terpanjang --}}
                                @if($isFeatured)
                                    <div class="bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-slate-950 text-[11px] font-black uppercase tracking-[0.2em] text-center py-2.5 px-4 shadow-[0_4px_16px_rgba(245,158,11,0.35)] flex items-center justify-center gap-2 relative z-10">
                                        <i data-lucide="crown" class="w-4 h-4 fill-slate-950 text-slate-950"></i>
                                        Paling Populer
                                    </div>
                                @elseif($isLongest)
                                    <div class="bg-white/10 text-gold-400 text-[10px] font-black uppercase tracking-widest text-center py-2 px-4 border-b border-white/10 flex items-center justify-center gap-1.5 relative z-10 backdrop-blur-sm">
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
                                        <h3 class="text-xl font-bold font-heading text-white group-hover:text-gold-400 transition-colors line-clamp-1" title="{{ $paket->nama }}">
                                            {{ $paket->nama }}
                                        </h3>
                                    </div>

                                    {{-- Blok Harga yang Mewah --}}
                                    <div class="py-4 my-2 border-y border-white/[0.08] bg-black/30 -mx-6 sm:-mx-7 px-6 sm:px-7">
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="text-sm font-bold {{ $isFeatured ? 'text-amber-400' : 'text-gold-500' }}">Rp</span>
                                            <span class="text-3xl sm:text-4xl font-black font-display tracking-tight text-white group-hover:text-gold-300 transition-colors">
                                                {{ number_format($harga, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5 mt-2.5 text-xs text-brand-silver/70">
                                            <span class="inline-flex items-center gap-1.5 text-brand-silver font-medium">
                                                <i data-lucide="clock-3" class="w-3.5 h-3.5 text-gold-500/80 shrink-0"></i>
                                                Masa aktif: <strong class="text-white">{{ $durasi }} hari</strong>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Deskripsi Paket --}}
                                    <p class="text-xs text-brand-silver/85 leading-relaxed min-h-[38px] line-clamp-2 my-4">
                                        {{ $paket->deskripsi ?: 'Membership resmi BETA GYM dengan akses lengkap seluruh fasilitas.' }}
                                    </p>

                                    {{-- Fasilitas & Keuntungan Checklist (Diperbarui sesuai fasilitas gym riil) --}}
                                    <ul class="space-y-3 text-xs text-brand-silver/90 mb-6 flex-1">
                                        <li class="flex items-start gap-2.5">
                                            <span class="w-4 h-4 rounded-full bg-gold-500/20 text-gold-400 flex items-center justify-center shrink-0 mt-0.5 border border-gold-500/30">
                                                <i data-lucide="check" class="w-2.5 h-2.5 stroke-[3]"></i>
                                            </span>
                                            <span>Akses bebas seluruh area gym</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="w-4 h-4 rounded-full bg-gold-500/20 text-gold-400 flex items-center justify-center shrink-0 mt-0.5 border border-gold-500/30">
                                                <i data-lucide="check" class="w-2.5 h-2.5 stroke-[3]"></i>
                                            </span>
                                            <span>Loker penyimpanan pribadi yang aman</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="w-4 h-4 rounded-full bg-gold-500/20 text-gold-400 flex items-center justify-center shrink-0 mt-0.5 border border-gold-500/30">
                                                <i data-lucide="check" class="w-2.5 h-2.5 stroke-[3]"></i>
                                            </span>
                                            <span>Kartu absensi digital member</span>
                                        </li>
                                    </ul>

                                    {{-- Tombol Aksi Menarik & Berwarna --}}
                                    <div class="mt-auto pt-2">
                                        <a href="{{ $actionUrl }}"
                                           class="w-full py-3.5 px-5 rounded-2xl text-xs font-heading uppercase tracking-wider
                                                  flex items-center justify-center gap-2 transition-all duration-300 shadow-md
                                                  {{ $isFeatured
                                                      ? 'bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 hover:from-amber-300 hover:to-yellow-300 text-slate-950 font-black shadow-[0_4px_20px_rgba(245,158,11,0.45)] hover:shadow-[0_4px_25px_rgba(245,158,11,0.70)] hover:scale-[1.02]'
                                                      : 'bg-white/10 hover:bg-gold-500 text-white hover:text-brand-nav font-bold border border-white/15 hover:border-gold-500 hover:shadow-gold-glow hover:scale-[1.02]' }}">
                                            <span>{{ $actionLabel }}</span>
                                            <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                                        </a>
                                    </div>

                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- KEUNGGULAN FASILITAS GYM --}}
        {{-- ========================================================= --}}
        <section class="py-24 md:py-28 border-t border-white/[0.08] relative z-10 bg-black/35">
            <div class="container mx-auto px-6">
                <div class="max-w-2xl mx-auto text-center mb-14 md:mb-16">
                    <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Fasilitas Lengkap</span>
                    <h2 class="text-3xl md:text-4xl font-display font-bold text-white mt-2">
                        KENAPA BERLATIH DI <span class="text-transparent bg-clip-text bg-brand-gold">BETA GYM?</span>
                    </h2>
                    <p class="text-brand-silver/80 text-sm mt-3.5 max-w-xl mx-auto leading-relaxed">
                        Kenyamanan dan kelengkapan fasilitas Anda adalah komitmen utama kami.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-2">
                    {{-- Card 1: Peralatan Gym --}}
                    <div class="facility-card rounded-2xl p-6 flex flex-col items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                            <i data-lucide="dumbbell" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white text-base mb-1.5">Peralatan Gym Lengkap</h3>
                            <p class="text-brand-silver/75 text-xs leading-relaxed">
                                Variasi mesin beban, plate-loaded, cable machine, hingga dumbbell set lengkap untuk seluruh target latihan.
                            </p>
                        </div>
                    </div>

                    {{-- Card 2: Loker & Ruang Ganti (Tanpa Shower) --}}
                    <div class="facility-card rounded-2xl p-6 flex flex-col items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                            <svg class="w-6 h-6 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white text-base mb-1.5">Loker & Ruang Ganti</h3>
                            <p class="text-brand-silver/75 text-xs leading-relaxed">
                                Tersedia loker pribadi untuk menyimpan barang bawaan dengan aman serta area ruang ganti yang bersih.
                            </p>
                        </div>
                    </div>

                    {{-- Card 3: Coach Independen --}}
                    <div class="facility-card rounded-2xl p-6 flex flex-col items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white text-base mb-1.5">Coach Independen Handal</h3>
                            <p class="text-brand-silver/75 text-xs leading-relaxed">
                                Hubungi langsung coach profesional pilihan Anda untuk program latihan privat sesuai target Anda.
                            </p>
                        </div>
                    </div>

                    {{-- Card 4: Aplikasi Digital --}}
                    <div class="facility-card rounded-2xl p-6 flex flex-col items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-400 flex items-center justify-center shrink-0 shadow-inner">
                            <svg class="w-6 h-6 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="3" ry="3"/>
                                <circle cx="12" cy="18" r="1" fill="currentColor"/>
                                <line x1="9" y1="6" x2="15" y2="6" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white text-base mb-1.5">Aplikasi Digital Member</h3>
                            <p class="text-brand-silver/75 text-xs leading-relaxed">
                                Cek masa aktif, riwayat latihan, belanja di marketplace, hingga presensi kehadiran secara digital.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- FAQ SECTION --}}
        {{-- ========================================================= --}}
        <section class="py-24 md:py-28 z-10 relative">
            <div class="container mx-auto px-6 max-w-4xl">
                <div class="text-center mb-16 sm:mb-20">
                    <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Tanya Jawab</span>
                    <h2 class="text-3xl md:text-4xl font-display font-bold text-white mt-2.5">
                        PERTANYAAN SEPUTAR MEMBERSHIP
                    </h2>
                </div>

                <div class="space-y-4 pt-4 sm:pt-6" x-data="{ activeAccordion: null }">
                    {{-- FAQ 1 --}}
                    <div class="faq-card rounded-2xl overflow-hidden">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === 1 ? null : 1"
                                class="w-full py-5 px-6 text-left flex items-center justify-between gap-4 font-heading font-bold text-sm text-white hover:text-gold-400 transition-colors cursor-pointer select-none">
                            <span>Bagaimana cara mendaftar dan mengaktifkan membership?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gold-500 transition-transform duration-300 shrink-0" :class="{ 'rotate-180': activeAccordion === 1 }"></i>
                        </button>
                        <div x-show="activeAccordion === 1" x-collapse x-cloak class="px-6 pb-5 text-xs text-brand-silver/85 leading-relaxed border-t border-white/[0.08] pt-3.5 bg-black/25">
                            Anda cukup menekan tombol "Daftar & Gabung", mengisi form pendaftaran member, lalu memilih metode pembayaran yang tersedia (Transfer Bank atau QRIS). Setelah verifikasi, membership Anda akan langsung aktif.
                        </div>
                    </div>

                    {{-- FAQ 2 --}}
                    <div class="faq-card rounded-2xl overflow-hidden">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === 2 ? null : 2"
                                class="w-full py-5 px-6 text-left flex items-center justify-between gap-4 font-heading font-bold text-sm text-white hover:text-gold-400 transition-colors cursor-pointer select-none">
                            <span>Apakah ada biaya pendaftaran tambahan selain harga paket?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gold-500 transition-transform duration-300 shrink-0" :class="{ 'rotate-180': activeAccordion === 2 }"></i>
                        </button>
                        <div x-show="activeAccordion === 2" x-collapse x-cloak class="px-6 pb-5 text-xs text-brand-silver/85 leading-relaxed border-t border-white/[0.08] pt-3.5 bg-black/25">
                            Tidak ada biaya pendaftaran tersembunyi! Biaya yang tertera sudah mencakup akses penuh ke fasilitas gym sesuai durasi paket yang Anda pilih.
                        </div>
                    </div>

                    {{-- FAQ 3 --}}
                    <div class="faq-card rounded-2xl overflow-hidden">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === 3 ? null : 3"
                                class="w-full py-5 px-6 text-left flex items-center justify-between gap-4 font-heading font-bold text-sm text-white hover:text-gold-400 transition-colors cursor-pointer select-none">
                            <span>Apakah saya bisa memperpanjang paket sebelum masa aktif habis?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gold-500 transition-transform duration-300 shrink-0" :class="{ 'rotate-180': activeAccordion === 3 }"></i>
                        </button>
                        <div x-show="activeAccordion === 3" x-collapse x-cloak class="px-6 pb-5 text-xs text-brand-silver/85 leading-relaxed border-t border-white/[0.08] pt-3.5 bg-black/25">
                            Tentu saja! Anda dapat melakukan perpanjangan paket membership kapan saja melalui dashboard member. Durasi paket baru akan otomatis diakumulasikan ke sisa masa aktif Anda.
                        </div>
                    </div>
                </div>

                {{-- Bottom Support Box --}}
                <div class="mt-12 p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4"
                     style="background: linear-gradient(180deg, rgba(212,167,87,0.14) 0%, rgba(212,167,87,0.05) 100%), #171b25; border: 1px solid rgba(212,167,87,0.35); box-shadow: 0 12px 32px rgba(0,0,0,0.5);">
                    <div class="text-left">
                        <h4 class="font-heading font-bold text-white text-sm">Masih punya pertanyaan lain seputar membership?</h4>
                        <p class="text-brand-silver/70 text-xs mt-0.5">Admin BETA GYM siap membantu menjawab kebutuhan Anda.</p>
                    </div>
                    <a href="https://wa.me/6285376754025?text={{ urlencode('Halo Admin BETA GYM, saya ingin bertanya tentang paket membership.') }}"
                       target="_blank" rel="noopener"
                       class="px-5 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#1da851] text-white font-bold text-xs flex items-center gap-2 shadow-lg transition-all shrink-0 hover:scale-105">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        Tanya Admin via WA
                    </a>
                </div>

            </div>
        </section>

    </div>

</x-layouts.guest>
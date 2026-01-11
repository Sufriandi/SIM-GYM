{{-- resources/views/member/paket_membership/index.blade.php --}}

@php
    use Illuminate\Support\Str;

    $pageTitle    = $pageTitle    ?? 'Paket Membership';
    $pageSubtitle = $pageSubtitle ?? 'Pilih paket membership, lakukan pembayaran, lalu upload bukti untuk verifikasi admin.';

    // kompatibel: ?search=... (utama) atau ?q=... (fallback)
    $search = $search ?? request('search', request('q', ''));

    // Filter tipe: ?tipe=single|double|triple
    $currentTipe = $currentTipe ?? request('tipe', '');

    // Data
    $pakets = $pakets ?? ($paketMemberships ?? collect());

    $fmt = fn($v) => number_format((int)$v, 0, ',', '.');
    $totalCount = method_exists($pakets, 'total') ? $pakets->total() : (is_countable($pakets) ? count($pakets) : 0);

    $tipeLabel = fn($t) => match ((string)$t) {
        'double' => 'DOUBLE',
        'triple' => 'TRIPLE',
        default  => 'SINGLE',
    };
@endphp

<x-layouts.member
    :title="$pageTitle"
    :page-title="$pageTitle"
    :page-subtitle="$pageSubtitle"
>
    {{-- ========================================================= --}}
    {{-- HERO (tanpa gambar) --}}
    {{-- ========================================================= --}}
    <section class="relative overflow-hidden rounded-3xl border border-brand-borderSoft/60 shadow-card-strong mb-8">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/20"></div>

            <div class="absolute -top-24 -right-24 w-[560px] h-[560px] bg-gold-500/12 rounded-full blur-[160px]"></div>
            <div class="absolute -bottom-24 -left-24 w-[560px] h-[560px] bg-accent-500/10 rounded-full blur-[170px]"></div>

            <div class="absolute inset-0 opacity-[0.07]"
                 style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 38px 38px;"></div>
        </div>

        <div class="relative z-10 px-6 md:px-10 py-10 md:py-12">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8">
                <div class="max-w-2xl">
                    <span class="text-gold-300 font-bold tracking-widest uppercase text-xs font-heading block">
                        Membership
                    </span>

                    <h1 class="mt-3 font-display font-extrabold leading-[0.95] tracking-tight
                               text-[clamp(34px,4.6vw,64px)] text-white">
                        PILIH<br>
                        <span class="text-transparent bg-clip-text bg-brand-gold">PAKET TERBAIKMU</span>
                    </h1>

                    <p class="text-white/80 text-base md:text-lg mt-5 max-w-lg leading-relaxed">
                        Buat transaksi, lakukan pembayaran, lalu upload bukti. Admin akan memverifikasi agar membership aktif dengan aman.
                    </p>
                </div>

                {{-- Search + Filter (menyatu) --}}
                <div class="w-full lg:w-[520px]">
                    <form method="GET" action="{{ route('member.paket_membership.index') }}" class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                class="w-full pl-5 pr-5 py-4 rounded-full
                                       bg-white/10 backdrop-blur-md text-white placeholder:text-white/60
                                       border border-white/15 focus:outline-none focus:ring-2 focus:ring-gold-500/30 focus:border-gold-500/30 transition"
                                placeholder="Cari nama paket…"
                            >
                            @if($currentTipe)
                                <input type="hidden" name="tipe" value="{{ $currentTipe }}">
                            @endif
                        </div>

                        {{-- Filter Button (Alpine dropdown) --}}
                        <div class="relative" x-data="{ open: false }">
                            <button
                                type="button"
                                @click="open = !open"
                                @click.outside="open = false"
                                class="w-12 h-12 flex items-center justify-center rounded-full transition-all cursor-pointer relative group"
                                :class="open
                                    ? 'bg-gold-500 text-brand-nav'
                                    : 'bg-black/5 text-brand-nav/55 hover:bg-black/10 hover:text-brand-nav dark:bg-brand-surface-200/20 dark:text-brand-silver dark:hover:bg-brand-surface-200/40 dark:hover:text-white'">

                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                                </svg>

                                @if($currentTipe)
                                    <span class="absolute top-3 right-3 w-2 h-2 bg-gold-500 rounded-full border border-white dark:border-brand-sidebar" x-show="!open"></span>
                                @endif
                            </button>

                            <div
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                class="absolute right-0 top-full mt-4 w-60
                                       bg-brand-card dark:bg-[#1a1a1a]
                                       border border-brand-borderSoft/45 dark:border-brand-borderSoft/30
                                       rounded-2xl shadow-2xl overflow-hidden py-2 z-[95]
                                       ring-1 ring-black/5 dark:ring-white/10"
                                style="display: none;">

                                <div class="px-4 py-3 border-b border-brand-borderSoft/30 dark:border-brand-borderSoft/10 bg-black/2 dark:bg-brand-surface-200/5 mb-1">
                                    <p class="text-[10px] font-bold text-brand-nav/60 dark:text-brand-silver uppercase tracking-widest">
                                        Filter Tipe Paket
                                    </p>
                                </div>

                                <div class="max-h-64 overflow-y-auto custom-scrollbar p-1">
                                    <a
                                        href="{{ route('member.paket_membership.index', array_filter(['search' => $search])) }}"
                                        class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors
                                               {{ !$currentTipe
                                                    ? 'bg-gold-500 text-brand-nav'
                                                    : 'text-brand-nav hover:bg-black/5 dark:text-brand-white dark:hover:bg-brand-surface-200/20' }}">
                                        <span>Semua Tipe</span>
                                    </a>

                                    @foreach(['single' => 'Single', 'double' => 'Double', 'triple' => 'Triple'] as $key => $label)
                                        <a
                                            href="{{ route('member.paket_membership.index', array_filter(['tipe' => $key, 'search' => $search])) }}"
                                            class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors
                                                   {{ $currentTipe === $key
                                                        ? 'bg-gold-500 text-brand-nav'
                                                        : 'text-brand-nav hover:bg-black/5 dark:text-brand-white dark:hover:bg-brand-surface-200/20' }}">
                                            <span>{{ $label }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Submit icon (opsional): tetap minimal, search bisa enter --}}
                        <button
                            type="submit"
                            class="hidden"
                            aria-hidden="true"
                            tabindex="-1"
                        ></button>
                    </form>

                    @if($search || $currentTipe)
                        <div class="mt-2 text-right">
                            <a href="{{ route('member.paket_membership.index') }}"
                               class="text-xs font-bold text-gold-300 hover:text-gold-200 transition">
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold-500/30 to-transparent"></div>
    </section>

    {{-- ========================================================= --}}
    {{-- GRID --}}
    {{-- ========================================================= --}}
    <div class="space-y-6">
        @if($totalCount <= 0)
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="w-24 h-24 rounded-3xl bg-brand-card border border-brand-borderSoft flex items-center justify-center mb-6 shadow-card-soft">
                    <i data-lucide="package-x" class="w-10 h-10 text-brand-textSoft/60"></i>
                </div>
                <h3 class="text-2xl font-bold font-display text-brand-text mb-2">Paket Tidak Ditemukan</h3>
                <p class="text-brand-textSoft max-w-md">
                    @if($search || $currentTipe)
                        Tidak ada paket yang cocok dengan filter saat ini.
                    @else
                        Belum ada paket membership yang tersedia.
                    @endif
                </p>
            </div>
        @else
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-brand-text">
                    Paket Tersedia
                    <span class="text-gold-500">({{ $totalCount }})</span>
                </h3>
            </div>

            {{-- Mobile 2 kolom, md 3 kolom, xl 4 kolom --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-7">
                @foreach($pakets as $p)
                    @php
                        $name = $p->nama ?? 'Membership';
                        $id   = $p->id;
                        $tipe = (string)($p->tipe ?? 'single');
                    @endphp

                    <div class="group rounded-3xl overflow-hidden bg-brand-card border border-brand-borderSoft shadow-card-soft
                                hover:shadow-card-strong transition-all duration-300 flex flex-col">
                        {{-- Header (tanpa gambar) --}}
                        <div class="p-4 sm:p-5 border-b border-brand-borderSoft/40">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold tracking-widest uppercase
                                                     bg-gold-500/90 text-brand-nav border border-gold-500/20 shadow-gold-glow">
                                            {{ $tipeLabel($tipe) }}
                                        </span>
                                        <span class="px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold
                                                     bg-brand-shell text-brand-text border border-brand-borderSoft">
                                            {{ (int)($p->durasi ?? 0) }} Hari
                                        </span>
                                    </div>

                                    <h3 class="text-sm sm:text-lg font-bold font-heading text-brand-text line-clamp-2">
                                        {{ $name }}
                                    </h3>
                                </div>

                                <div class="w-11 h-11 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center shrink-0">
                                    <i data-lucide="package" class="w-5 h-5 text-gold-500"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="p-4 sm:p-5 flex flex-col flex-1">
                            <div class="flex items-end justify-between gap-3">
                                <div class="text-brand-textSoft text-[11px] sm:text-xs">
                                    Harga Paket
                                </div>
                                <div class="text-gold-500 font-extrabold font-display text-base sm:text-lg">
                                    Rp {{ $fmt($p->harga ?? 0) }}
                                </div>
                            </div>

                            <p class="mt-3 text-[11px] sm:text-sm text-brand-textSoft leading-relaxed line-clamp-3 min-h-[48px]">
                                {{ $p->deskripsi ?: 'Pilih paket ini untuk berlangganan membership. Lakukan pembayaran dan upload bukti untuk verifikasi admin.' }}
                            </p>

                            {{-- Actions --}}
                            <div class="mt-5 sm:mt-6 grid grid-cols-5 gap-3">
                                <a
                                    href="{{ route('member.paket_membership.show', $id) }}"
                                    class="col-span-2 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                           bg-brand-shell hover:bg-brand-surface-100 text-brand-text transition
                                           border border-brand-borderSoft flex items-center justify-center"
                                >
                                    Detail
                                </a>

                                <a
                                    href="{{ route('member.paket_membership.checkout', $id) }}"
                                    class="col-span-3 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                           text-brand-nav transition flex items-center justify-center gap-2
                                           bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700
                                           border border-gold-500/20 shadow-gold-glow hover:-translate-y-[1px]"
                                >
                                    <i data-lucide="credit-card" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                    Beli
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(method_exists($pakets, 'hasPages') && $pakets->hasPages())
                <div class="mt-10">
                    {{ $pakets->appends(['search' => $search, 'tipe' => $currentTipe])->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.member>

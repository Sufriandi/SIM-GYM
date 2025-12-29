{{-- resources/views/guest/home.blade.php --}}
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use App\Models\PaketMembership;
    use App\Models\Coach;

    $pageTitle   = $pageTitle ?? 'BETA GYM – Build a Better You';
    $registerUrl = Route::has('register') ? route('register') : url('/register');

    // =========================
    // DATA
    // =========================
    $paketMemberships = PaketMembership::orderBy('harga', 'asc')->get();

    // tampilkan 6 coach saja (featured), tapi total tetap bisa ditampilkan kalau mau
    $coachTotal   = Coach::count();
    $coaches      = Coach::inRandomOrder()->limit(6)->get();

    // Produk: cari model yang ada (agar tidak memaksa nama tertentu)
    $productModelClass = null;
    foreach ([
        'App\\Models\\Produk',
        'App\\Models\\Product',
        'App\\Models\\ProdukMarketplace',
        'App\\Models\\ProdukGym',
        'App\\Models\\ProdukItem',
    ] as $cls) {
        if (class_exists($cls)) { $productModelClass = $cls; break; }
    }
    $products = $productModelClass ? $productModelClass::inRandomOrder()->limit(10)->get() : collect();

    // =========================
    // TOP 3 PACKAGE (tetap premium)
    // =========================
    $bestValue = null;
    $longest   = null;

    if ($paketMemberships->count() > 0) {
        $bestValue = $paketMemberships
            ->filter(fn($p) => (int)($p->durasi ?? 0) > 0 && (float)($p->harga ?? 0) > 0)
            ->sortBy(fn($p) => ((float)$p->harga) / max(1, (int)$p->durasi))
            ->first();

        $longest = $paketMemberships->sortByDesc('durasi')->first();
    }

    $selected = collect();
    if ($bestValue) $selected->push($bestValue);
    if ($longest && (!$bestValue || $longest->id !== $bestValue->id)) $selected->push($longest);

    $third = $paketMemberships->values()->get((int) floor(($paketMemberships->count() - 1) / 2));
    if ($third && !$selected->contains('id', $third->id)) $selected->push($third);

    foreach ($paketMemberships as $p) {
        if ($selected->count() >= 3) break;
        if (!$selected->contains('id', $p->id)) $selected->push($p);
    }

    $paketTop3 = $selected->take(3);

    // =========================
    // ROUTES (aman + fleksibel)
    // =========================
    $allPaketUrl = Route::has('guest.membership.index') ? route('guest.membership.index')
                : (Route::has('guest.paket-membership.index') ? route('guest.paket-membership.index')
                : url('/membership'));

    $allCoachUrl = Route::has('guest.coaches.index') ? route('guest.coaches.index')
               : (Route::has('guest.coach.index') ? route('guest.coach.index')
               : url('/coaches'));

    $allProductUrl = Route::has('guest.products.index') ? route('guest.products.index')
                 : (Route::has('guest.produk.index') ? route('guest.produk.index')
                 : url('/products'));

    // =========================
    // HELPERS
    // =========================
    $mockupUrl = asset('images/' . rawurlencode('mockup hp.png'));

    $coachImg = function ($path) {
        $path = trim((string) $path);
        $fallback = 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?q=80&w=2070&auto=format&fit=crop';

        if ($path === '') return $fallback;
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/'))  return Storage::url($path);

        return Storage::url($path);
    };

    $coachDetailUrl = function ($coach) {
        if (Route::has('guest.coaches.show')) return route('guest.coaches.show', $coach->id);
        if (Route::has('guest.coach.show'))   return route('guest.coach.show', $coach->id);
        return url('/coaches/' . $coach->id);
    };

    // WA link coach (JANGAN taruh di href agar tidak “terlihat” saat hover)
    $coachWaUrl = function ($coach) {
        $raw = (string) ($coach->no_hp ?? '');
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits !== '') {
            if (Str::startsWith($digits, '0')) $digits = '62' . substr($digits, 1);
            if (Str::startsWith($digits, '8')) $digits = '62' . $digits;
        }

        if ($digits === '') return null;

        $name = $coach->nama ?? 'Coach';
        $msg  = "Halo $name, saya mau tanya jadwal dan layanan coaching di BETA GYM.";
        return "https://wa.me/{$digits}?text=" . urlencode($msg);
    };

    $productImg = function ($path) {
        $path = trim((string) $path);

        $fallback = 'https://images.unsplash.com/photo-1599058918144-1ffabb6ab9a0?q=80&w=2070&auto=format&fit=crop';
        if ($path === '') return $fallback;

        if (Str::startsWith($path, ['http://', 'https://'])) return $path;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/'))  return Storage::url($path);

        return Storage::url($path);
    };

    $productDetailUrl = function ($product) {
        if (Route::has('guest.products.show')) return route('guest.products.show', $product->id);
        if (Route::has('guest.produk.show'))   return route('guest.produk.show', $product->id);
        return url('/products/' . $product->id);
    };

    // copywriting highlight (lebih “masuk” untuk publik)
    $highlights = [
        ['icon'=>'shield-check', 'title'=>'Membership Lebih Pasti',  'desc'=>'Status aktif dan masa berlaku jelas, jadi Anda tahu progresnya dari awal.'],
        ['icon'=>'qr-code',      'title'=>'Absensi Lebih Rapi',      'desc'=>'Pencatatan kunjungan cepat dan konsisten untuk kebutuhan evaluasi gym.'],
        ['icon'=>'wallet',       'title'=>'Pembayaran Lebih Nyaman', 'desc'=>'Instruksi pembayaran ringkas, proses konfirmasi lebih terarah.'],
        ['icon'=>'shopping-bag', 'title'=>'Produk Pendukung Latihan', 'desc'=>'Kebutuhan latihan tersedia dan mudah dicari dalam satu tempat.'],
    ];
@endphp

<x-layouts.guest :title="$pageTitle">
    {{-- ========================================================= --}}
    {{-- Local polish: reveal + surface contrast (tanpa garis putih) --}}
    {{-- ========================================================= --}}
    <style>
        
        /* Reveal animation (kiri/kanan/atas) */
        [data-reveal]{
            opacity: 0;
            transform: translate3d(0,18px,0);
            filter: blur(6px);
            transition: opacity .85s cubic-bezier(.2,.8,.2,1),
                        transform .85s cubic-bezier(.2,.8,.2,1),
                        filter .85s cubic-bezier(.2,.8,.2,1);
            will-change: opacity, transform, filter;
        }
        [data-reveal="left"]{ transform: translate3d(-26px,0,0); }
        [data-reveal="right"]{ transform: translate3d(26px,0,0); }
        [data-reveal="up"]{ transform: translate3d(0,26px,0); }
        .is-visible{
            opacity: 1 !important;
            transform: translate3d(0,0,0) !important;
            filter: blur(0) !important;
        }

        /* Hilangkan “white hairline” yang sering muncul dari border terang */
        .surface-card{
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(212,167,87,.14); /* gold-soft */
            box-shadow: 0 18px 50px rgba(0,0,0,.45);
        }
        .surface-card:hover{
            border-color: rgba(212,167,87,.22);
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
        }

        /* Scrollbar halus */
        .soft-scroll::-webkit-scrollbar{ height: 10px; }
        .soft-scroll::-webkit-scrollbar-track{ background: rgba(255,255,255,.04); border-radius: 999px; }
        .soft-scroll::-webkit-scrollbar-thumb{ background: rgba(212,167,87,.22); border-radius: 999px; }
        .soft-scroll{ scrollbar-color: rgba(212,167,87,.22) rgba(255,255,255,.04); scrollbar-width: thin; }

        /* Auto-scroll UX */
        .autoscroll-track{ -webkit-overflow-scrolling: touch; }
        .autoscroll-playing{ scroll-snap-type: none !important; }

        /* Mobile safe viewport helper */
        .min-svh{ min-height: 100svh; }
    </style>

    <div class="dark bg-brand-bg text-brand-text min-h-screen">

        {{-- ========================================================= --}}
        {{-- HERO (1 layar) --}}
        {{-- ========================================================= --}}
        <section class="relative overflow-hidden min-svh flex items-center">
            {{-- background --}}
            <div class="absolute inset-0">
                <img
                    src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop"
                    alt="Gym Background"
                    class="w-full h-full object-cover opacity-25"
                >
                <div class="absolute inset-0 bg-brand-overlay-premium"></div>

                {{-- glows --}}
                <div class="absolute -top-24 -right-24 w-[620px] h-[620px] bg-gold-500/10 rounded-full blur-[170px]"></div>
                <div class="absolute -bottom-24 -left-24 w-[620px] h-[620px] bg-accent-500/10 rounded-full blur-[180px]"></div>

                {{-- texture --}}
                <div class="absolute inset-0 opacity-[0.06]"
                     style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 38px 38px;"></div>
            </div>

            <div class="relative w-full container mx-auto px-6 py-16 md:py-20">
                <div class="grid lg:grid-cols-12 gap-10 items-center">
                    {{-- LEFT --}}
                    <div class="lg:col-span-7 space-y-7" data-reveal="left" data-delay="0">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-pill
                                    bg-black/20 backdrop-blur border border-gold-500/20">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-60"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-gold-500"></span>
                            </span>
                            <span class="text-[10px] font-bold tracking-widest text-gold-500 uppercase font-heading">
                                BETA GYM • DARK PREMIUM
                            </span>
                        </div>

                        <div class="space-y-4">
                            <h1 class="font-display font-extrabold leading-[0.95] tracking-tight
                                       text-[clamp(40px,5.2vw,74px)]">
                                LATIHAN JADI<br>
                                <span class="text-transparent bg-clip-text bg-brand-gold">LEBIH TERARAH.</span>
                            </h1>
                            <p class="text-base md:text-lg text-brand-textSoft max-w-2xl leading-relaxed">
                                Program latihan lebih jelas, pilihan paket mudah dipahami, dan coach siap membantu sesuai kebutuhan.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-1">
                            <a href="{{ $registerUrl }}"
                               class="btn-primary bg-gold-500/85 hover:bg-gold-500/95
                                      border border-gold-500/20 shadow-gold-glow hover:shadow-gold-glow-strong transition">
                                Daftar Member
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </a>

                            <a href="#membership"
                               class="btn-ghost bg-black/15 hover:bg-black/20
                                      border border-gold-500/18 hover:border-gold-500/24 transition">
                                Lihat Paket
                                <i data-lucide="layers" class="w-5 h-5 text-gold-500"></i>
                            </a>
                        </div>

                        {{-- quick info (tanpa “white line”) --}}
                        <div class="grid sm:grid-cols-3 gap-4 pt-6">
                            <div class="surface-card rounded-2xl p-4 sm:p-5" data-reveal="up" data-delay="120">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-black/20 flex items-center justify-center">
                                        <i data-lucide="clock" class="w-5 h-5 text-gold-500"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-brand-textSoft font-bold tracking-wider uppercase">Jam Operasional</p>
                                        <p class="font-bold">Setiap Hari</p>
                                    </div>
                                </div>
                            </div>

                            <div class="surface-card rounded-2xl p-4 sm:p-5" data-reveal="up" data-delay="180">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-black/20 flex items-center justify-center">
                                        <i data-lucide="map-pin" class="w-5 h-5 text-gold-500"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-brand-textSoft font-bold tracking-wider uppercase">Lokasi</p>
                                        <p class="font-bold truncate">Bengkalis</p>
                                    </div>
                                </div>
                            </div>

                            <div class="surface-card rounded-2xl p-4 sm:p-5" data-reveal="up" data-delay="240">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-black/20 flex items-center justify-center">
                                        <i data-lucide="sparkles" class="w-5 h-5 text-gold-500"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-brand-textSoft font-bold tracking-wider uppercase">Fokus</p>
                                        <p class="font-bold">Latihan Konsisten</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT --}}
                    <div class="lg:col-span-5 hidden lg:block" data-reveal="right" data-delay="120">
                        <div class="relative rounded-3xl overflow-hidden surface-card">
                            <div class="absolute inset-0 bg-premium-dark-gold opacity-70 pointer-events-none"></div>

                            <div class="relative p-6">
                                <div class="rounded-2xl overflow-hidden"
                                     style="border:1px solid rgba(212,167,87,.18);">
                                    <div class="aspect-[4/5] bg-black/20">
                                        <img
                                            src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=2070&auto=format&fit=crop"
                                            alt="Athlete"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>
                                </div>

                                
                            </div>

                            <div class="absolute -top-16 -right-16 w-64 h-64 bg-gold-500/10 rounded-full blur-3xl pointer-events-none"></div>
                            <div class="absolute -bottom-16 -left-16 w-64 h-64 bg-accent-500/10 rounded-full blur-3xl pointer-events-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
{{-- INFORMASI GYM (REVISI UI HIGHLIGHTS) --}}
{{-- ========================================================= --}}
<section class="py-20 md:py-24 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 opacity-[0.05]"
             style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 42px 42px;"></div>
        <div class="absolute -top-28 left-1/3 w-[520px] h-[520px] bg-gold-500/7 rounded-full blur-[160px]"></div>
        <div class="absolute -bottom-24 right-1/4 w-[520px] h-[520px] bg-accent-500/7 rounded-full blur-[170px]"></div>
    </div>

    <div class="container mx-auto px-6 relative">
        <div class="grid lg:grid-cols-12 gap-10 items-start">
            {{-- LEFT --}}
            <div class="lg:col-span-5" data-reveal="left" data-delay="0">
                <div class="inline-flex items-center gap-2">
                    <span class="h-[2px] w-10 bg-gold-500/70 rounded-full"></span>
                    <span class="text-[11px] font-bold tracking-[0.25em] text-gold-500 uppercase font-heading">
                        Informasi Gym
                    </span>
                </div>

                <h2 class="mt-4 font-display font-extrabold leading-[0.95]
                           text-[clamp(34px,4.2vw,56px)]">
                    LEBIH MUDAH<br>
                    <span class="text-transparent bg-clip-text bg-brand-gold">DATANG & MULAI.</span>
                </h2>

                <p class="mt-4 text-brand-textSoft leading-relaxed">
                    Lokasi, jam operasional, dan fasilitas ditampilkan jelas agar Anda bisa datang dengan persiapan yang tepat.
                </p>

                <div class="mt-6 flex flex-col gap-3">
                    <div class="surface-card rounded-2xl p-5" data-reveal="up" data-delay="90">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/20 flex items-center justify-center">
                                <i data-lucide="map-pin" class="w-5 h-5 text-gold-500"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold">Alamat</p>
                                <p class="text-sm text-brand-textSoft">
                                    (Isi alamat gym Anda di sini) • Bengkalis, Riau
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card rounded-2xl p-5" data-reveal="up" data-delay="150">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/20 flex items-center justify-center">
                                <i data-lucide="clock-3" class="w-5 h-5 text-gold-500"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold">Jam Operasional</p>
                                <p class="text-sm text-brand-textSoft">
                                    Senin–Minggu • (contoh) 06:00–22:00
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card rounded-2xl p-5" data-reveal="up" data-delay="210">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/20 flex items-center justify-center">
                                <i data-lucide="building-2" class="w-5 h-5 text-gold-500"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold">Fasilitas</p>
                                <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-2 text-sm text-brand-textSoft">
                                    <div class="flex items-center gap-2"><span class="text-gold-500">✓</span> Parkir</div>
                                    <div class="flex items-center gap-2"><span class="text-gold-500">✓</span> Loker</div>
                                    <div class="flex items-center gap-2"><span class="text-gold-500">✓</span> Ruang Ganti</div>
                                    <div class="flex items-center gap-2"><span class="text-gold-500">✓</span> Area Latihan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT (HIGHLIGHTS BARU) --}}
            <div class="lg:col-span-7" data-reveal="right" data-delay="120">
                <div class="grid sm:grid-cols-2 gap-5 lg:gap-6">
                    @foreach($highlights as $idx => $h)
                        <div
                            class="group relative rounded-3xl p-6 sm:p-7 overflow-hidden
                                   bg-gradient-to-br from-white/[0.07] via-white/[0.035] to-black/25
                                   border border-gold-500/15
                                   shadow-[0_18px_60px_rgba(0,0,0,0.55)]
                                   hover:border-gold-500/28 hover:-translate-y-1
                                   transition duration-300"
                            data-reveal="up"
                            data-delay="{{ 140 + $idx*70 }}"
                        >
                            {{-- glow halus --}}
                            <div class="pointer-events-none absolute -top-24 -right-24 w-64 h-64 bg-gold-500/10 rounded-full blur-[90px]"></div>
                            <div class="pointer-events-none absolute -bottom-28 -left-24 w-72 h-72 bg-accent-500/10 rounded-full blur-[110px]"></div>

                            {{-- shine hover --}}
                            <div
                                class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition duration-500"
                                style="background: radial-gradient(650px circle at 18% 12%, rgba(212,167,87,.16), transparent 45%);"
                            ></div>

                            {{-- accent line --}}
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-[2px]
                                        bg-gradient-to-r from-transparent via-gold-500/45 to-transparent opacity-70"></div>

                            <div class="relative flex items-start gap-4">
                                <div class="shrink-0 w-12 h-12 rounded-2xl bg-black/25
                                            flex items-center justify-center
                                            border border-gold-500/18
                                            shadow-[0_12px_30px_rgba(0,0,0,0.35)]">
                                    <i data-lucide="{{ $h['icon'] }}" class="w-6 h-6 text-gold-500"></i>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="text-lg sm:text-xl font-extrabold font-heading tracking-tight">
                                        {{ $h['title'] }}
                                    </h3>

                                    <p class="mt-2 text-sm text-brand-textSoft leading-relaxed">
                                        {{ $h['desc'] }}
                                    </p>

                                    <div class="mt-5 inline-flex items-center gap-2 text-[11px]
                                                font-bold tracking-widest uppercase text-gold-500/90">
                                        <span class="inline-block h-[2px] w-7 bg-gold-500/60 rounded-full"></span>
                                        <span class="opacity-80 group-hover:opacity-100 transition">Lebih nyaman dipakai</span>
                                        <i data-lucide="arrow-right"
                                           class="w-4 h-4 opacity-0 -translate-x-1
                                                  group-hover:opacity-100 group-hover:translate-x-0 transition"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>


        {{-- ========================================================= --}}
{{-- MEMBERSHIP --}}
{{-- ========================================================= --}}
<section class="py-20 md:py-24 relative overflow-hidden" id="membership">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-brand-radial-gold opacity-12"></div>
        <div class="absolute -top-24 right-1/3 w-[640px] h-[640px] bg-gold-500/8 rounded-full blur-[170px]"></div>
    </div>

    <div class="container mx-auto px-6 relative">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
            <div class="max-w-2xl" data-reveal="left" data-delay="0">
                <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Membership</span>
                <h2 class="mt-2 font-display leading-[0.95]
                           text-[clamp(34px,4.5vw,60px)]">
                    PILIH PAKET<br>
                    <span class="text-transparent bg-clip-text bg-brand-gold">SESUAI TARGET ANDA</span>
                </h2>
                <p class="text-brand-textSoft mt-4">
                    Mulai dari yang paling ringan sampai yang paling panjang, pilih yang paling nyaman untuk ritme latihan Anda.
                </p>
            </div>

            <div data-reveal="right" data-delay="120">
                <a href="{{ $allPaketUrl }}"
                   class="inline-flex items-center gap-2 text-gold-500 font-bold hover:text-gold-400 transition">
                    Lihat Paket Lainnya
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        @if($paketTop3->count() > 0)

            {{-- ========================================================= --}}
            {{-- MOBILE/TABLET: 1 card + swipe (snap) + optional buttons (md) --}}
            {{-- ========================================================= --}}
            <div class="lg:hidden -mx-6 px-6" data-reveal="up" data-delay="80">
                <div data-carousel id="membershipCarousel" data-nudge="1" class="relative">
                    {{-- Prev / Next (tampil mulai md, tidak mengganggu mobile kecil) --}}
                    <button type="button"
                            data-carousel-prev
                            class="hidden md:flex items-center justify-center
                                   absolute left-0 top-1/2 -translate-y-1/2 z-10
                                   w-10 h-10 rounded-full
                                   bg-black/35 backdrop-blur border border-gold-500/20
                                   text-gold-500 hover:text-gold-400 hover:border-gold-500/30 transition">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>

                    <button type="button"
                            data-carousel-next
                            class="hidden md:flex items-center justify-center
                                   absolute right-0 top-1/2 -translate-y-1/2 z-10
                                   w-10 h-10 rounded-full
                                   bg-black/35 backdrop-blur border border-gold-500/20
                                   text-gold-500 hover:text-gold-400 hover:border-gold-500/30 transition">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>

                    <div id="membershipTrack"
                         data-carousel-track
                         class="flex gap-5 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-5
                                soft-scroll custom-scrollbar overscroll-x-contain
                                scroll-px-6 touch-auto
                                select-none md:cursor-grab md:active:cursor-grabbing">

                        @foreach($paketTop3 as $idx => $paket)
                            @php
                                $harga  = (float)($paket->harga ?? 0);
                                $durasi = (int)($paket->durasi ?? 0);
                                $tipe   = trim((string)($paket->tipe ?? ''));

                                $badge = null;
                                if ($bestValue && $paket->id === $bestValue->id) $badge = 'Rekomendasi';
                                elseif ($longest && $paket->id === $longest->id) $badge = 'Durasi Panjang';

                                $isFeatured = ($badge === 'Rekomendasi');
                            @endphp

                            <div class="snap-center flex-none w-[88%] sm:w-[420px] md:w-[520px]">
                                <div class="rounded-3xl overflow-hidden surface-card">
                                    <div class="p-6 sm:p-7">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="text-lg sm:text-xl font-bold font-heading truncate">{{ $paket->nama }}</h3>
                                                <p class="text-xs text-brand-textSoft mt-1">
                                                    {{ $durasi > 0 ? "Durasi {$durasi} hari" : "Durasi fleksibel" }}
                                                </p>
                                            </div>

                                            <div class="flex flex-col items-end gap-2">
                                                @if($badge)
                                                    <span class="px-3 py-1 rounded-pill text-[10px] font-bold tracking-widest uppercase
                                                                 bg-black/20 text-gold-500"
                                                          style="border:1px solid rgba(212,167,87,.18);">
                                                        {{ $badge }}
                                                    </span>
                                                @endif

                                                @if($tipe !== '')
                                                    <span class="px-3 py-1 rounded-pill text-[10px] font-bold tracking-widest uppercase
                                                                 bg-black/20 text-brand-textSoft"
                                                          style="border:1px solid rgba(212,167,87,.12);">
                                                        {{ $tipe }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-6">
                                            <p class="font-display font-extrabold leading-none
                                                      text-[clamp(34px,7vw,48px)]
                                                      {{ $isFeatured ? 'text-transparent bg-clip-text bg-brand-gold' : '' }}">
                                                RP {{ number_format($harga, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <p class="mt-4 text-sm text-brand-textSoft leading-relaxed line-clamp-3">
                                            {{ $paket->deskripsi ?: 'Paket membership sesuai durasi dan kebijakan gym.' }}
                                        </p>

                                        <div class="mt-7">
                                            <a href="{{ $registerUrl }}"
                                               class="w-full inline-flex items-center justify-center gap-2 px-7 py-4 rounded-pill font-heading font-bold
                                                      {{ $isFeatured
                                                          ? 'bg-gold-500/85 hover:bg-gold-500/95 text-brand-nav shadow-gold-glow hover:shadow-gold-glow-strong'
                                                          : 'bg-black/18 hover:bg-black/24 text-brand-text' }}
                                                      transition"
                                               style="border:1px solid rgba(212,167,87,.18);">
                                                Pilih Paket
                                                <i data-lucide="arrow-right" class="w-5 h-5 {{ $isFeatured ? '' : 'text-gold-500' }}"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="h-1 w-full" style="background:rgba(212,167,87,{{ $isFeatured ? '.75' : '.35' }});"></div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- DESKTOP: 3 kolom --}}
            {{-- ========================================================= --}}
            <div class="hidden lg:grid lg:grid-cols-3 gap-6 items-stretch">
                @foreach($paketTop3 as $idx => $paket)
                    @php
                        $harga  = (float)($paket->harga ?? 0);
                        $durasi = (int)($paket->durasi ?? 0);
                        $tipe   = trim((string)($paket->tipe ?? ''));

                        $isCenter = ($idx === 1);

                        $badge = null;
                        if ($bestValue && $paket->id === $bestValue->id) $badge = 'Rekomendasi';
                        elseif ($longest && $paket->id === $longest->id) $badge = 'Durasi Panjang';
                    @endphp

                    <div data-reveal="{{ $idx === 0 ? 'left' : ($idx === 1 ? 'up' : 'right') }}"
                         data-delay="{{ 80 + $idx*120 }}">
                        <div class="rounded-3xl overflow-hidden surface-card">
                            <div class="p-6 sm:p-7">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-lg sm:text-xl font-bold font-heading truncate">{{ $paket->nama }}</h3>
                                        <p class="text-xs text-brand-textSoft mt-1">
                                            {{ $durasi > 0 ? "Durasi {$durasi} hari" : "Durasi fleksibel" }}
                                        </p>
                                    </div>

                                    <div class="flex flex-col items-end gap-2">
                                        @if($badge)
                                            <span class="px-3 py-1 rounded-pill text-[10px] font-bold tracking-widest uppercase
                                                         bg-black/20 text-gold-500"
                                                  style="border:1px solid rgba(212,167,87,.18);">
                                                {{ $badge }}
                                            </span>
                                        @endif

                                        @if($tipe !== '')
                                            <span class="px-3 py-1 rounded-pill text-[10px] font-bold tracking-widest uppercase
                                                         bg-black/20 text-brand-textSoft"
                                                  style="border:1px solid rgba(212,167,87,.12);">
                                                {{ $tipe }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <p class="font-display font-extrabold leading-none
                                              text-[clamp(34px,3.2vw,52px)]
                                              {{ $isCenter ? 'text-transparent bg-clip-text bg-brand-gold' : '' }}">
                                        RP {{ number_format($harga, 0, ',', '.') }}
                                    </p>
                                </div>

                                <p class="mt-4 text-sm text-brand-textSoft leading-relaxed line-clamp-3">
                                    {{ $paket->deskripsi ?: 'Paket membership sesuai durasi dan kebijakan gym.' }}
                                </p>

                                <div class="mt-7">
                                    <a href="{{ $registerUrl }}"
                                       class="w-full inline-flex items-center justify-center gap-2 px-7 py-4 rounded-pill font-heading font-bold
                                              {{ $isCenter
                                                  ? 'bg-gold-500/85 hover:bg-gold-500/95 text-brand-nav shadow-gold-glow hover:shadow-gold-glow-strong'
                                                  : 'bg-black/18 hover:bg-black/24 text-brand-text' }}
                                              transition"
                                       style="border:1px solid rgba(212,167,87,.18);">
                                        Pilih Paket
                                        <i data-lucide="arrow-right" class="w-5 h-5 {{ $isCenter ? '' : 'text-gold-500' }}"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="h-1 w-full" style="background:rgba(212,167,87,{{ $isCenter ? '.75' : '.35' }});"></div>
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <div class="text-center py-12" data-reveal="up" data-delay="60">
                <p class="text-brand-textSoft">Paket membership belum tersedia.</p>
                <a href="{{ $registerUrl }}"
                   class="inline-flex items-center justify-center gap-2 px-7 py-4 rounded-pill font-heading font-bold
                          bg-gold-500/85 hover:bg-gold-500/95 text-brand-nav shadow-gold-glow transition mt-6"
                   style="border:1px solid rgba(212,167,87,.18);">
                    Daftar Sekarang
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>
        @endif
    </div>
</section>


        {{-- ========================================================= --}}
        {{-- COACH --}}
        {{-- ========================================================= --}}
        @if($coaches->count() > 0)
            <section class="py-20 md:py-24 relative overflow-hidden" id="coaches">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute inset-0 bg-brand-radial-spot opacity-18"></div>
                    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[900px] h-[900px] bg-gold-500/7 rounded-full blur-[170px]"></div>
                </div>

                <div class="container mx-auto px-6 relative">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
                        <div class="max-w-2xl" data-reveal="left" data-delay="0">
                            <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Coach</span>
                            <h2 class="mt-2 font-display leading-[0.95]
                                       text-[clamp(34px,4.5vw,60px)]">
                                PILIH COACH<br>
                                <span class="text-transparent bg-clip-text bg-brand-gold">YANG PAS BUAT ANDA</span>
                            </h2>
                            <p class="text-brand-textSoft mt-4">
                                Temukan coach yang sesuai gaya latihan Anda, lalu konsultasi langsung untuk jadwal dan program.
                            </p>
                        </div>

                        <div data-reveal="right" data-delay="120">
                            <a href="{{ $allCoachUrl }}"
                               class="inline-flex items-center gap-2 text-gold-500 font-bold hover:text-gold-400 transition">
                                Lihat Coach Lainnya
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                    <div class="relative" data-reveal="up" data-delay="160" data-carousel data-nudge="1">
    {{-- edge fade kiri/kanan (indikasi ada konten di samping) --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 w-10 sm:w-14
                bg-gradient-to-r from-black/70 to-transparent z-10"></div>
    <div class="pointer-events-none absolute inset-y-0 right-0 w-10 sm:w-14
                bg-gradient-to-l from-black/70 to-transparent z-10"></div>

    {{-- track --}}
    <div id="coachCarousel"
     data-carousel-track
     class="autoscroll-track flex gap-5 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-4
            custom-scrollbar touch-auto overscroll-x-contain soft-scroll
            select-none md:cursor-grab md:active:cursor-grabbing">

        @foreach($coaches as $cidx => $coach)
            @php
                $foto      = $coachImg($coach->foto ?? '');
                $detailUrl = $coachDetailUrl($coach);
                $waUrl     = $coachWaUrl($coach);
            @endphp

            <article class="snap-start flex-none w-[240px] sm:w-[270px] md:w-[290px]">
                <div class="surface-card rounded-3xl overflow-hidden transition">
                    <a href="{{ $detailUrl }}" class="block">
                        <div class="relative aspect-[3/4] overflow-hidden">
                            <img src="{{ $foto }}"
                                 alt="{{ $coach->nama }}"
                                 class="w-full h-full object-cover transition duration-700 hover:scale-[1.04]"
                                 onerror="this.src='https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?q=80&w=2070&auto=format&fit=crop'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/15 to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-lg sm:text-xl font-bold font-heading text-brand-text truncate">
                                    {{ $coach->nama }}
                                </h3>
                            </div>
                        </div>
                    </a>

                    <div class="p-4 sm:p-5">
                        @if($waUrl)
                            <button type="button"
                                    class="js-wa w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-pill font-heading font-bold
                                           text-white transition"
                                    style="background:#25D366; box-shadow:0 18px 45px rgba(37,211,102,.18);"
                                    data-wa="{{ $waUrl }}">
                                <svg width="18" height="18" viewBox="0 0 32 32" aria-hidden="true">
                                    <path fill="currentColor" d="M19.11 17.53c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.14-1.13-.42-2.16-1.33-.8-.71-1.34-1.58-1.5-1.85-.16-.27-.02-.42.12-.55.12-.12.27-.31.41-.47.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.47-.07-.14-.61-1.48-.83-2.02-.22-.53-.44-.46-.61-.46h-.52c-.18 0-.47.07-.72.34-.25.27-.94.92-.94 2.24s.96 2.6 1.09 2.78c.14.18 1.9 2.9 4.61 4.07.64.28 1.14.45 1.53.57.64.2 1.23.17 1.7.1.52-.08 1.6-.65 1.82-1.28.22-.63.22-1.16.15-1.28-.07-.12-.25-.2-.52-.34z"/>
                                    <path fill="currentColor" d="M16.04 3.2c-6.99 0-12.68 5.69-12.68 12.68 0 2.23.58 4.4 1.69 6.31L3.2 28.8l6.79-1.78c1.86 1.02 3.96 1.55 6.05 1.55 6.99 0 12.68-5.69 12.68-12.68S23.03 3.2 16.04 3.2zm0 23.02c-1.92 0-3.8-.52-5.44-1.5l-.39-.23-4.03 1.06 1.08-3.93-.25-.4c-1.07-1.72-1.64-3.71-1.64-5.75 0-6.01 4.89-10.9 10.9-10.9 6.01 0 10.9 4.89 10.9 10.9 0 6.01-4.89 10.9-10.9 10.9z"/>
                                </svg>
                                Hubungi Coach
                            </button>
                        @else
                            <button type="button"
                                    class="w-full py-3 rounded-pill text-center font-heading font-bold
                                           bg-black/20 text-brand-textSoft cursor-not-allowed"
                                    style="border:1px solid rgba(212,167,87,.12);">
                                Kontak belum tersedia
                            </button>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    {{-- tombol desktop --}}
    <button type="button"
            class="hidden md:flex absolute left-2 top-1/2 -translate-y-1/2 z-20
                   w-11 h-11 rounded-full bg-black/40 border border-gold-500/20
                   items-center justify-center hover:bg-black/55 transition"
            data-carousel-prev
            aria-label="Sebelumnya">
        <i data-lucide="chevron-left" class="w-5 h-5 text-gold-500"></i>
    </button>

    <button type="button"
            class="hidden md:flex absolute right-2 top-1/2 -translate-y-1/2 z-20
                   w-11 h-11 rounded-full bg-black/40 border border-gold-500/20
                   items-center justify-center hover:bg-black/55 transition"
            data-carousel-next
            aria-label="Berikutnya">
        <i data-lucide="chevron-right" class="w-5 h-5 text-gold-500"></i>
    </button>
</div>

                </div>
            </section>
        @endif

        {{-- ========================================================= --}}
        {{-- PRODUK --}}
        {{-- ========================================================= --}}
        @if($products->count() > 0)
            <section class="py-20 md:py-24 relative overflow-hidden" id="products">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute inset-0 opacity-[0.05]"
                         style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 44px 44px;"></div>
                    <div class="absolute -bottom-24 left-1/3 w-[820px] h-[820px] bg-accent-500/7 rounded-full blur-[180px]"></div>
                </div>

                <div class="container mx-auto px-6 relative">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
                        <div class="max-w-2xl" data-reveal="left" data-delay="0">
                            <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Produk</span>
                            <h2 class="mt-2 font-display leading-[0.95]
                                       text-[clamp(34px,4.5vw,60px)]">
                                PILIH PRODUK<br>
                                <span class="text-transparent bg-clip-text bg-brand-gold">PENDUKUNG LATIHAN</span>
                            </h2>
                            <p class="text-brand-textSoft mt-4">
                                Perlengkapan dan kebutuhan latihan yang bisa Anda pilih sesuai kebutuhan.
                            </p>
                        </div>

                        <div data-reveal="right" data-delay="120">
                            <a href="{{ $allProductUrl }}"
                               class="inline-flex items-center gap-2 text-gold-500 font-bold hover:text-gold-400 transition">
                                Lihat Produk Lainnya
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                    <div class="relative" data-reveal="up" data-delay="160" data-carousel data-nudge="1">
    {{-- edge fade kiri/kanan --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 w-10 sm:w-14
                bg-gradient-to-r from-black/70 to-transparent z-10"></div>
    <div class="pointer-events-none absolute inset-y-0 right-0 w-10 sm:w-14
                bg-gradient-to-l from-black/70 to-transparent z-10"></div>

    {{-- track --}}
    <div id="productTrack"
     data-carousel-track
     class="autoscroll-track flex gap-5 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-4
            custom-scrollbar touch-auto overscroll-x-contain soft-scroll
            select-none md:cursor-grab md:active:cursor-grabbing">

        @foreach($products as $pidx => $p)
            @php
                $img    = $productImg($p->foto ?? ($p->gambar ?? ($p->image ?? '')));
                $name   = $p->nama ?? ($p->name ?? 'Produk');
                $price  = $p->harga ?? ($p->price ?? null);
                $detail = $productDetailUrl($p);
            @endphp

            <article class="snap-start flex-none w-[240px] sm:w-[280px] md:w-[320px]">
                <div class="surface-card rounded-3xl overflow-hidden transition">
                    <a href="{{ $detail }}" class="block">
                        <div class="relative w-full pt-[100%] overflow-hidden bg-black/20">
                            <img src="{{ $img }}"
                                 alt="{{ $name }}"
                                 class="absolute inset-0 w-full h-full object-cover object-center"
                                 loading="lazy"
                                 onerror="this.src='https://images.unsplash.com/photo-1599058918144-1ffabb6ab9a0?q=80&w=2070&auto=format&fit=crop'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                        </div>

                        <div class="p-5">
                            <h3 class="text-lg font-bold font-heading truncate">{{ $name }}</h3>

                            @if(!is_null($price))
                                <p class="mt-2 font-display font-extrabold text-2xl text-transparent bg-clip-text bg-brand-gold">
                                    Rp {{ number_format((float)$price, 0, ',', '.') }}
                                </p>
                            @else
                                <p class="mt-2 text-sm text-brand-textSoft">Harga belum tersedia</p>
                            @endif

                            <div class="mt-5 flex items-center justify-end">
                                <span class="inline-flex items-center gap-2 text-gold-500 font-bold">
                                    Lihat Detail
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    {{-- tombol desktop --}}
    <button type="button"
            class="hidden md:flex absolute left-2 top-1/2 -translate-y-1/2 z-20
                   w-11 h-11 rounded-full bg-black/40 border border-gold-500/20
                   items-center justify-center hover:bg-black/55 transition"
            data-carousel-prev
            aria-label="Sebelumnya">
        <i data-lucide="chevron-left" class="w-5 h-5 text-gold-500"></i>
    </button>

    <button type="button"
            class="hidden md:flex absolute right-2 top-1/2 -translate-y-1/2 z-20
                   w-11 h-11 rounded-full bg-black/40 border border-gold-500/20
                   items-center justify-center hover:bg-black/55 transition"
            data-carousel-next
            aria-label="Berikutnya">
        <i data-lucide="chevron-right" class="w-5 h-5 text-gold-500"></i>
    </button>
</div>

                </div>
            </section>
        @endif

        {{-- ========================================================= --}}
        {{-- MOBILE APP + FAQ --}}
        {{-- ========================================================= --}}
        <section class="relative overflow-hidden min-svh flex items-center" id="app">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[980px] h-[980px] bg-gold-500/7 rounded-full blur-[190px]"></div>
                <div class="absolute inset-0 opacity-[0.05]"
                     style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 46px 46px;"></div>
            </div>

            <div class="container mx-auto px-6 relative py-16 md:py-20">
                <div class="grid lg:grid-cols-2 gap-10 lg:gap-12 items-center">
                    {{-- MOCKUP ONLY --}}
                    <div class="relative flex justify-center" data-reveal="left" data-delay="0">
                        <div class="absolute -z-10 w-[520px] h-[520px] bg-gold-500/10 rounded-full blur-[120px]"></div>
                        <img
                            src="{{ $mockupUrl }}"
                            alt="Aplikasi BETA GYM"
                            class="w-[min(420px,78vw)] max-w-[420px] drop-shadow-[0_28px_70px_rgba(0,0,0,0.65)]"
                            onerror="this.style.display='none'"
                        >
                    </div>

                    {{-- CONTENT --}}
                    <div class="space-y-8" data-reveal="right" data-delay="120">
                        <div>
                            <h2 class="font-display font-extrabold uppercase leading-[0.95]
                                       text-[clamp(34px,4.6vw,64px)]">
                                SEMUA LEBIH PRAKTIS<br>
                                <span class="text-transparent bg-clip-text bg-brand-gold">DALAM SATU AKSES</span>
                            </h2>
                            <p class="text-brand-textSoft text-lg mt-4">
                                Membership, coach, dan kebutuhan latihan disusun rapi agar lebih mudah dipakai.
                            </p>
                        </div>

                        {{-- Accordion: default tertutup --}}
                        <div x-data="{ active: 0 }" class="space-y-4">
                            @php
                                $faq = [
                                    ['t'=>'Info membership mudah dipahami', 'd'=>'Pilih paket berdasarkan durasi dan kebutuhan, dengan informasi yang jelas sejak awal.'],
                                    ['t'=>'Akses coach lebih cepat', 'd'=>'Lihat profil coach dan konsultasi langsung untuk jadwal dan program latihan.'],
                                    ['t'=>'Produk pendukung latihan', 'd'=>'Cari kebutuhan latihan dengan tampilan yang rapi dan pilihan yang mudah dipahami.'],
                                    ['t'=>'Cara daftar membership', 'd'=>'Daftar, pilih paket, lalu aktivasi diproses sesuai ketentuan gym.'],
                                    ['t'=>'Metode pembayaran', 'd'=>'Pembayaran mengikuti metode yang tersedia di gym, dengan instruksi yang ringkas dan jelas.'],
                                ];
                            @endphp

                            @foreach($faq as $i => $item)
                                @php $n = $i + 1; @endphp
                                <div class="surface-card rounded-2xl px-5 sm:px-6 py-4" data-reveal="up" data-delay="{{ 120 + $i*70 }}">
                                    <button type="button"
                                            class="w-full flex items-center justify-between gap-4"
                                            @click="active = (active === {{ $n }} ? 0 : {{ $n }})">
                                        <span class="text-base sm:text-lg font-bold font-heading uppercase tracking-wide text-brand-text">
                                            {{ $item['t'] }}
                                        </span>
                                        <i data-lucide="chevron-down"
                                           class="w-5 h-5 text-gold-500 transition-transform duration-300"
                                           :class="active === {{ $n }} ? 'rotate-180' : ''"></i>
                                    </button>

                                    <div x-show="active === {{ $n }}" x-collapse>
                                        <p class="pt-3 text-brand-textSoft text-sm leading-relaxed">
                                            {{ $item['d'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- CTA --}}
        {{-- ========================================================= --}}
        <section class="py-20 md:py-24 relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-0 bg-brand-radial-gold opacity-12"></div>
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[900px] h-[900px] bg-gold-500/7 rounded-full blur-[190px]"></div>
            </div>

            <div class="container mx-auto px-6 relative" data-reveal="up" data-delay="0">
                <div class="surface-card rounded-3xl p-8 md:p-10 overflow-hidden relative">
                    <div class="absolute -top-16 -right-16 w-64 h-64 bg-gold-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-16 -left-16 w-64 h-64 bg-accent-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="grid lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-8">
                            <h3 class="font-display font-extrabold leading-[0.95]
                                       text-[clamp(28px,3.4vw,44px)]">
                                SIAP MULAI LATIHAN<br>
                                <span class="text-transparent bg-clip-text bg-brand-gold">DENGAN ARAH YANG JELAS?</span>
                            </h3>
                            <p class="text-brand-textSoft mt-4 max-w-2xl leading-relaxed">
                                Mulai dari paket yang sesuai, lalu tingkatkan konsistensi latihan. Jika perlu, Anda bisa konsultasi dengan coach untuk program yang lebih terarah.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3 lg:justify-end">
                            <a href="{{ $registerUrl }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-7 py-4 rounded-pill font-heading font-bold
                                      bg-gold-500/85 hover:bg-gold-500/95 text-brand-nav shadow-gold-glow hover:shadow-gold-glow-strong transition"
                               style="border:1px solid rgba(212,167,87,.18);">
                                Daftar Member
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </a>

                            <a href="#membership"
                               class="w-full inline-flex items-center justify-center gap-2 px-7 py-4 rounded-pill font-heading font-bold
                                      bg-black/18 hover:bg-black/24 text-brand-text transition"
                               style="border:1px solid rgba(212,167,87,.16);">
                                Lihat Paket
                                <i data-lucide="layers" class="w-5 h-5 text-gold-500"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    {{-- ========================================================= --}}
    {{-- SCRIPTS: WA + AutoScroll UX + Scroll-Reveal --}}
    {{-- ========================================================= --}}
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion =
        window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // =========================
    // WA button (tetap aman)
    // =========================
    document.querySelectorAll('.js-wa').forEach(btn => {
        btn.addEventListener('click', () => {
            const url = btn.getAttribute('data-wa');
            if (!url) return;
            window.open(url, '_blank', 'noopener');
        });
    });

    // =========================
    // Carousel Premium Usable
    // - Mobile: native swipe + snap
    // - Desktop: Prev/Next + drag-to-scroll (grab)
    // - Wheel: scroll vertikal -> geser horizontal
    // - Nudge hint 1x saat terlihat (opsional)
    // =========================
    (function initCarousels() {
        const keyPrefix = 'bgym_carousel_nudged_';

        const getStep = (track) => {
            const first = track.querySelector(':scope > *');
            if (!first) return 320;

            const rect = first.getBoundingClientRect();
            const styles = getComputedStyle(track);
            const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;

            return rect.width + gap;
        };

        const scrollByStep = (track, dir) => {
            const step = getStep(track);
            track.scrollBy({
                left: dir * step,
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        };

        document.querySelectorAll('[data-carousel]').forEach((root, idx) => {
            const track = root.querySelector('[data-carousel-track]');
            if (!track) return;

            const prev = root.querySelector('[data-carousel-prev]');
            const next = root.querySelector('[data-carousel-next]');

            // tombol desktop
            if (prev) prev.addEventListener('click', () => scrollByStep(track, -1));
            if (next) next.addEventListener('click', () => scrollByStep(track,  1));

            // biar feel premium di desktop
            track.style.scrollBehavior = reduceMotion ? 'auto' : 'smooth';

            // hindari drag image (sering bikin “ketarik gambar”)
            track.querySelectorAll('img').forEach(img => {
                img.setAttribute('draggable', 'false');
                img.style.userSelect = 'none';
                img.style.webkitUserDrag = 'none';
            });

            // Wheel: kalau user scroll vertical di track, ubah jadi horizontal
            track.addEventListener('wheel', (e) => {
                // jika user memang scroll horizontal (trackpad), biarkan
                if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;

                e.preventDefault();
                track.scrollBy({ left: e.deltaY, behavior: 'auto' });
            }, { passive: false });

            // Drag-to-scroll: aktifkan untuk MOUSE saja (desktop)
            let isDown = false;
            let startX = 0;
            let startLeft = 0;

            // cursor hint hanya di device yang ada hover
            const canHover = window.matchMedia && window.matchMedia('(hover: hover)').matches;
            if (canHover) track.style.cursor = 'grab';

            track.addEventListener('pointerdown', (e) => {
                // hanya mouse kiri
                if (e.pointerType !== 'mouse' || e.button !== 0) return;

                isDown = true;
                startX = e.clientX;
                startLeft = track.scrollLeft;

                track.classList.add('is-dragging');
                if (canHover) track.style.cursor = 'grabbing';

                try { track.setPointerCapture(e.pointerId); } catch (_) {}
            });

            track.addEventListener('pointermove', (e) => {
                if (!isDown) return;
                const dx = e.clientX - startX;
                track.scrollLeft = startLeft - dx;
            });

            const endDrag = () => {
                if (!isDown) return;
                isDown = false;
                track.classList.remove('is-dragging');
                if (canHover) track.style.cursor = 'grab';
            };

            track.addEventListener('pointerup', endDrag);
            track.addEventListener('pointercancel', endDrag);
            track.addEventListener('mouseleave', endDrag);

            // Nudge hint 1x saat pertama kali terlihat (opsional)
            // aktif jika wrapper punya data-nudge="1"
            if (root.dataset.nudge === '1' && !reduceMotion) {
                const nudgeKey = keyPrefix + (root.id || String(idx));
                if (!sessionStorage.getItem(nudgeKey)) {
                    const io = new IntersectionObserver((entries) => {
                        entries.forEach(en => {
                            if (!en.isIntersecting) return;

                            sessionStorage.setItem(nudgeKey, '1');
                            io.disconnect();

                            const max = track.scrollWidth - track.clientWidth;
                            if (max <= 0) return;

                            const amt = Math.min(getStep(track) * 0.55, 160);

                            setTimeout(() => {
                                track.scrollBy({ left: amt, behavior: 'smooth' });
                                setTimeout(() => {
                                    track.scrollBy({ left: -amt, behavior: 'smooth' });
                                }, 520);
                            }, 260);
                        });
                    }, { threshold: 0.35 });

                    io.observe(root);
                }
            }
        });
    })();

    // =========================
    // Scroll-Reveal (tetap)
    // =========================
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (reduceMotion) {
        items.forEach(el => el.classList.add('is-visible'));
        return;
    }

    const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (!e.isIntersecting) return;
            const el = e.target;

            const delay = parseInt(el.getAttribute('data-delay') || '0', 10);
            el.style.transitionDelay = `${delay}ms`;

            el.classList.add('is-visible');
            obs.unobserve(el);
        });
    }, { threshold: 0.14 });

    items.forEach(el => obs.observe(el));
});
</script>

</x-layouts.guest>

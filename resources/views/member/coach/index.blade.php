{{-- resources/views/member/coach/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle    = $pageTitle    ?? 'Daftar Coach';
    $pageSubtitle = $pageSubtitle ?? 'Kenali coach BETA GYM dan pilih pendamping latihan yang tepat.';
    $search       = $search       ?? request('search', '');
@endphp

<x-layouts.member
    :title="$pageTitle"
    :page-title="$pageTitle"
    :page-subtitle="$pageSubtitle"
>
{{-- ========================================================= --}}
{{-- 1. HERO BANNER (MEDIUM • PHOTO BG • 1 CARD) --}}
{{-- ========================================================= --}}
@php
    // Pastikan file ada di: public/images/hero-coach.jpg
    $heroBg = asset('images/hero-coach.jpg');
@endphp

<div
    class="relative w-full rounded-3xl overflow-hidden mb-8 border border-brand-borderSoft/40 shadow-card-strong bg-brand-nav"
    style="
        background-image:
          linear-gradient(90deg, rgba(0,0,0,.82) 0%, rgba(0,0,0,.55) 48%, rgba(0,0,0,.25) 100%),
          url('{{ $heroBg }}');
        background-size: cover;
        background-position: right center; /* penting: biar sisi kiri lebih aman untuk teks */
     "
>
    {{-- Depth overlay biar mirip dashboard --}}
    <div class="absolute inset-0 bg-gradient-to-t from-brand-black/35 via-transparent to-white/5"></div>

    <div class="relative z-10 px-8 py-9 md:px-10 md:py-10">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-10">

            {{-- LEFT: Text --}}
            <div class="max-w-2xl space-y-4">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full
                            bg-brand-black/35 backdrop-blur-md border border-gold-500/20">
                    <span class="w-2 h-2 rounded-full bg-gold-400"></span>
                    <span class="text-[11px] font-bold tracking-widest uppercase text-gold-300">
                        Official Coach Member
                    </span>
                </div>

                <h2 class="font-display font-extrabold leading-tight text-3xl md:text-4xl lg:text-[44px]">
                    <span class="text-brand-white">Find Your Best</span>
                    <span class="text-gold-400"> Coach.</span>
                </h2>

                <p class="text-brand-silver/90 text-sm md:text-base leading-relaxed max-w-xl">
                    Coach profesional BETA GYM siap mendampingi progres latihanmu dengan program yang terarah dan konsisten.
                </p>
            </div>

            {{-- RIGHT: Glass Panel (1 CARD) --}}
            <div class="w-full lg:w-auto lg:min-w-[360px]">
                <div class="rounded-3xl bg-white/8 backdrop-blur-xl border border-white/15 shadow-xl overflow-hidden min-h-[100px]">

                    <div class="p-5 flex items-start gap-4">
                        <div class="relative w-12 h-12 rounded-2xl overflow-hidden border border-gold-500/35 bg-brand-black/25 flex items-center justify-center">
    <div class="absolute inset-0 bg-gradient-to-br from-gold-500/20 via-transparent to-white/5"></div>

    <svg class="relative w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 12c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4Z"
              stroke="#F3D08A" stroke-opacity="0.9" stroke-width="1.6"/>
        <path d="M4.5 20c1.7-3.3 4.3-5 7.5-5s5.8 1.7 7.5 5"
              stroke="#D4A757" stroke-opacity="0.75" stroke-width="1.6" stroke-linecap="round"/>
        <path d="M6.5 17.2c1.2-1 2.6-1.7 4.1-2.1"
              stroke="#F3D08A" stroke-opacity="0.45" stroke-width="1.4" stroke-linecap="round"/>
    </svg>

    <div class="absolute -inset-6 bg-gold-500/10 blur-2xl"></div>
</div>

                        <div class="min-w-0">
                            <p class="text-base font-bold text-brand-white leading-snug">
                                Pilih coach yang cocok
                            </p>
                            <p class="text-sm text-brand-silver/85 mt-1">
                                Cari berdasarkan nama, lokasi, atau keahlian.
                            </p>
                        </div>
                    </div>

                    <div class="h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

                    <div class="p-5 pt-4">
                        <p class="text-sm text-brand-silver/80 leading-relaxed">
                            Konsistensi lebih mudah saat Anda punya pendamping latihan yang tepat.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- bottom accent --}}
    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold-500/30 to-transparent"></div>
</div>


{{-- ========================================================= --}}
{{-- 2. SEARCH BAR (DISERAGAMKAN DENGAN PRODUK) --}}
{{-- ========================================================= --}}
<div class="sticky top-20 z-30 bg-brand-bg/95 backdrop-blur-sm py-4 border-b border-brand-borderSoft/40 mb-8">
    <form method="GET" action="{{ route('member.coach.index') }}" class="flex gap-3 items-center">
        <div class="relative w-full max-w-xl flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-5 h-5 text-brand-textSoft"></i>
            </div>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari nama coach, lokasi, atau keahlian…"
                class="block w-full pl-10 pr-4 py-3 bg-brand-card border border-brand-borderSoft rounded-2xl
                       text-sm text-brand-text placeholder-brand-textSoft/60
                       focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-all"
            >
        </div>
    </form>
</div>

{{-- ========================================================= --}}
{{-- 3. CONTENT --}}
{{-- ========================================================= --}}
@if($coaches->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 px-4">
        <div class="w-24 h-24 rounded-3xl bg-brand-card border border-brand-borderSoft flex items-center justify-center mb-6 shadow-xl">
            <i data-lucide="user-x" class="w-12 h-12 text-text-muted"></i>
        </div>
        <h3 class="text-xl font-bold text-text-main mb-2">Tidak Ada Coach Ditemukan</h3>
        <p class="text-text-muted text-center max-w-md mb-6">
            @if($search)
                Maaf, tidak ada coach yang cocok dengan pencarian "{{ $search }}".
            @else
                Belum ada coach yang tersedia saat ini.
            @endif
        </p>
    </div>
@else

<div class="mb-6 flex items-center gap-2">
    <h3 class="text-lg font-semibold text-text-main">
        Coach Tersedia
        <span class="text-gold-400">({{ $coaches->total() }})</span>
    </h3>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
@foreach ($coaches as $coach)
@php
    $imageUrl = $coach->foto
        ? Storage::url($coach->foto)
        : 'https://placehold.co/400x500/1F2937/FACC15?text=COACH';

    $waNumber = $coach->no_hp
        ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $coach->no_hp))
        : null;
@endphp

<div class="group bg-brand-card border border-brand-borderSoft rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col">

    {{-- IMAGE --}}
    <div class="relative h-72 w-full overflow-hidden bg-brand-surface-50">
        <img
            src="{{ $imageUrl }}"
            alt="{{ $coach->nama }}"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        >
        {{-- Overlay dikurangi --}}
        <div class="absolute inset-0 bg-gradient-to-t from-brand-black/40 via-transparent to-transparent"></div>
    </div>

    {{-- CONTENT --}}
    <div class="p-5 flex flex-col flex-grow">
        {{-- NAMA COACH (SEKARANG SELALU JELAS) --}}
        <h3 class="text-xl font-bold text-gold-400 mb-2 transition-colors duration-200 group-hover:text-gold-300">
    {{ $coach->nama ?? 'Nama Coach' }}
</h3>


        @if (!empty($coach->alamat))
            <div class="flex items-start gap-2 mb-3">
                <i data-lucide="map-pin" class="w-4 h-4 text-gold-400 mt-0.5"></i>
                <p class="text-xs text-text-muted line-clamp-1">
                    {{ $coach->alamat }}
                </p>
            </div>
        @endif

        <p class="text-sm text-text-muted mb-4 line-clamp-3">
            {{ $coach->deskripsi
                ? Str::limit($coach->deskripsi, 120)
                : 'Personal trainer profesional dengan pendekatan latihan terstruktur.' }}
        </p>

        <div class="mt-auto">
            @if ($waNumber)
                <a
                    href="https://wa.me/{{ $waNumber }}"
                    target="_blank"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-all"
                >
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    Hubungi via WhatsApp
                </a>
            @else
                <div class="text-center text-xs text-text-muted py-3">
                    Kontak tidak tersedia
                </div>
            @endif
        </div>
    </div>
</div>
@endforeach
</div>

@if($coaches->hasPages())
    <div class="mt-8">
        {{ $coaches->links() }}
    </div>
@endif

@endif

{{-- CTA SECTION (WITH PHOTO BACKGROUND) --}}
@php
    // Pastikan file ada di: public/images/cta-coach1.jpg
    $ctaBg = asset('images/cta-coach1.jpg');
@endphp

<div
    class="mt-12 relative overflow-hidden rounded-2xl border border-brand-borderSoft shadow-xl"
    style="
        background-image:
          linear-gradient(90deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.35) 55%, rgba(0,0,0,.20) 100%),
          url('{{ $ctaBg }}');
        background-size: cover;
        background-position: center;
    "
>
    {{-- overlay halus biar lebih “premium” & teks aman --}}
    <div class="absolute inset-0 bg-gradient-to-br from-brand-black/40 via-transparent to-brand-black/25"></div>

    <div class="relative z-10 p-8 md:p-10">
        <div class="text-center max-w-2xl mx-auto">
            {{-- SPACER: menjaga tinggi seperti saat ada icon box (w-16 h-16 + mb-4) --}}
            <div class="w-16 h-16 mx-auto mb-4"></div>

            <h3 class="text-2xl font-bold text-brand-white mb-3">
                Butuh Bantuan Memilih Coach?
            </h3>

            <p class="text-brand-silver/90 mb-6">
                Tim kami siap membantu Anda menemukan personal trainer yang tepat.
            </p>

            <a
                href="https://wa.me/6281234567890"
                target="_blank"
                class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700 text-brand-black font-bold rounded-xl transition-all"
            >
                <i data-lucide="headphones" class="w-5 h-5"></i>
                Hubungi Customer Service
            </a>
        </div>
    </div>
</div>




</x-layouts.member>

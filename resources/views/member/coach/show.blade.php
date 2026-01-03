{{-- resources/views/member/coach/show.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $coach = $coach ?? null;

    // =========================
    // Helper: Image Resolver
    // =========================
    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);

        if ($path === '') {
            return 'https://placehold.co/1200x1600/111827/FACC15?text=' . urlencode($fallbackText ?: 'COACH') . '&font=raleway';
        }

        if (Str::startsWith($path, ['http://', 'https://'])) return $path;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/'))  $path = Str::after($path, 'public/');

        return Storage::url($path);
    };

    $imageUrl = $imgUrl($coach?->foto, $coach?->nama ?? 'COACH');

    // =========================
    // Helper: WhatsApp normalize
    // (untuk CTA saja, nomor tidak ditampilkan)
    // =========================
    $waNumber = null;
    if (!empty($coach?->no_hp)) {
        $digits = preg_replace('/\D/', '', (string) $coach->no_hp);
        if ($digits) {
            if (Str::startsWith($digits, '0')) $digits = '62' . substr($digits, 1);
            if (Str::startsWith($digits, '8')) $digits = '62' . $digits;
            $waNumber = $digits;
        }
    }

    // =========================
    // Related Coaches (opsional)
    // Idealnya dari controller, tapi aman di view juga
    // =========================
    $relatedCoaches = collect();
    if ($coach?->id) {
        $relatedCoaches = \App\Models\Coach::query()
            ->where('id', '!=', $coach->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    $pageTitle = ($coach?->nama ?: 'Detail Coach');
@endphp

<x-layouts.member :title="$pageTitle" :page-title="$pageTitle" page-subtitle="Detail coach dan konsultasi via WhatsApp.">

    @if(!$coach)
        <div class="bg-brand-card border border-brand-borderSoft rounded-3xl p-8 text-center shadow-card-soft">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-brand-shell border border-brand-borderSoft flex items-center justify-center mb-4">
                <i data-lucide="user-x" class="w-10 h-10 text-brand-textSoft/60"></i>
            </div>
            <h2 class="text-xl font-bold text-brand-text mb-2">Coach tidak ditemukan</h2>
            <p class="text-sm text-brand-textSoft mb-6">Data coach yang Anda cari tidak tersedia atau sudah dihapus.</p>
            <a href="{{ route('member.coach.index') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-brand-shell border border-brand-borderSoft
                      text-brand-text font-semibold hover:bg-brand-surface-100 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke daftar coach
            </a>
        </div>
    @else

        

        {{-- ========================================================= --}}
        {{-- MAIN --}}
        {{-- ========================================================= --}}
        <section class="overflow-x-hidden">
            <div class="grid lg:grid-cols-12 gap-8 lg:gap-14 items-start">

                {{-- LEFT: FOTO (nama saja di atas foto) --}}
                <div class="lg:col-span-4 lg:sticky lg:top-24">
                    <div class="relative rounded-[2rem] overflow-hidden border border-brand-borderSoft shadow-card-strong bg-brand-card group">
                        <div class="aspect-[3/4] w-full relative overflow-hidden bg-brand-surface-50">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $coach->nama }}"
                                class="w-full h-full object-cover object-top transition-transform duration-700 ease-out group-hover:scale-105"
                                onerror="this.onerror=null; this.src='https://placehold.co/1200x1600/111827/FACC15?text={{ urlencode($coach->nama ?? 'COACH') }}&font=raleway';"
                            >

                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent opacity-90"></div>

                            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-7">
                                <h2 class="text-3xl md:text-4xl font-display font-bold text-white drop-shadow-md">
                                    {{ $coach->nama }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: INFO --}}
                <div class="lg:col-span-8 flex flex-col justify-center min-w-0">

                    {{-- Header copy --}}
                    <div class="mb-8 pb-8 border-b border-brand-borderSoft/60">
                        <div class="flex flex-wrap items-center gap-2.5 mb-5 w-full min-w-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl
                                         bg-gold-500/10 text-gold-600 dark:text-gold-400
                                         border border-gold-500/25 text-[11px] font-bold uppercase tracking-widest shrink-0">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Terpercaya
                            </span>

                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl
                                         bg-brand-shell text-brand-textSoft border border-brand-borderSoft
                                         text-[11px] font-bold uppercase tracking-widest
                                         w-full sm:w-auto max-w-full min-w-0">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 text-gold-500/80"></i>
                                <span class="truncate min-w-0">{{ $coach->alamat ?: 'BETA Gym' }}</span>
                            </span>
                        </div>

                        <h1 class="text-3xl md:text-5xl font-display font-bold text-brand-text leading-[1.05] mb-5">
                            BANGUN POTENSI <br>
                            <span class="text-transparent bg-clip-text bg-brand-gold">TERBAIK ANDA.</span>
                        </h1>

                        <p class="text-sm md:text-base text-brand-textSoft leading-relaxed max-w-2xl border-l-4 border-gold-500 pl-6">
                            Program latihan dan pendampingan disusun berdasarkan tujuan serta kondisi awal Anda, agar progres lebih terarah dan realistis.
                        </p>
                    </div>

                    {{-- Tentang Coach --}}
                    <div class="mb-8">
                        <h3 class="text-xs font-bold text-brand-textSoft uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                            <span class="w-8 h-[1px] bg-gold-500"></span> Tentang Coach
                        </h3>

                        <div class="prose max-w-none prose-sm sm:prose-base
                                    prose-headings:text-brand-text
                                    prose-p:text-brand-textSoft
                                    dark:prose-invert dark:prose-p:text-brand-textSoft">
                            <p>
                                {{ $coach->deskripsi ?: 'Coach ini siap membantu Anda menyusun latihan yang lebih terarah sesuai kebutuhan, baik untuk pemula maupun yang sudah rutin berlatih.' }}
                            </p>
                        </div>
                    </div>

                    {{-- WhatsApp CTA (nomor tidak ditampilkan) --}}
                    <div class="p-7 sm:p-8 rounded-[2rem] bg-brand-card border border-brand-borderSoft relative overflow-hidden shadow-card-soft
                                hover:border-gold-500/40 transition-all duration-300">
                        <div class="absolute -top-10 -right-10 opacity-[0.08]">
                            <svg viewBox="0 0 24 24" class="w-44 h-44 fill-current text-brand-text" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </div>

                        <div class="relative z-10">
                            <h3 class="text-xl sm:text-2xl font-display font-bold text-brand-text mb-2">Mulai Konsultasi</h3>
                            <p class="text-brand-textSoft mb-7 max-w-md text-sm">
                                Tanyakan jadwal, sistem latihan, serta estimasi biaya. Anda bisa mulai dari konsultasi singkat terlebih dahulu.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4">
                                @if($waNumber)
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                                       class="flex-1 py-4 px-6 rounded-2xl text-white font-bold text-base sm:text-lg
                                              transition-all duration-300 flex items-center justify-center gap-3
                                              hover:-translate-y-[1px]"
                                       style="background: linear-gradient(90deg, rgba(37,211,102,.95), rgba(18,140,126,.95));
                                              box-shadow: 0 18px 45px rgba(37,211,102,.16);"
                                       aria-label="Chat WhatsApp">
                                        {{-- WhatsApp icon (SVG) --}}
                                        <svg viewBox="0 0 24 24" class="w-6 h-6 fill-current" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                        Chat WhatsApp
                                    </a>
                                @else
                                    <button disabled
                                            class="flex-1 py-4 px-6 rounded-2xl bg-brand-shell border border-brand-borderSoft
                                                   text-brand-textSoft/60 font-bold text-base sm:text-lg cursor-not-allowed
                                                   flex items-center justify-center gap-3">
                                        <i data-lucide="phone-off" class="w-5 h-5"></i>
                                        Kontak Tidak Tersedia
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- RELATED COACHES --}}
            {{-- ========================================================= --}}
            @if($relatedCoaches->count() > 0)
                <div class="border-t border-brand-borderSoft/60 pt-12 mt-14">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8 min-w-0">
                        <div class="min-w-0">
                            <h3 class="text-xl sm:text-2xl font-display font-bold text-brand-text uppercase tracking-tight">
                                Coach Lainnya
                            </h3>
                            <p class="text-brand-textSoft text-sm">Temukan coach dengan fokus latihan berbeda.</p>
                        </div>

                        <a
    href="{{ route('member.coach.index') }}"
    class="group inline-flex items-center gap-2 text-sm font-semibold
           text-brand-textSoft hover:text-gold-500 transition-colors shrink-0"
>
    Lihat semua
    <i data-lucide="arrow-right"
       class="w-4 h-4 text-brand-textSoft group-hover:text-gold-500 transition-colors group-hover:translate-x-0.5"></i>
</a>

                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($relatedCoaches as $related)
                            @php
                                $rSlug = ($related->id ?? 0) . '-' . Str::slug($related->nama ?? 'coach');
                                $rImg  = $imgUrl($related->foto ?? '', $related->nama ?? 'COACH');
                            @endphp

                            <a href="{{ route('member.coach.show', $rSlug) }}"
                               class="group block bg-brand-card rounded-[1.5rem] overflow-hidden
                                      border border-brand-borderSoft shadow-card-soft
                                      hover:border-gold-500/40 hover:shadow-card-strong
                                      transition-all duration-300 hover:-translate-y-1">

                                <div class="aspect-[3/4] bg-brand-surface-50 overflow-hidden relative">
                                    <img src="{{ $rImg }}" alt="{{ $related->nama }}"
                                         class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-105"
                                         onerror="this.onerror=null; this.src='https://placehold.co/800x1000/111827/FACC15?text={{ urlencode($related->nama ?? 'COACH') }}&font=raleway';">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                                </div>

                                <div class="p-4 sm:p-5">
                                    <p class="text-[10px] text-gold-600 dark:text-gold-400 font-bold uppercase mb-1 tracking-wider">
                                        Coach
                                    </p>

                                    <h4 class="text-brand-text font-bold text-sm sm:text-base line-clamp-1 mb-3 group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors">
                                        {{ $related->nama }}
                                    </h4>

                                    <div class="pt-3 border-t border-brand-borderSoft/60 flex justify-between items-center">
                                        <span class="text-[10px] text-brand-textSoft group-hover:text-brand-text transition-colors font-bold">
                                            Lihat Profil
                                        </span>
                                        <div class="w-7 h-7 rounded-full bg-brand-shell border border-brand-borderSoft flex items-center justify-center
                                                    text-brand-text group-hover:bg-gold-500 group-hover:text-brand-nav transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m9 18 6-6-6-6"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </section>
    @endif

</x-layouts.member>

{{-- resources/views/member/coach/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle    = $pageTitle    ?? 'Daftar Coach';
    $pageSubtitle = $pageSubtitle ?? 'Kenali coach BETA GYM dan pilih pendamping latihan yang tepat.';
    $search       = $search       ?? '';
@endphp

<x-layouts.member
    :title="$pageTitle"
    :page-title="$pageTitle"
    :page-subtitle="$pageSubtitle"
>

    {{-- HERO SECTION WITH GRADIENT --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-gunmetal via-brand-card to-brand-gunmetal p-8 mb-8 border border-brand-borderSoft/40 shadow-2xl">
        {{-- Decorative Elements --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-gold-500/5 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gold-400 to-gold-600 flex items-center justify-center shadow-lg">
                    <i data-lucide="users" class="w-6 h-6 text-brand-black"></i>
                </div>
                <div>
                    <h1 class="text-3xl lg:text-4xl font-heading font-bold text-brand-white">
                        {{ $pageTitle }}
                    </h1>
                    <p class="text-sm text-gold-200 mt-1">
                        {{ $pageSubtitle }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- SEARCH BAR WITH MODERN DESIGN --}}
    <div class="mb-8">
        <form method="GET" action="{{ route('member.coach.index') }}" class="relative">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-gold-500/20 to-gold-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
                
                <div class="relative flex flex-col md:flex-row gap-3 p-4 bg-brand-card border border-brand-borderSoft rounded-2xl shadow-lg">
                    {{-- Search Input --}}
                    <div class="flex-1 relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                            <i data-lucide="search" class="w-5 h-5 text-gold-400"></i>
                        </div>
                        <input
                            type="search"
                            id="search"
                            name="search"
                            placeholder="Cari nama coach, lokasi, atau keahlian..."
                            value="{{ $search }}"
                            class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-brand-borderSoft bg-brand-shell/50 text-text-main placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-gold-500/50 focus:border-gold-500 transition-all duration-200"
                        >
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        <button 
                            type="submit"
                            class="px-6 py-3.5 bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700 text-brand-black font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center gap-2 whitespace-nowrap"
                        >
                            <i data-lucide="search" class="w-4 h-4"></i>
                            <span>Cari Coach</span>
                        </button>

                        @if ($search)
                            <a 
                                href="{{ route('member.coach.index') }}"
                                class="px-6 py-3.5 bg-brand-shell border border-brand-borderSoft text-text-main font-semibold rounded-xl hover:bg-brand-surface-50 transition-all duration-200 flex items-center gap-2 whitespace-nowrap"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- RESULTS INFO --}}
    @if($search)
        <div class="mb-6 flex items-center gap-2 text-sm text-text-muted">
            <i data-lucide="info" class="w-4 h-4"></i>
            <span>Menampilkan hasil pencarian untuk: <strong class="text-gold-400">"{{ $search }}"</strong></span>
        </div>
    @endif

    {{-- COACH GRID --}}
    @if($coaches->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 px-4">
            <div class="w-24 h-24 rounded-3xl bg-brand-card border border-brand-borderSoft flex items-center justify-center mb-6 shadow-xl">
                <i data-lucide="user-x" class="w-12 h-12 text-text-muted"></i>
            </div>
            <h3 class="text-xl font-bold text-text-main mb-2">Tidak Ada Coach Ditemukan</h3>
            <p class="text-text-muted text-center max-w-md mb-6">
                @if($search)
                    Maaf, tidak ada coach yang cocok dengan pencarian "{{ $search }}". Coba kata kunci lain.
                @else
                    Belum ada coach yang tersedia saat ini. Silakan cek kembali nanti.
                @endif
            </p>
            @if($search)
                <a href="{{ route('member.coach.index') }}" class="px-6 py-3 bg-gold-500 hover:bg-gold-600 text-brand-black font-bold rounded-xl transition-all duration-200">
                    Lihat Semua Coach
                </a>
            @endif
        </div>
    @else
        {{-- Stats Bar --}}
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-gold-500 animate-pulse"></div>
                <h3 class="text-lg font-semibold text-text-main">
                    Coach Tersedia 
                    <span class="text-gold-400">({{ $coaches->total() }})</span>
                </h3>
            </div>
        </div>

        {{-- COACH CARDS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach ($coaches as $coach)
                @php
                    $imageUrl = $coach->foto
                        ? Storage::url($coach->foto)
                        : 'https://placehold.co/400x500/1F2937/FACC15?text=' . urlencode($coach->nama ?? 'COACH') . '&font=raleway';

                    $waNumber = $coach->no_hp
                        ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $coach->no_hp))
                        : null;
                @endphp

                <div class="group relative bg-brand-card border border-brand-borderSoft rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 flex flex-col">
                    
                    {{-- Hover Glow Effect --}}
                    <div class="absolute inset-0 bg-gradient-to-br from-gold-500/0 via-gold-500/0 to-gold-500/0 group-hover:from-gold-500/5 group-hover:via-transparent group-hover:to-gold-500/5 transition-all duration-500 pointer-events-none z-10"></div>

                    {{-- Image Container with Overlay --}}
                    <div class="relative h-72 w-full overflow-hidden bg-brand-surface-50">
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $coach->nama ?? 'Coach' }}"
                            class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                            onerror="this.onerror=null; this.src='https://placehold.co/400x500/1F2937/FACC15?text={{ urlencode($coach->nama ?? 'COACH') }}&font=raleway';"
                        >
                        
                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-black/80 via-brand-black/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-500"></div>
                        
                        {{-- Status Badge --}}
                        <div class="absolute top-4 left-4 z-20">
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-brand-black/70 backdrop-blur-sm border border-gold-500/30 rounded-full">
                                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                                <span class="text-xs font-semibold text-brand-white">Tersedia</span>
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-5 flex flex-col flex-grow relative z-20">
                        {{-- Coach Name --}}
                        <h3 class="text-xl font-bold text-brand-white mb-2 group-hover:text-gold-300 transition-colors duration-300">
                            {{ $coach->nama ?? 'Nama Coach' }}
                        </h3>

                        {{-- Location --}}
                        @if (!empty($coach->alamat))
                            <div class="flex items-start gap-2 mb-3">
                                <i data-lucide="map-pin" class="w-4 h-4 text-gold-400 mt-0.5 flex-shrink-0"></i>
                                <p class="text-xs text-text-muted line-clamp-1">
                                    {{ $coach->alamat }}
                                </p>
                            </div>
                        @endif

                        {{-- Description --}}
                        <p class="text-sm text-text-muted mb-4 line-clamp-3 leading-relaxed">
                            {{ $coach->deskripsi
                                ? Str::limit($coach->deskripsi, 120)
                                : 'Personal trainer profesional yang siap membantu Anda mencapai target fitness dengan program latihan yang terstruktur dan efektif.' }}
                        </p>

                        {{-- Divider --}}
                        <div class="my-4 border-t border-brand-borderSoft/50"></div>

                        {{-- Action Section --}}
                        <div class="mt-auto">
                            @if ($waNumber)
                                <a 
                                    href="https://wa.me/{{ $waNumber }}"
                                    target="_blank"
                                    class="group/btn w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200"
                                >
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    <span>Hubungi via WhatsApp</span>
                                    <i data-lucide="external-link" class="w-3 h-3 opacity-0 group-hover/btn:opacity-100 transition-opacity"></i>
                                </a>
                            @elseif(!empty($coach->no_hp))
                                <div class="flex items-center justify-center gap-2 px-4 py-3 bg-brand-shell/50 border border-brand-borderSoft rounded-xl">
                                    <i data-lucide="phone" class="w-4 h-4 text-gold-400"></i>
                                    <span class="text-sm text-text-muted font-medium">{{ $coach->no_hp }}</span>
                                </div>
                            @else
                                <div class="text-center text-xs text-text-muted/60 py-3">
                                    Kontak tidak tersedia
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($coaches->hasPages())
            <div class="mt-8">
                {{ $coaches->links() }}
            </div>
        @endif
    @endif

    {{-- CTA SECTION --}}
    <div class="mt-12 p-8 bg-gradient-to-br from-brand-gunmetal to-brand-card border border-brand-borderSoft rounded-2xl shadow-xl">
        <div class="text-center max-w-2xl mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gold-400 to-gold-600 flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i data-lucide="trophy" class="w-8 h-8 text-brand-black"></i>
            </div>
            <h3 class="text-2xl font-bold text-brand-white mb-3">
                Butuh Bantuan Memilih Coach?
            </h3>
            <p class="text-text-muted mb-6">
                Tim kami siap membantu Anda menemukan personal trainer yang tepat sesuai dengan tujuan fitness Anda.
            </p>
            <a 
                href="https://wa.me/6281234567890" 
                target="_blank"
                class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700 text-brand-black font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200"
            >
                <i data-lucide="headphones" class="w-5 h-5"></i>
                <span>Hubungi Customer Service</span>
            </a>
        </div>
    </div>

</x-layouts.member>
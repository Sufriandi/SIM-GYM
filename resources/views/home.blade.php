@php
    use Illuminate\Support\Facades\Route;
    use App\Models\PaketMembership;
    use App\Models\Coach;

    $pageTitle = $pageTitle ?? 'BETA GYM – Build a Better You';
    $registerUrl = Route::has('register') ? route('register') : url('/register');
    
    // Fetch data from database
    $paketMemberships = PaketMembership::orderBy('harga', 'asc')->get();
    $coaches = Coach::inRandomOrder()->limit(6)->get();
    
    // Statistics (could be dynamic from database)
    $totalMembers = 1500;
    $totalClasses = 25;
    $happyClients = 98;
@endphp

<x-layouts.guest :title="$pageTitle">

    {{-- ========================================================= --}}
    {{-- HERO SECTION --}}
    {{-- ========================================================= --}}
    <section class="relative min-h-screen flex items-center overflow-hidden">
        {{-- Background Overlay --}}
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop"
                 alt="Gym Background"
                 class="w-full h-full object-cover opacity-30 mix-blend-overlay">

            <div class="absolute inset-0 bg-brand-overlay-dark"></div>
            <div class="absolute top-0 right-0 w-full h-full bg-brand-radial-spot opacity-60 pointer-events-none"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10 grid md:grid-cols-2 gap-12 items-center">

            {{-- Left Content --}}
            <div class="space-y-6 animate-slide-up" style="animation-delay: 0.2s;">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-pill border border-gold-500/30 bg-gold-900/10 backdrop-blur-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-gold-500"></span>
                    </span>
                    <span class="text-[10px] font-bold tracking-widest text-gold-500 uppercase font-heading">Gym Premium No.1 Bengkalis</span>
                </div>

                <h1 class="text-display md:text-display font-display font-bold text-brand-white leading-none tracking-tight">
                    BUKAN SEKADAR <br>
                    <span class="text-transparent bg-clip-text bg-brand-gold">TEMPAT LATIHAN.</span>
                </h1>

                <p class="text-lg text-brand-silver max-w-lg leading-relaxed font-sans">
                    Transformasi tubuh dan mental Anda bersama BETA GYM. Fasilitas standar atlet, komunitas suportif, dan hasil nyata.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <a href="{{ $registerUrl }}"
                       class="px-8 py-4 rounded-pill font-heading font-bold text-brand-nav tracking-wide bg-gold-500 hover:bg-gold-400 shadow-gold-glow hover:-translate-y-1 transition-all duration-normal active:scale-98 text-center">
                        Mulai Free Trial
                    </a>
                    <a href="#membership"
                       class="px-8 py-4 rounded-pill font-heading font-bold text-brand-white border border-brand-borderSoft/20 hover:bg-brand-white/10 transition-all duration-normal flex items-center justify-center gap-2 group">
                        <i data-lucide="play-circle" class="w-5 h-5 text-gold-500 group-hover:text-brand-white transition-colors"></i>
                        Lihat Paket
                    </a>
                </div>

                {{-- Stats --}}
                <div class="flex items-center gap-8 pt-8 border-t border-brand-borderSoft/10">
                    <div>
                        <p class="text-3xl font-display font-bold text-brand-white">24/7</p>
                        <p class="text-xs text-brand-silver uppercase tracking-wider font-bold">Akses Gym</p>
                    </div>
                    <div>
                        <p class="text-3xl font-display font-bold text-brand-white">50+</p>
                        <p class="text-xs text-brand-silver uppercase tracking-wider font-bold">Alat Pro</p>
                    </div>
                    <div>
                        <p class="text-3xl font-display font-bold text-brand-white">{{ $coaches->count() }}+</p>
                        <p class="text-xs text-brand-silver uppercase tracking-wider font-bold">Expert Coach</p>
                    </div>
                </div>
            </div>

            {{-- Right Content (Visual) --}}
            <div class="relative hidden md:block animate-float">
                {{-- Main Image Card --}}
                <div class="relative z-10 rounded-3xl overflow-hidden border border-brand-borderSoft/20 shadow-card-strong transform rotate-2 hover:rotate-0 transition-all duration-slow">
                    <img src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=2070&auto=format&fit=crop"
                         alt="Athlete" class="w-full h-[550px] object-cover">

                    {{-- Overlay Card --}}
                    <div class="absolute bottom-6 left-6 right-6 p-4 bg-brand-nav/80 backdrop-blur-md border border-brand-borderSoft/20 rounded-2xl flex items-center gap-4 shadow-lg">
                        <div class="w-12 h-12 rounded-xl bg-gold-500 flex items-center justify-center">
                            <i data-lucide="trophy" class="w-6 h-6 text-brand-nav"></i>
                        </div>
                        <div>
                            <p class="text-brand-white font-bold font-heading uppercase">Best Facility Award</p>
                            <p class="text-xs text-gold-300">2025 - Bengkalis Health & Fit</p>
                        </div>
                    </div>
                </div>

                {{-- Decorative Glows --}}
                <div class="absolute -top-10 -right-10 w-64 h-64 bg-gold-500/10 rounded-full blur-3xl z-0 pointer-events-none"></div>
                <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-accent-500/10 rounded-full blur-3xl z-0 pointer-events-none"></div>
            </div>

        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- STATISTICS COUNTER --}}
    {{-- ========================================================= --}}
    <section class="py-16 bg-brand-ember relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-5xl md:text-6xl font-display font-bold text-gold-500 mb-2">{{ number_format($totalMembers) }}+</div>
                    <p class="text-brand-white/80 font-heading uppercase text-sm tracking-wider">Active Members</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl md:text-6xl font-display font-bold text-gold-500 mb-2">{{ $totalClasses }}+</div>
                    <p class="text-brand-white/80 font-heading uppercase text-sm tracking-wider">Classes Weekly</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl md:text-6xl font-display font-bold text-gold-500 mb-2">{{ $happyClients }}%</div>
                    <p class="text-brand-white/80 font-heading uppercase text-sm tracking-wider">Happy Clients</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl md:text-6xl font-display font-bold text-gold-500 mb-2">5+</div>
                    <p class="text-brand-white/80 font-heading uppercase text-sm tracking-wider">Years Experience</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- WHY CHOOSE US --}}
    {{-- ========================================================= --}}
    <section class="py-24 bg-brand-nav" id="fasilitas">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Kenapa Memilih Kami?</span>
                <h2 class="mt-2 text-hero font-display text-brand-white">
                    DEFINISIKAN ULANG <br> <span class="text-transparent bg-clip-text bg-brand-gold">POTENSI DIRIMU</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="dumbbell" class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Peralatan Premium</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Peralatan standar internasional untuk keamanan maksimal dan progres yang konsisten.
                    </p>
                </div>

                <div class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="users" class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Komunitas Positif</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Lingkungan nyaman untuk pemula sampai advanced. Fokus progres, bukan intimidasi.
                    </p>
                </div>

                <div class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="activity" class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Personal Training</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Program yang dipersonalisasi sesuai tujuan, dipandu coach berpengalaman.
                    </p>
                </div>

                <div class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="clock" class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Fleksibel 24/7</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Akses gym kapan saja sesuai jadwal Anda. Pagi, siang, malam, bahkan tengah malam.
                    </p>
                </div>

                <div class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="shield-check" class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Keamanan Terjamin</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        CCTV 24 jam, sistem akses digital, dan protokol kesehatan yang ketat.
                    </p>
                </div>

                <div class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="sparkles" class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Fasilitas Lengkap</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Sauna, ruang ganti premium, shower, loker pribadi, dan area relaksasi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- OUR EXPERT COACHES --}}
    {{-- ========================================================= --}}
    @if($coaches->count() > 0)
    <section class="py-24 bg-brand-dark relative overflow-hidden" id="coaches">
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(#A67C39 1px, transparent 1px); background-size: 30px 30px;"></div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Tim Profesional Kami</span>
                <h2 class="mt-2 text-hero font-display text-brand-white">
                    EXPERT COACHES <br> <span class="text-transparent bg-clip-text bg-brand-gold">SIAP MEMBIMBING</span>
                </h2>
                <p class="text-brand-silver mt-4">
                    Dipandu oleh coach bersertifikat internasional dengan pengalaman puluhan tahun
                </p>
            </div>

            {{-- Coach Slider Container --}}
            <div class="relative">
                <div class="coach-slider-wrapper overflow-hidden">
                    <div class="coach-slider flex gap-6 transition-transform duration-500 ease-in-out" id="coachSlider">
                        @foreach($coaches as $coach)
                        <div class="coach-card flex-none w-full md:w-1/3 group">
                            <div class="relative rounded-3xl overflow-hidden bg-brand-sidebar border border-brand-borderSoft/10 hover:border-gold-500/30 transition-all duration-500 shadow-card hover:shadow-gold-glow">
                                {{-- Coach Image --}}
                                <div class="relative h-80 overflow-hidden">
                                    <img src="{{ asset('storage/' . $coach->foto) }}" 
                                         alt="{{ $coach->nama }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                         onerror="this.src='https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?q=80&w=2070&auto=format&fit=crop'">
                                    
                                    {{-- Gradient Overlay --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-brand-nav via-brand-nav/50 to-transparent opacity-80"></div>
                                    
                                    {{-- Name Badge --}}
                                    <div class="absolute bottom-6 left-6 right-6">
                                        <h3 class="text-2xl font-bold font-heading text-brand-white mb-1">{{ $coach->nama }}</h3>
                                        <div class="flex items-center gap-2 text-gold-500 text-sm">
                                            <i data-lucide="award" class="w-4 h-4"></i>
                                            <span class="font-semibold">Certified Coach</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Coach Info --}}
                                <div class="p-6 space-y-4">
                                    <p class="text-brand-silver text-sm leading-relaxed line-clamp-3">
                                        {{ $coach->deskripsi ?? 'Professional trainer dengan pengalaman melatih ratusan member dari berbagai level fitness.' }}
                                    </p>

                                    @if($coach->no_hp)
                                    <div class="flex items-center gap-3 text-brand-silver text-sm">
                                        <div class="w-8 h-8 rounded-lg bg-brand-surface-200/10 flex items-center justify-center">
                                            <i data-lucide="phone" class="w-4 h-4 text-gold-500"></i>
                                        </div>
                                        <span>{{ $coach->no_hp }}</span>
                                    </div>
                                    @endif

                                    <button class="w-full py-3 rounded-pill border border-gold-500/30 text-gold-500 font-bold hover:bg-gold-500 hover:text-brand-nav transition-all duration-normal text-sm font-heading">
                                        Jadwalkan Sesi
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Navigation Arrows --}}
                @if($coaches->count() > 3)
                <button id="prevCoach" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-12 h-12 rounded-full bg-gold-500 hover:bg-gold-400 text-brand-nav shadow-lg hover:shadow-gold-glow transition-all duration-normal z-10 flex items-center justify-center group">
                    <i data-lucide="chevron-left" class="w-6 h-6 group-hover:-translate-x-1 transition-transform"></i>
                </button>
                <button id="nextCoach" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-12 h-12 rounded-full bg-gold-500 hover:bg-gold-400 text-brand-nav shadow-lg hover:shadow-gold-glow transition-all duration-normal z-10 flex items-center justify-center group">
                    <i data-lucide="chevron-right" class="w-6 h-6 group-hover:translate-x-1 transition-transform"></i>
                </button>
                @endif

                {{-- Dots Indicator --}}
                <div class="flex justify-center gap-2 mt-8">
                    @for($i = 0; $i < ceil($coaches->count() / 3); $i++)
                    <button class="coach-dot w-2 h-2 rounded-full bg-brand-silver/30 hover:bg-gold-500 transition-all duration-normal" data-index="{{ $i }}"></button>
                    @endfor
                </div>
            </div>
        </div>

        {{-- Coach Slider Script --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const slider = document.getElementById('coachSlider');
                const prevBtn = document.getElementById('prevCoach');
                const nextBtn = document.getElementById('nextCoach');
                const dots = document.querySelectorAll('.coach-dot');
                
                let currentIndex = 0;
                const totalSlides = Math.ceil({{ $coaches->count() }} / 3);
                const isMobile = window.innerWidth < 768;
                const slidesPerView = isMobile ? 1 : 3;

                function updateSlider() {
                    const offset = -currentIndex * (100 / slidesPerView);
                    slider.style.transform = `translateX(${offset}%)`;
                    
                    // Update dots
                    dots.forEach((dot, index) => {
                        if(index === currentIndex) {
                            dot.classList.add('bg-gold-500', 'w-8');
                            dot.classList.remove('bg-brand-silver/30', 'w-2');
                        } else {
                            dot.classList.remove('bg-gold-500', 'w-8');
                            dot.classList.add('bg-brand-silver/30', 'w-2');
                        }
                    });
                }

                if(prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        currentIndex = currentIndex > 0 ? currentIndex - 1 : totalSlides - 1;
                        updateSlider();
                    });
                }

                if(nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        currentIndex = currentIndex < totalSlides - 1 ? currentIndex + 1 : 0;
                        updateSlider();
                    });
                }

                dots.forEach(dot => {
                    dot.addEventListener('click', (e) => {
                        currentIndex = parseInt(e.target.dataset.index);
                        updateSlider();
                    });
                });

                // Auto slide every 5 seconds
                setInterval(() => {
                    currentIndex = currentIndex < totalSlides - 1 ? currentIndex + 1 : 0;
                    updateSlider();
                }, 5000);

                updateSlider();
            });
        </script>
    </section>
    @endif

    {{-- ========================================================= --}}
    {{-- PROGRAM KELAS --}}
    {{-- ========================================================= --}}
    <section class="py-24 relative bg-brand-nav" id="program">
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div>
                    <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Variasi Latihan</span>
                    <h2 class="text-4xl font-display font-bold text-brand-white mt-2">KELAS POPULER</h2>
                    <p class="text-brand-silver mt-2">Variasi latihan agar kamu tidak pernah bosan.</p>
                </div>
                <a href="{{ url('/schedule') }}" class="inline-flex items-center gap-2 text-gold-500 font-bold hover:text-gold-400 transition-colors group">
                    Lihat Jadwal Lengkap <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 h-[500px]">
                <div class="md:col-span-2 md:row-span-2 relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1599058945522-28d584b6f0ff?q=80&w=2069&auto=format&fit=crop"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="HIIT">
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <span class="px-3 py-1 bg-gold-500 text-brand-nav text-xs font-bold font-heading rounded-md mb-2 inline-block">INTENSE</span>
                        <h3 class="text-2xl font-bold font-heading text-brand-white">HIIT Cardio</h3>
                        <p class="text-brand-silver text-sm mt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            Bakar kalori maksimal dalam waktu singkat dengan intensitas tinggi.
                        </p>
                        <div class="flex items-center gap-4 mt-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <span class="text-xs text-brand-white/70 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> 45 min
                            </span>
                            <span class="text-xs text-brand-white/70 flex items-center gap-1">
                                <i data-lucide="flame" class="w-3 h-3"></i> 500+ kcal
                            </span>
                        </div>
                    </div>
                </div>

                <div class="relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=2070&auto=format&fit=crop"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="Yoga">
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6">
                        <span class="px-3 py-1 bg-brand-white/20 text-brand-white text-xs font-bold font-heading rounded-md mb-2 inline-block">RELAX</span>
                        <h3 class="text-xl font-bold font-heading text-brand-white">Power Yoga</h3>
                    </div>
                </div>

                <div class="relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1534367507873-d2d7e24c797f?q=80&w=2070&auto=format&fit=crop"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="Crossfit">
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6">
                        <span class="px-3 py-1 bg-brand-white/20 text-brand-white text-xs font-bold font-heading rounded-md mb-2 inline-block">FUNCTIONAL</span>
                        <h3 class="text-xl font-bold font-heading text-brand-white">Cross Training</h3>
                    </div>
                </div>

                <div class="md:col-span-2 relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1579758629938-03607ccdbaba?q=80&w=2070&auto=format&fit=crop"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="Weightlifting">
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6">
                        <span class="px-3 py-1 bg-brand-white text-brand-nav text-xs font-bold font-heading rounded-md mb-2 inline-block">STRENGTH</span>
                        <h3 class="text-2xl font-bold font-heading text-brand-white">Heavy Lifting</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- MEMBERSHIP PACKAGES (FROM DATABASE) --}}
    {{-- ========================================================= --}}
    <section class="py-24 bg-brand-footer relative overflow-hidden" id="membership">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gold-900/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Pilih Paket Terbaik</span>
                <h2 class="text-hero font-display text-brand-white mt-2">INVESTASI KESEHATANMU</h2>
                <p class="text-brand-silver mt-4">Tanpa biaya tersembunyi. Batalkan kapan saja.</p>
            </div>

            @if($paketMemberships->count() > 0)
            <div class="grid md:grid-cols-{{ min($paketMemberships->count(), 3) }} gap-8 items-start max-w-6xl mx-auto">
                @foreach($paketMemberships as $index => $paket)
                <div class="relative p-8 rounded-3xl transition-all duration-normal shadow-card
                    {{ $index === 1 ? 'bg-brand-surface-200/5 border-2 border-gold-600 shadow-gold-glow md:-translate-y-4' : 'bg-brand-sidebar border border-brand-borderSoft/10 hover:border-brand-borderSoft/30' }}">
                    
                    @if($index === 1)
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-brand-gold px-4 py-1 rounded-pill text-xs font-bold text-brand-nav uppercase tracking-wider shadow-lg font-heading">
                        Paling Laris
                    </div>
                    @endif

                    {{-- Package Name --}}
                    <h3 class="text-xl font-bold font-heading {{ $index === 1 ? 'text-brand-white' : 'text-brand-white' }}">
                        {{ $paket->nama }}
                    </h3>

                    {{-- Price --}}
                    <div class="my-6">
                        <span class="text-5xl font-display font-bold {{ $index === 1 ? 'text-transparent bg-clip-text bg-brand-gold' : 'text-brand-white' }}">
                            Rp {{ number_format($paket->harga / 1000, 0) }}k
                        </span>
                        <span class="text-brand-muted text-sm">/{{ $paket->durasi }} hari</span>
                    </div>

                    {{-- Tipe Badge --}}
                    @if($paket->tipe)
                    <div class="mb-4">
                        <span class="px-3 py-1 bg-gold-900/20 text-gold-500 text-xs font-bold rounded-md inline-block border border-gold-500/20">
                            {{ strtoupper($paket->tipe) }}
                        </span>
                    </div>
                    @endif

                    {{-- Description --}}
                    @if($paket->deskripsi)
                    <div class="mb-6 pb-6 border-b border-brand-borderSoft/10">
                        <p class="text-brand-silver text-sm leading-relaxed">
                            {{ $paket->deskripsi }}
                        </p>
                    </div>
                    @endif

                    {{-- Features (you can customize based on package type) --}}
                    <ul class="space-y-4 mb-8 {{ $index === 1 ? 'text-brand-white' : 'text-brand-silver' }} text-sm">
                        <li class="flex items-center gap-3">
                            <i data-lucide="{{ $index === 1 ? 'check-circle' : 'check' }}" class="w-5 h-5 {{ $index === 1 ? 'text-gold-500' : 'text-success' }} flex-shrink-0"></i>
                            <span>Akses Gym {{ $paket->durasi >= 30 ? '24/7' : 'Terbatas' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="{{ $index === 1 ? 'check-circle' : 'check' }}" class="w-5 h-5 {{ $index === 1 ? 'text-gold-500' : 'text-success' }} flex-shrink-0"></i>
                            <span>{{ $paket->tipe === 'premium' ? 'Semua' : 'Pilihan' }} Kelas Grup</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="{{ $index === 1 ? 'check-circle' : 'check' }}" class="w-5 h-5 {{ $index === 1 ? 'text-gold-500' : 'text-success' }} flex-shrink-0"></i>
                            <span>Loker {{ $paket->tipe === 'premium' ? 'VIP + Handuk' : 'Standar' }}</span>
                        </li>
                        @if($paket->tipe === 'premium' || $paket->durasi >= 180)
                        <li class="flex items-center gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-gold-500 flex-shrink-0"></i>
                            <span><strong>Konsultasi Personal Trainer</strong></span>
                        </li>
                        @endif
                        @if($paket->durasi >= 365)
                        <li class="flex items-center gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-gold-500 flex-shrink-0"></i>
                            <span>Gratis 1 Bulan Membership</span>
                        </li>
                        @endif
                    </ul>

                    {{-- CTA Button --}}
                    <a href="{{ $registerUrl }}" class="block w-full py-4 rounded-pill text-center font-bold transition-all duration-normal
                        {{ $index === 1 ? 'bg-accent-gradient text-brand-white shadow-btn-primary hover:shadow-btn-primary-hover hover:scale-102' : 'border border-brand-borderSoft/20 text-brand-white hover:bg-brand-white hover:text-brand-nav' }}">
                        {{ $index === 1 ? 'Gabung Sekarang' : 'Pilih Paket' }}
                    </a>
                </div>
                @endforeach
            </div>
            @else
            {{-- Fallback if no packages in database --}}
            <div class="text-center py-12">
                <p class="text-brand-silver">Paket membership akan segera tersedia.</p>
                <a href="{{ $registerUrl }}" class="inline-block mt-6 px-8 py-4 bg-gold-500 text-brand-nav font-bold rounded-pill hover:bg-gold-400 transition-all">
                    Daftar Free Trial
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- TESTIMONIALS --}}
    {{-- ========================================================= --}}
    <section class="py-24 bg-brand-nav">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Kata Mereka</span>
                <h2 class="mt-2 text-hero font-display text-brand-white">
                    TRANSFORMASI <span class="text-transparent bg-clip-text bg-brand-gold">NYATA</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-brand-sidebar p-8 rounded-3xl border border-brand-borderSoft/10 shadow-card">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <i data-lucide="star" class="w-4 h-4 fill-gold-500 text-gold-500"></i>
                        @endfor
                    </div>
                    <p class="text-brand-white mb-6 leading-relaxed">
                        "Dalam 3 bulan, berat badan turun 15kg dan stamina meningkat drastis. Coach-nya sangat supportive!"
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gold-500/20 flex items-center justify-center">
                            <i data-lucide="user" class="w-6 h-6 text-gold-500"></i>
                        </div>
                        <div>
                            <p class="font-bold text-brand-white">Andi Wijaya</p>
                            <p class="text-xs text-brand-silver">Member sejak 2024</p>
                        </div>
                    </div>
                </div>

                <div class="bg-brand-sidebar p-8 rounded-3xl border border-brand-borderSoft/10 shadow-card">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <i data-lucide="star" class="w-4 h-4 fill-gold-500 text-gold-500"></i>
                        @endfor
                    </div>
                    <p class="text-brand-white mb-6 leading-relaxed">
                        "Fasilitas top, trainer professional, dan yang paling penting: komunitasnya bikin semangat terus!"
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gold-500/20 flex items-center justify-center">
                            <i data-lucide="user" class="w-6 h-6 text-gold-500"></i>
                        </div>
                        <div>
                            <p class="font-bold text-brand-white">Siti Rahma</p>
                            <p class="text-xs text-brand-silver">Member sejak 2023</p>
                        </div>
                    </div>
                </div>

                <div class="bg-brand-sidebar p-8 rounded-3xl border border-brand-borderSoft/10 shadow-card">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <i data-lucide="star" class="w-4 h-4 fill-gold-500 text-gold-500"></i>
                        @endfor
                    </div>
                    <p class="text-brand-white mb-6 leading-relaxed">
                        "Gym paling lengkap di Bengkalis! Dari pemula sampai atlet bisa latihan maksimal di sini."
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gold-500/20 flex items-center justify-center">
                            <i data-lucide="user" class="w-6 h-6 text-gold-500"></i>
                        </div>
                        <div>
                            <p class="font-bold text-brand-white">Budi Santoso</p>
                            <p class="text-xs text-brand-silver">Member sejak 2022</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- FAQ SECTION --}}
    {{-- ========================================================= --}}
    <section class="py-24 bg-brand-dark">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Pertanyaan Umum</span>
                <h2 class="mt-2 text-hero font-display text-brand-white">
                    ADA PERTANYAAN?
                </h2>
            </div>

            <div class="max-w-3xl mx-auto space-y-4">
                <details class="group bg-brand-sidebar rounded-2xl border border-brand-borderSoft/10 overflow-hidden">
                    <summary class="flex justify-between items-center p-6 cursor-pointer hover:bg-brand-surface-200/5 transition-colors">
                        <span class="font-bold text-brand-white font-heading">Apakah ada free trial?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-gold-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-6 pb-6 text-brand-silver">
                        Ya! Kami menyediakan free trial 1 hari untuk member baru. Daftar sekarang dan rasakan pengalaman BETA GYM.
                    </div>
                </details>

                <details class="group bg-brand-sidebar rounded-2xl border border-brand-borderSoft/10 overflow-hidden">
                    <summary class="flex justify-between items-center p-6 cursor-pointer hover:bg-brand-surface-200/5 transition-colors">
                        <span class="font-bold text-brand-white font-heading">Bisa batalkan membership kapan saja?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-gold-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-6 pb-6 text-brand-silver">
                        Untuk paket bulanan, Anda bisa memberitahu kami 7 hari sebelum periode berakhir. Tidak ada biaya pembatalan.
                    </div>
                </details>

                <details class="group bg-brand-sidebar rounded-2xl border border-brand-borderSoft/10 overflow-hidden">
                    <summary class="flex justify-between items-center p-6 cursor-pointer hover:bg-brand-surface-200/5 transition-colors">
                        <span class="font-bold text-brand-white font-heading">Apakah cocok untuk pemula?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-gold-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-6 pb-6 text-brand-silver">
                        Sangat cocok! Kami menyediakan program khusus pemula dengan panduan coach yang akan membantu Anda dari awal.
                    </div>
                </details>

                <details class="group bg-brand-sidebar rounded-2xl border border-brand-borderSoft/10 overflow-hidden">
                    <summary class="flex justify-between items-center p-6 cursor-pointer hover:bg-brand-surface-200/5 transition-colors">
                        <span class="font-bold text-brand-white font-heading">Fasilitas apa saja yang tersedia?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-gold-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-6 pb-6 text-brand-silver">
                        Cardio zone, strength training area, free weights, functional training zone, sauna, loker, shower, area relaksasi, dan masih banyak lagi.
                    </div>
                </details>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- CTA SECTION --}}
    {{-- ========================================================= --}}
    <section class="py-20 bg-brand-footer">
        <div class="container mx-auto px-6">
            <div class="relative rounded-[2.5rem] overflow-hidden bg-brand-ember shadow-card-strong">
                <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>

                <div class="relative z-10 px-8 py-20 text-center">
                    <h2 class="text-4xl md:text-display font-display font-bold text-brand-white mb-6">SIAP UNTUK PERUBAHAN?</h2>
                    <p class="text-brand-white/90 text-lg mb-8 max-w-xl mx-auto">
                        Jangan menunggu "waktu yang tepat". Mulailah hari ini dan rasakan bedanya dalam 30 hari pertama.
                    </p>

                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ $registerUrl }}" class="px-10 py-4 bg-brand-white text-brand-nav font-heading font-bold rounded-pill hover:bg-brand-surface-50 transition shadow-xl hover:-translate-y-1">
                            Daftar Member
                        </a>
                        <a href="{{ url('/contact') }}" class="px-10 py-4 bg-transparent border-2 border-brand-white text-brand-white font-heading font-bold rounded-pill hover:bg-brand-white hover:text-brand-nav transition">
                            Hubungi Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- SCROLLING BRANDS (MARQUEE) --}}
    {{-- ========================================================= --}}
    <div class="py-8 bg-brand-dark border-y border-brand-borderSoft/5 overflow-hidden">
        <div class="relative w-full">
            <div class="flex animate-marquee items-center gap-16 whitespace-nowrap">
                @foreach(range(1, 20) as $i)
                    <div class="flex items-center gap-2 opacity-30 hover:opacity-100 transition-opacity cursor-default group">
                        <i data-lucide="dumbbell" class="w-6 h-6 text-gold-600 group-hover:text-gold-400"></i>
                        <span class="font-display font-bold text-xl text-brand-silver group-hover:text-brand-white">
                            PARTNER BRAND {{ $i }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</x-layouts.guest>

<style>
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.animate-marquee {
    animation: marquee 40s linear infinite;
    width: 200%;
}

.animate-marquee:hover {
    animation-play-state: paused;
}
</style>
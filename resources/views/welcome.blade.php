<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BETA GYM – Build a Better You</title>

    {{-- Production Setup --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Fallback Script untuk Preview --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Import Fonts jika belum ada di app.css */
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap');

        /* ANIMATION UTILITIES */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up {
            animation: slideUpFade 0.8s ease-out forwards;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-marquee {
            animation: marquee 30s linear infinite;
        }
    </style>
</head>

{{-- Menggunakan bg-brand-dark dari config (Gradasi Dark Premium) --}}

<body
    class="bg-brand-dark text-brand-silver font-sans antialiased overflow-x-hidden selection:bg-gold-500 selection:text-brand-nav">

    <x-guest.navbar />

    {{-- ========================================================= --}}
    {{-- HERO SECTION --}}
    {{-- ========================================================= --}}
    <section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        {{-- Background Overlay --}}
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop"
                alt="Gym Background" class="w-full h-full object-cover opacity-30 mix-blend-overlay">

            {{-- Menggunakan class brand-overlay-dark dari config --}}
            <div class="absolute inset-0 bg-brand-overlay-dark"></div>
            {{-- Menggunakan brand-radial-spot dari config --}}
            <div class="absolute top-0 right-0 w-full h-full bg-brand-radial-spot opacity-60 pointer-events-none"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10 grid md:grid-cols-2 gap-12 items-center">

            {{-- Left Content --}}
            <div class="space-y-6 animate-slide-up" style="animation-delay: 0.2s;">
                {{-- Badge --}}
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-pill border border-gold-500/30 bg-gold-900/10 backdrop-blur-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-gold-500"></span>
                    </span>
                    <span class="text-[10px] font-bold tracking-widest text-gold-500 uppercase font-heading">Gym Premium
                        No.1 Bengkalis</span>
                </div>

                <h1
                    class="text-display md:text-display font-display font-bold text-brand-white leading-none tracking-tight">
                    BUKAN SEKADAR <br>
                    {{-- Text Gradient Gold dari config --}}
                    <span class="text-transparent bg-clip-text bg-brand-gold">TEMPAT LATIHAN.</span>
                </h1>

                <p class="text-lg text-brand-silver max-w-lg leading-relaxed font-sans">
                    Transformasi tubuh dan mental Anda bersama BETA GYM. Fasilitas standar atlet, komunitas suportif,
                    dan hasil nyata.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    {{-- Button Primary --}}
                    <button
                        class="px-8 py-4 rounded-pill font-heading font-bold text-brand-nav tracking-wide bg-gold-500 hover:bg-gold-400 shadow-gold-glow hover:-translate-y-1 transition-all duration-normal active:scale-98">
                        Mulai Free Trial
                    </button>
                    {{-- Button Secondary --}}
                    <button
                        class="px-8 py-4 rounded-pill font-heading font-bold text-brand-white border border-brand-borderSoft/20 hover:bg-brand-white/10 transition-all duration-normal flex items-center justify-center gap-2 group">
                        <i data-lucide="play-circle"
                            class="w-5 h-5 text-gold-500 group-hover:text-brand-white transition-colors"></i>
                        Lihat Video Tur
                    </button>
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
                        <p class="text-3xl font-display font-bold text-brand-white">15+</p>
                        <p class="text-xs text-brand-silver uppercase tracking-wider font-bold">Expert Coach</p>
                    </div>
                </div>
            </div>

            {{-- Right Content (Visual) --}}
            <div class="relative hidden md:block animate-float">
                {{-- Main Image Card --}}
                <div
                    class="relative z-10 rounded-3xl overflow-hidden border border-brand-borderSoft/20 shadow-card-strong transform rotate-2 hover:rotate-0 transition-all duration-slow">
                    <img src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=2070&auto=format&fit=crop"
                        alt="Athlete" class="w-full h-[550px] object-cover">

                    {{-- Overlay Card --}}
                    <div
                        class="absolute bottom-6 left-6 right-6 p-4 bg-brand-nav/80 backdrop-blur-md border border-brand-borderSoft/20 rounded-2xl flex items-center gap-4 shadow-lg">
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
                <div
                    class="absolute -top-10 -right-10 w-64 h-64 bg-gold-500/10 rounded-full blur-3xl z-0 pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-10 -left-10 w-64 h-64 bg-accent-500/10 rounded-full blur-3xl z-0 pointer-events-none">
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- SCROLLING BRANDS (MARQUEE) --}}
    {{-- ========================================================= --}}
    <div class="py-8 bg-brand-footer border-y border-brand-borderSoft/5 overflow-hidden">
        <div class="relative w-full">
            <div class="flex w-[200%] animate-marquee items-center gap-16 whitespace-nowrap">
                @foreach (range(1, 20) as $i)
                    <div
                        class="flex items-center gap-2 opacity-30 hover:opacity-100 transition-opacity cursor-default group">
                        <i data-lucide="dumbbell" class="w-6 h-6 text-gold-600 group-hover:text-gold-400"></i>
                        <span
                            class="font-display font-bold text-xl text-brand-silver group-hover:text-brand-white">PARTNER
                            BRAND {{ $i }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- WHY CHOOSE US --}}
    {{-- ========================================================= --}}
    <section class="py-24 bg-brand-nav" id="fasilitas">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Kenapa Memilih
                    Kami?</span>
                <h2 class="mt-2 text-hero font-display text-brand-white">DEFINISIKAN ULANG <br> <span
                        class="text-transparent bg-clip-text bg-brand-gold">POTENSI DIRIMU</span></h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div
                    class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div
                        class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="biceps-flex"
                            class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Peralatan Premium</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Kami menggunakan peralatan impor standar internasional (Technogym & Rogue) untuk keamanan
                        maksimal.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div
                    class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div
                        class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="users"
                            class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Komunitas Positif</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Lingkungan yang tidak mengintimidasi. Baik pemula maupun atlet pro, semua saling mendukung.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div
                    class="group p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/5 hover:border-gold-500/30 hover:bg-brand-surface-200/5 transition-all duration-normal hover:-translate-y-2 shadow-card">
                    <div
                        class="w-14 h-14 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 flex items-center justify-center mb-6 group-hover:bg-gold-500 transition-colors duration-normal">
                        <i data-lucide="activity"
                            class="w-7 h-7 text-gold-500 group-hover:text-brand-nav transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white mb-3">Personal Training</h3>
                    <p class="text-brand-silver text-sm leading-relaxed">
                        Program yang dipersonalisasi khusus untuk tubuh dan tujuanmu, dipandu oleh coach bersertifikat.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- PROGRAM KELAS --}}
    {{-- ========================================================= --}}
    <section class="py-24 relative bg-brand-dark" id="program">
        <div class="absolute inset-0">
            {{-- Pattern --}}
            <div class="absolute inset-0 opacity-5"
                style="background-image: radial-gradient(#A67C39 1px, transparent 1px); background-size: 30px 30px;">
            </div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div>
                    <h2 class="text-4xl font-display font-bold text-brand-white">KELAS POPULER</h2>
                    <p class="text-brand-silver mt-2">Variasi latihan agar kamu tidak pernah bosan.</p>
                </div>
                <a href="#"
                    class="inline-flex items-center gap-2 text-gold-500 font-bold hover:text-gold-400 transition-colors">
                    Lihat Jadwal Lengkap <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 h-[500px]">
                {{-- Class Item 1 (Large) --}}
                <div
                    class="md:col-span-2 md:row-span-2 relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1599058945522-28d584b6f0ff?q=80&w=2069&auto=format&fit=crop"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        alt="HIIT">
                    {{-- Overlay menggunakan brand-overlay-dark --}}
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6">
                        <span
                            class="px-3 py-1 bg-gold-500 text-brand-nav text-xs font-bold font-heading rounded-md mb-2 inline-block">INTENSE</span>
                        <h3 class="text-2xl font-bold font-heading text-brand-white">HIIT Cardio</h3>
                        <p
                            class="text-brand-silver text-sm mt-1 h-0 overflow-hidden group-hover:h-auto transition-all duration-300">
                            Bakar kalori maksimal dalam waktu singkat.</p>
                    </div>
                </div>

                {{-- Class Item 2 --}}
                <div class="relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=2070&auto=format&fit=crop"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        alt="Yoga">
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6">
                        <h3 class="text-xl font-bold font-heading text-brand-white">Power Yoga</h3>
                    </div>
                </div>

                {{-- Class Item 3 --}}
                <div class="relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1534367507873-d2d7e24c797f?q=80&w=2070&auto=format&fit=crop"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        alt="Crossfit">
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6">
                        <h3 class="text-xl font-bold font-heading text-brand-white">CrossFit</h3>
                    </div>
                </div>

                {{-- Class Item 4 --}}
                <div class="md:col-span-2 relative group overflow-hidden rounded-3xl cursor-pointer shadow-card">
                    <img src="https://images.unsplash.com/photo-1579758629938-03607ccdbaba?q=80&w=2070&auto=format&fit=crop"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        alt="Weightlifting">
                    <div class="absolute inset-0 bg-brand-overlay-dark opacity-90"></div>
                    <div class="absolute bottom-6 left-6">
                        <span
                            class="px-3 py-1 bg-brand-white text-brand-nav text-xs font-bold font-heading rounded-md mb-2 inline-block">STRENGTH</span>
                        <h3 class="text-2xl font-bold font-heading text-brand-white">Heavy Lifting</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- MEMBERSHIP --}}
    {{-- ========================================================= --}}
    <section class="py-24 bg-brand-footer relative overflow-hidden" id="membership">
        {{-- Ambient Light --}}
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gold-900/10 rounded-full blur-[100px] pointer-events-none">
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-hero font-display text-brand-white">INVESTASI KESEHATANMU</h2>
                <p class="text-brand-silver mt-4">Tanpa biaya tersembunyi. Batalkan kapan saja.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 items-start">

                {{-- Plan 1 --}}
                <div
                    class="bg-brand-sidebar p-8 rounded-3xl border border-brand-borderSoft/10 hover:border-brand-borderSoft/30 transition-all duration-normal shadow-card">
                    <h3 class="text-xl font-bold font-heading text-brand-white">Starter</h3>
                    <div class="my-6">
                        <span class="text-4xl font-display font-bold text-brand-white">Rp 249k</span>
                        <span class="text-brand-muted text-sm">/bulan</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-brand-silver text-sm">
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-success"></i>
                            Akses Gym 09.00 - 15.00</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-success"></i>
                            Loker Standar</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-success"></i>
                            1x Sesi Coach Intro</li>
                    </ul>
                    <a href="#"
                        class="block w-full py-3 rounded-pill border border-brand-borderSoft/20 text-center text-brand-white font-bold hover:bg-brand-white hover:text-brand-nav transition-all">Pilih
                        Paket</a>
                </div>

                {{-- Plan 2 (Featured) --}}
                <div
                    class="relative p-8 rounded-3xl bg-brand-surface-200/5 border-2 border-gold-600 shadow-gold-glow transform md:-translate-y-4">
                    <div
                        class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-brand-gold px-4 py-1 rounded-pill text-xs font-bold text-brand-nav uppercase tracking-wider shadow-lg font-heading">
                        Paling Laris
                    </div>
                    <h3 class="text-xl font-bold font-heading text-brand-white">Elite Member</h3>
                    <div class="my-6">
                        <span class="text-5xl font-display font-bold text-transparent bg-clip-text bg-brand-gold">Rp
                            399k</span>
                        <span class="text-brand-muted text-sm">/bulan</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-brand-white text-sm">
                        <li class="flex items-center gap-3"><i data-lucide="check-circle-2"
                                class="w-5 h-5 text-gold-500"></i> <strong>Akses Unlimited 24/7</strong></li>
                        <li class="flex items-center gap-3"><i data-lucide="check-circle-2"
                                class="w-5 h-5 text-gold-500"></i> Semua Kelas Grup Gratis</li>
                        <li class="flex items-center gap-3"><i data-lucide="check-circle-2"
                                class="w-5 h-5 text-gold-500"></i> Loker VIP + Handuk</li>
                    </ul>
                    <a href="{{ route('register') }}"
                        class="block w-full py-4 rounded-pill bg-accent-gradient text-center text-brand-white font-bold shadow-btn-primary hover:shadow-btn-primary-hover hover:scale-102 transition-all duration-normal">
                        Gabung Sekarang
                    </a>
                </div>

                {{-- Plan 3 --}}
                <div
                    class="bg-brand-sidebar p-8 rounded-3xl border border-brand-borderSoft/10 hover:border-brand-borderSoft/30 transition-all duration-normal shadow-card">
                    <h3 class="text-xl font-bold font-heading text-brand-white">Tahunan</h3>
                    <div class="my-6">
                        <span class="text-4xl font-display font-bold text-brand-white">Rp 3.9jt</span>
                        <span class="text-brand-muted text-sm">/tahun</span>
                    </div>
                    <p class="text-xs text-gold-500 mb-4 font-semibold">Hemat 20% dibanding bulanan</p>
                    <ul class="space-y-4 mb-8 text-brand-silver text-sm">
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-success"></i>
                            Semua Fitur Elite</li>
                        <li class="flex items-center gap-3"><i data-lucide="check" class="w-5 h-5 text-success"></i>
                            2x Sesi Personal Trainer</li>
                    </ul>
                    <a href="#"
                        class="block w-full py-3 rounded-pill border border-brand-borderSoft/20 text-center text-brand-white font-bold hover:bg-brand-white hover:text-brand-nav transition-all">Pilih
                        Paket</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- CTA --}}
    {{-- ========================================================= --}}
    <section class="py-20 bg-brand-dark">
        <div class="container mx-auto px-6">
            {{-- Menggunakan brand-ember untuk background CTA (Merah Gold Gradient) --}}
            <div class="relative rounded-[2.5rem] overflow-hidden bg-brand-ember shadow-card-strong">
                <div
                    class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
                </div>

                <div class="relative z-10 px-8 py-20 text-center">
                    <h2 class="text-4xl md:text-display font-display font-bold text-brand-white mb-6">SIAP UNTUK
                        PERUBAHAN?</h2>
                    <p class="text-brand-white/90 text-lg mb-8 max-w-xl mx-auto">Jangan menunggu "waktu yang tepat".
                        Mulailah hari ini dan rasakan bedanya dalam 30 hari pertama.</p>

                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}"
                            class="px-10 py-4 bg-brand-white text-brand-nav font-heading font-bold rounded-pill hover:bg-brand-surface-50 transition shadow-xl hover:-translate-y-1">
                            Daftar Member
                        </a>
                        <a href="#"
                            class="px-10 py-4 bg-transparent border-2 border-brand-white text-brand-white font-heading font-bold rounded-pill hover:bg-brand-white hover:text-brand-nav transition">
                            Hubungi WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-guest.footer />

    <script>
        lucide.createIcons();
    </script>
</body>

</html>

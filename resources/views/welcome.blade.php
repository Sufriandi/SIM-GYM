<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BETA GYM – Build a Better You</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-brand-bg text-text-main font-sans">

    {{-- ========================================================= --}}
    {{-- NAVBAR --}}
    {{-- ========================================================= --}}
    <header class="bg-brand-nav text-brand-white shadow-header border-b border-brand-borderStrong">
        <div class="container flex items-center justify-between py-4">
            <div class="flex items-center gap-3">
                <img src="images/Logo.png" alt="BETA GYM" class="w-12 h-12">
                <div class="font-display text-2xl uppercase tracking-wide">BETA <span class="text-gold-500">GYM</span></div>
            </div>

            <nav class="hidden md:flex items-center gap-8 text-brand-white">
                <a href="#" class="hover:text-gold-500 transition-all">Program</a>
                <a href="#" class="hover:text-gold-500 transition-all">Fasilitas</a>
                <a href="#" class="hover:text-gold-500 transition-all">Membership</a>
            </nav>

            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-sm hover:text-gold-500 transition-all">Masuk</a>

                <button
                    class="px-5 py-2.5 rounded-pill text-sm font-semibold
                           text-brand-white bg-accent-500
                           shadow-btn-primary transition-all duration-normal ease-smooth
                           hover:bg-accent-600 hover:shadow-btn-primary-hover hover:-translate-y-0.5
                           active:scale-98 focus-visible:outline-none focus-visible:ring-2
                           focus-visible:ring-accent">
                    
                    <a href= "{{ route('register') }}">Daftar Member</a>
                </button>
            </div>
        </div>
    </header>


    {{-- ========================================================= --}}
    {{-- HERO SECTION --}}
    {{-- ========================================================= --}}
    <section class="relative bg-brand-sand py-20 overflow-hidden">

        {{-- Radial gold lighting --}}
        <div class="absolute inset-0 pointer-events-none bg-brand-radial-spot opacity-40"></div>

        <div class="container relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">

            {{-- HERO TEXT --}}
            <div>
                <div class="inline-block px-4 py-1 rounded-pill bg-brand-card border border-brand-borderSoft text-xs mb-4">
                    Buka setiap hari • 06.00 – 23.00
                </div>

                <h1 class="text-5xl md:text-6xl font-display leading-tight">
                    Bangun <span class="text-gold-600">Tubuh Kuat</span><br>
                    dan <span class="text-gold-700">Mental Tangguh</span>.
                </h1>

                <p class="mt-6 text-text-muted text-lg max-w-xl">
                    BETA GYM menggabungkan fasilitas premium, coaching terarah,
                    dan suasana latihan yang bikin kamu betah. Cocok untuk pemula sampai atlet.
                </p>

                <div class="flex gap-4 mt-8">
                    <button
                        class="px-7 py-3 text-sm font-semibold text-brand-white
                               bg-accent-gradient bg-[length:140%_140%] bg-center
                               rounded-pill shadow-btn-primary transition-all ease-smooth duration-normal
                               hover:bg-accent-600 hover:-translate-y-1 hover:shadow-btn-primary-hover
                               active:scale-98">
                        Lihat Paket Membership
                    </button>

                    <button
                        class="px-7 py-3 text-sm font-semibold
                               text-text-main bg-brand-cardSoft border border-brand-borderStrong
                               rounded-pill shadow-btn-soft transition-all ease-smooth
                               hover:bg-brand-shell hover:-translate-y-1 hover:shadow-card
                               active:scale-98">
                        Jadwalkan Tur Gym
                    </button>
                </div>

                {{-- Animated badge --}}
                <div class="mt-6 inline-block px-4 py-1 text-xs rounded-pill text-white
                            bg-accent-fire bg-[length:200%_200%] animate-gradient-move">
                    PROMO BULAN INI • Diskon 15%
                </div>
            </div>


            {{-- HERO VISUAL CARD --}}
            <div class="bg-brand-card border border-brand-borderSoft rounded-3xl shadow-card p-6">
                <h3 class="text-sm text-text-muted uppercase tracking-wide mb-2">
                    Progress Member
                </h3>

                <h2 class="text-xl font-semibold mb-6">Emir van den</h2>

                <div class="mb-4">
                    <p class="text-sm font-medium">Daya tahan</p>
                    <div class="w-full h-2 bg-brand-surface-100 rounded-pill mt-1">
                        <div class="h-full bg-gold-500 rounded-pill" style="width: 75%"></div>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm font-medium">Kehadiran latihan</p>
                    <div class="w-full h-2 bg-brand-surface-100 rounded-pill mt-1">
                        <div class="h-full bg-gold-700 rounded-pill" style="width: 60%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mt-6 text-center">
                    <div class="bg-brand-cardSoft border border-brand-borderSoft rounded-xl py-3">
                        <div class="text-sm font-semibold">Trainer</div>
                        <div class="text-xs text-text-muted">Tersertifikasi</div>
                    </div>
                    <div class="bg-brand-cardSoft border border-brand-borderSoft rounded-xl py-3">
                        <div class="text-sm font-semibold">Kelas</div>
                        <div class="text-xs text-text-muted">HIIT • Yoga</div>
                    </div>
                    <div class="bg-brand-cardSoft border border-brand-borderSoft rounded-xl py-3">
                        <div class="text-sm font-semibold">Aplikasi</div>
                        <div class="text-xs text-text-muted">Tracking</div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- MEMBERSHIP PACKAGES --}}
    {{-- ========================================================= --}}
    <section class="py-24 bg-brand-bg">
        <div class="container">

            <h2 class="text-4xl font-display mb-3">Pilih Membership yang Pas</h2>
            <p class="text-text-muted mb-12 max-w-2xl">Semua paket memberi akses penuh ke seluruh fasilitas dan aplikasi BETA GYM.</p>

            <div class="grid md:grid-cols-3 gap-10">

                {{-- Basic --}}
                <div class="bg-brand-card border border-brand-borderSoft rounded-2xl shadow-card p-7 hover:shadow-card-strong hover:-translate-y-1 transition-all">
                    <h3 class="font-semibold text-text-main">Basic</h3>
                    <p class="mt-4 text-2xl font-display">Rp249.000</p>
                    <p class="text-sm text-text-muted mb-6">per bulan</p>

                    <ul class="text-sm space-y-2 mb-8">
                        <li>• Akses gym saat jam senggang</li>
                        <li>• Program latihan dasar</li>
                        <li>• Tracking progres</li>
                    </ul>

                    <button class="w-full px-5 py-3 rounded-pill text-sm font-semibold border border-brand-borderStrong shadow-btn-soft transition-all hover:bg-brand-shell">
                        Pilih Paket Basic
                    </button>
                </div>

                {{-- Elite (highlight) --}}
                <div class="bg-brand-cardSoft border-3 border-brand-borderStrong rounded-3xl shadow-card-strong p-8 bg-brand-radial-spot bg-no-repeat hover:scale-102 transition-all">
                    <div class="absolute -mt-6 ml-2 inline-block bg-brand-card px-4 py-1 text-xs rounded-pill border border-brand-borderSoft shadow">
                        Paling Populer
                    </div>

                    <h3 class="font-semibold text-text-main mt-4">Elite</h3>
                    <p class="mt-4 text-3xl font-display">Rp399.000</p>
                    <p class="text-sm text-text-muted mb-6">per bulan</p>

                    <ul class="text-sm space-y-2 mb-8">
                        <li>• Akses full 06.00 – 23.00</li>
                        <li>• Konsultasi coach</li>
                        <li>• Kelas grup gratis</li>
                        <li>• Prioritas booking</li>
                    </ul>

                    <button class="w-full px-5 py-3 rounded-pill text-sm font-semibold text-white bg-accent-gradient shadow-btn-primary transition-all hover:-translate-y-1 hover:shadow-btn-primary-hover">
                        Pilih Paket Elite
                    </button>
                </div>

                {{-- Annual --}}
                <div class="bg-brand-card border border-brand-borderSoft rounded-2xl shadow-card p-7">
                    <h3 class="font-semibold text-text-main">Annual Commit</h3>
                    <p class="mt-4 text-2xl font-display">Rp3.999.000</p>
                    <p class="text-sm text-text-muted mb-6">per tahun</p>

                    <ul class="text-sm space-y-2 mb-8">
                        <li>• Hemat hingga 20%</li>
                        <li>• 2 sesi personal trainer gratis</li>
                        <li>• Merchandise eksklusif</li>
                    </ul>

                    <button class="w-full px-5 py-3 rounded-pill text-sm font-semibold border border-brand-borderStrong hover:bg-brand-shell">
                        Pilih Paket Tahunan
                    </button>
                </div>

            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}
    <footer class="bg-brand-footer text-brand-white py-12">
        <div class="container grid md:grid-cols-3 gap-10">

            <div>
                <h4 class="font-display text-xl mb-3">BETA GYM</h4>
                <p class="text-sm text-brand-silver max-w-xs">
                    Build a Better You. Gym premium dengan fasilitas lengkap dan suasana yang bikin betah.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-semibold mb-3">Menu</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-gold-500 transition-all">Program</a></li>
                    <li><a href="#" class="hover:text-gold-500 transition-all">Fasilitas</a></li>
                    <li><a href="#" class="hover:text-gold-500 transition-all">Membership</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold mb-3">Kontak</h4>
                <ul class="space-y-2 text-sm">
                    <li>Email: info@betagym.com</li>
                    <li>Telp: +62 812 3456 7890</li>
                    <li>Alamat: Jakarta Selatan</li>
                </ul>
            </div>

        </div>

        <div class="mt-8 text-center text-xs text-brand-silver">
            © 2025 BETA GYM. All rights reserved.
        </div>
    </footer>

</body>
</html>

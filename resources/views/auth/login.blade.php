{{-- resources/views/auth/login.blade.php --}}

<x-layouts.auth :title="'Login | BETA GYM'">

    <div
        class="w-full max-w-5xl bg-brand-card rounded-3xl overflow-hidden border border-brand-borderSoft shadow-card flex flex-col lg:flex-row">

        {{-- KIRI: HERO / BRAND (TIDAK DIUBAH) --}}
        <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
            <img src="{{ asset('images/gym-bg.jpg') }}" alt="BETA GYM Background"
                class="absolute inset-0 w-full h-full object-cover scale-105">
            <div class="absolute inset-0 bg-brand-shell/80 mix-blend-multiply"></div>

            <div
                class="relative h-full flex flex-col items-center justify-center px-10 py-8 text-center text-brand-black">
                <div class="mb-6">
                    <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM Logo"
                        class="h-20 w-auto mx-auto drop-shadow-[0_0_25px_rgba(212,167,87,0.8)]">
                </div>

                <p class="text-xs tracking-[0.25em] uppercase text-brand-steel mb-2">
                    Build a Better You
                </p>

                <h1 class="text-4xl font-heading tracking-tight text-text-main">
                    BETA <span class="text-gold-600">GYM</span>
                </h1>

                <p class="mt-3 text-sm text-text-muted max-w-sm">
                    Akses premium untuk melacak progres, izin latihan, dan membership dalam satu aplikasi.
                </p>

                <div class="mt-8 grid grid-cols-3 gap-6 text-left text-sm text-brand-gunmetal w-full max-w-md">
                    <div>
                        <div class="text-2xl font-heading text-gold-600 leading-none">500+</div>
                        <div class="text-[11px] text-brand-steel uppercase mt-1">Member aktif</div>
                    </div>
                    <div class="border-x border-brand-borderSoft px-4">
                        <div class="text-2xl font-heading text-gold-600 leading-none">20+</div>
                        <div class="text-[11px] text-brand-steel uppercase mt-1">Kelas / minggu</div>
                    </div>
                    <div>
                        <div class="text-2xl font-heading text-gold-600 leading-none">06–23</div>
                        <div class="text-[11px] text-brand-steel uppercase mt-1">Jam operasional</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: FORM LOGIN --}}
        <div class="w-full lg:w-1/2 bg-brand-bg px-8 py-10 lg:px-10 flex flex-col justify-center">
            <div class="lg:hidden flex justify-center mb-6">
                <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM Logo"
                    class="h-16 w-auto drop-shadow-[0_0_20px_rgba(212,167,87,0.6)]">
            </div>

            <div class="mb-6 text-center lg:text-left">
                <h2 class="text-2xl font-heading text-text-main tracking-tight">
                    Selamat Datang Kembali
                </h2>
                <p class="mt-1 text-sm text-text-muted">
                    Masuk ke akun BETA GYM untuk mengelola membership dan latihanmu.
                </p>
            </div>

            @if (session('status'))
                <x-ui.toast type="success" class="mb-4">
                    {{ session('status') }}
                </x-ui.toast>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- LOGIN FIELD (username / email / no_hp) --}}
                <div>
                    <x-ui.label for="login">Username / Email / No HP</x-ui.label>

                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-4 h-4 text-text-muted"></i>
                        </span>

                        <input id="login" name="login" type="text" required autocomplete="username"
                            value="{{ old('login') }}"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card 
                                   text-sm text-text-main placeholder:text-text-muted/70 
                                   focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500
                                   transition-shadow"
                            placeholder="username / email / nomor HP">
                    </div>

                    @error('login')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div>
                    <x-ui.label for="password">Password</x-ui.label>

                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-text-muted"></i>
                        </span>

                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card 
                                   text-sm text-text-main placeholder:text-text-muted/70
                                   focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500
                                   transition-shadow"
                            placeholder="••••••••">
                    </div>

                    @error('password')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember + Forgot --}}
                <div class="flex items-center justify-between gap-3 text-xs">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="rounded border-brand-borderSoft text-gold-600 focus:ring-gold-500 bg-brand-card">
                        <span class="text-text-muted hover:text-text-main transition-colors">
                            Ingat saya
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-gold-700 hover:text-gold-500 font-medium">
                            Lupa password?
                        </a>
                    @endif
                </div>

                {{-- Tombol Login --}}
                <x-ui.button-primary type="submit" class="w-full justify-center mt-2">
                    <span>Masuk</span>
                    <i data-lucide="log-in" class="w-4 h-4 ml-2"></i>
                </x-ui.button-primary>

                {{-- Register --}}
                <p class="text-xs text-center text-text-muted mt-4">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-gold-700 hover:text-gold-500 font-semibold">
                        Daftar sekarang
                    </a>
                </p>

            </form>
        </div>
    </div>

</x-layouts.auth>

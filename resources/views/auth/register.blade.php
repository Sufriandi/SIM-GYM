{{-- resources/views/auth/register.blade.php --}}

<x-layouts.auth :title="'Register | BETA GYM'">

    <div
        class="w-full max-w-5xl bg-brand-card rounded-3xl overflow-hidden border border-brand-borderSoft shadow-card flex flex-col lg:flex-row">

        {{-- KIRI: HERO --}}
        <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
            <img src="{{ asset('images/gym-bg.jpg') }}" alt="BETA GYM Membership"
                class="absolute inset-0 w-full h-full object-cover scale-105">
            <div class="absolute inset-0 bg-brand-shell/85 mix-blend-multiply"></div>

            <div
                class="relative h-full flex flex-col items-center justify-center px-10 py-8 text-center text-brand-black">
                <div class="mb-5">
                    <img src="{{ asset('images/logo.png') }}" alt="BETA GYM Logo"
                        class="h-20 w-auto mx-auto drop-shadow-[0_0_25px_rgba(212,167,87,0.8)]">
                </div>

                <h1 class="text-3xl font-heading text-text-main">
                    Mulai Perjalanan Fitness-mu
                </h1>
                <p class="mt-3 text-sm text-text-muted max-w-sm">
                    Daftar sebagai member BETA GYM dan nikmati progres tracking, izin latihan online, dan fitur lainnya.
                </p>

                <div class="mt-8 grid grid-cols-2 gap-4 text-left text-xs text-brand-gunmetal w-full max-w-md">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-success-soft text-success">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <p class="font-semibold text-text-main">Program terarah</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Personal trainer & kelas grup</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gold-500/15 text-gold-700">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <p class="font-semibold text-text-main">Aplikasi member</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Booking kelas & track progres</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: FORM REGISTER --}}
        <div class="w-full lg:w-1/2 bg-brand-bg px-8 py-10 lg:px-10 flex flex-col justify-center">
            <div class="lg:hidden flex justify-center mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="BETA GYM Logo"
                    class="h-16 w-auto drop-shadow-[0_0_20px_rgba(212,167,87,0.6)]">
            </div>

            <div class="mb-6 text-center lg:text-left">
                <h2 class="text-2xl font-heading text-text-main tracking-tight">
                    Daftar Akun Baru
                </h2>
                <p class="mt-1 text-sm text-text-muted">
                    Isi data di bawah untuk membuat akun member BETA GYM.
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Nama --}}
                <div>
                    <x-ui.label for="name">Nama Lengkap</x-ui.label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-4 h-4 text-text-muted"></i>
                        </span>
                        <input id="name" name="name" type="text" autocomplete="name" required
                            value="{{ old('name') }}"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card
                       text-sm text-text-main placeholder:text-text-muted/70
                       focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500 transition-shadow"
                            placeholder="Nama lengkap">
                    </div>
                    @error('name')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Username --}}
                <div>
                    <x-ui.label for="username">Username</x-ui.label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="badge-check" class="w-4 h-4 text-text-muted"></i>
                        </span>
                        <input id="username" name="username" type="text" autocomplete="username" required
                            value="{{ old('username') }}"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card
                       text-sm text-text-main placeholder:text-text-muted/70
                       focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500 transition-shadow"
                            placeholder="Username untuk login">
                    </div>
                    @error('username')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (Opsional) --}}
                <div>
                    <x-ui.label for="email">Email (Opsional)</x-ui.label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-4 h-4 text-text-muted"></i>
                        </span>
                        <input id="email" name="email" type="email" autocomplete="email"
                            value="{{ old('email') }}"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card
                       text-sm text-text-main placeholder:text-text-muted/70
                       focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500 transition-shadow"
                            placeholder="nama@email.com">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nomor HP (Opsional) --}}
                <div>
                    <x-ui.label for="no_hp">Nomor HP (Opsional)</x-ui.label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="phone" class="w-4 h-4 text-text-muted"></i>
                        </span>
                        <input id="no_hp" name="no_hp" type="text" autocomplete="tel"
                            value="{{ old('no_hp') }}"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card
                       text-sm text-text-main placeholder:text-text-muted/70
                       focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500 transition-shadow"
                            placeholder="08xxxxxxxxxx">
                    </div>
                    @error('no_hp')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <x-ui.label for="password">Password</x-ui.label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-text-muted"></i>
                        </span>
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card
                       text-sm text-text-main placeholder:text-text-muted/70
                       focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500 transition-shadow"
                            placeholder="Minimal 8 karakter">
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <x-ui.label for="password_confirmation">Konfirmasi Password</x-ui.label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock-keyhole" class="w-4 h-4 text-text-muted"></i>
                        </span>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            autocomplete="new-password" required
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card
                       text-sm text-text-main placeholder:text-text-muted/70
                       focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500 transition-shadow"
                            placeholder="Ulangi password">
                    </div>
                </div>

                <x-ui.button-primary type="submit" class="w-full justify-center mt-2">
                    <span>Buat Akun</span>
                    <i data-lucide="user-plus" class="w-4 h-4 ml-2"></i>
                </x-ui.button-primary>

                <p class="text-xs text-center text-text-muted mt-4">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-gold-700 hover:text-gold-500 font-semibold">
                        Masuk di sini
                    </a>
                </p>

            </form>

        </div>
    </div>

</x-layouts.auth>

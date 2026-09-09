{{-- resources/views/auth/reset-password.blade.php --}}

<x-layouts.auth :title="'Atur Ulang Password | BETA GYM'">

    <div
        class="w-full max-w-4xl bg-brand-card rounded-3xl overflow-hidden border border-brand-borderSoft shadow-card flex flex-col lg:flex-row">

        {{-- KIRI: HERO / BRAND --}}
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
                    Buat kata sandi baru yang aman untuk akun BETA GYM Anda.
                </p>
            </div>
        </div>

        {{-- KANAN: FORM RESET PASSWORD --}}
        <div class="w-full lg:w-1/2 bg-brand-bg px-8 py-10 lg:px-10 flex flex-col justify-center">
            <div class="lg:hidden flex justify-center mb-6">
                <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM Logo"
                    class="h-16 w-auto drop-shadow-[0_0_20px_rgba(212,167,87,0.6)]">
            </div>

            <div class="mb-6 text-center lg:text-left">
                <h2 class="text-2xl font-heading text-text-main tracking-tight">
                    Atur Ulang Password
                </h2>
                <p class="mt-1 text-sm text-text-muted">
                    Silakan masukkan kata sandi baru untuk akun Anda.
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- EMAIL --}}
                <div>
                    <x-ui.label for="email">Alamat Email</x-ui.label>

                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-4 h-4 text-text-muted"></i>
                        </span>

                        <input id="email" name="email" type="email" required autofocus
                            value="{{ old('email', $request->email) }}"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card 
                                   text-sm text-text-main placeholder:text-text-muted/70 
                                   focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500
                                   transition-shadow"
                            placeholder="nama@email.com">
                    </div>

                    @error('email')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PASSWORD BARU --}}
                <div>
                    <x-ui.label for="password">Password Baru</x-ui.label>

                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-text-muted"></i>
                        </span>

                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card 
                                   text-sm text-text-main placeholder:text-text-muted/70 
                                   focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500
                                   transition-shadow"
                            placeholder="Minimal 8 karakter">
                    </div>

                    @error('password')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KONFIRMASI PASSWORD --}}
                <div>
                    <x-ui.label for="password_confirmation">Konfirmasi Password Baru</x-ui.label>

                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-text-muted"></i>
                        </span>

                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-brand-borderSoft bg-brand-card 
                                   text-sm text-text-main placeholder:text-text-muted/70 
                                   focus:outline-none focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500
                                   transition-shadow"
                            placeholder="Ulangi password baru">
                    </div>

                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Reset --}}
                <x-ui.button-primary type="submit" class="w-full justify-center mt-2">
                    <span>Simpan Password Baru</span>
                    <i data-lucide="check" class="w-4 h-4 ml-2"></i>
                </x-ui.button-primary>

                {{-- Kembali ke Login --}}
                <div class="pt-2 text-center">
                    <a href="{{ route('login') }}" class="text-xs text-gold-700 hover:text-gold-500 font-semibold inline-flex items-center gap-1.5 transition-colors">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Batal & Kembali ke Login</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.auth>

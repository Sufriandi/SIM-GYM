@extends('layouts.guest')

@section('content')

{{-- CONTAINER SPLIT SCREEN --}}
<div class="bg-dark-card rounded-premium overflow-hidden border-2 border-gold-900 flex max-w-6xl mx-auto my-8">

    {{-- KOLOM KIRI: FORM REGISTER (60% Lebar) --}}
    <div class="w-full lg:w-3/5 p-12 bg-gradient-to-br from-dark-card to-dark-surface">

        {{-- Header --}}
        <div class="text-center mb-8">
            {{-- Logo Mobile --}}
            <div class="lg:hidden mb-4">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="BETA GYM Logo" 
                     class="h-16 w-auto mx-auto"
                     style="filter: drop-shadow(0 0 15px rgba(200, 168, 112, 0.6));">
            </div>
            
            <h2 class="text-3xl font-heading text-gold mb-2 tracking-wide">
                DAFTAR SEKARANG
            </h2>
            <div class="w-16 h-1 bg-accent mx-auto mb-3"></div>
            <p class="text-text-secondary text-sm">Mulai perjalanan fitness Anda bersama kami</p>
        </div>

        {{-- Form Register --}}
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- Name Input --}}
            <div>
                <label for="name" class="block font-semibold text-sm text-gold mb-2 uppercase tracking-wide">
                    Nama Lengkap
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold-700" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input id="name" 
                           class="w-full pl-10 pr-4 py-3 rounded-gym bg-dark-background border-2 border-gold-900 text-text-primary placeholder-text-secondary focus:border-gold focus:ring-2 focus:ring-gold/50 transition duration-200" 
                           type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus 
                           autocomplete="name" 
                           placeholder="John Doe" />
                </div>
                @error('name')
                    <p class="mt-2 text-xs text-accent">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email Input --}}
            <div>
                <label for="email" class="block font-semibold text-sm text-gold mb-2 uppercase tracking-wide">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold-700" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                    </div>
                    <input id="email" 
                           class="w-full pl-10 pr-4 py-3 rounded-gym bg-dark-background border-2 border-gold-900 text-text-primary placeholder-text-secondary focus:border-gold focus:ring-2 focus:ring-gold/50 transition duration-200" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="username" 
                           placeholder="nama@email.com" />
                </div>
                @error('email')
                    <p class="mt-2 text-xs text-accent">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Password Input --}}
                <div>
                    <label for="password" class="block font-semibold text-sm text-gold mb-2 uppercase tracking-wide">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold-700" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="password" 
                               class="w-full pl-10 pr-4 py-3 rounded-gym bg-dark-background border-2 border-gold-900 text-text-primary placeholder-text-secondary focus:border-gold focus:ring-2 focus:ring-gold/50 transition duration-200" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="new-password"
                               placeholder="••••••••" />
                    </div>
                    @error('password')
                        <p class="mt-2 text-xs text-accent">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password Input --}}
                <div>
                    <label for="password_confirmation" class="block font-semibold text-sm text-gold mb-2 uppercase tracking-wide">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold-700" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="password_confirmation" 
                               class="w-full pl-10 pr-4 py-3 rounded-gym bg-dark-background border-2 border-gold-900 text-text-primary placeholder-text-secondary focus:border-gold focus:ring-2 focus:ring-gold/50 transition duration-200" 
                               type="password" 
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               placeholder="••••••••" />
                    </div>
                    @error('password_confirmation')
                        <p class="mt-2 text-xs text-accent">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Terms & Conditions --}}
            <div class="flex items-start">
                <input id="terms" 
                       type="checkbox" 
                       class="mt-1 rounded border-gold-800 text-gold focus:ring-gold focus:ring-offset-dark-background bg-dark-background" 
                       name="terms"
                       required>
                <label for="terms" class="ml-3 text-sm text-text-secondary leading-relaxed">
                    Saya setuju dengan 
                    <a href="#" class="text-gold hover:text-gold-400 underline">Syarat & Ketentuan</a> 
                    dan 
                    <a href="#" class="text-gold hover:text-gold-400 underline">Kebijakan Privasi</a>
                </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit" 
                    class="w-full bg-gold hover:bg-gold-600 text-primary-900 font-bold py-4 rounded-gym uppercase transition duration-300 shadow-lg border-2 border-gold hover:border-gold-400 tracking-widest transform hover:scale-[1.02] flex items-center justify-center group">
                <span>Daftar Sekarang</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform group-hover:translate-x-1 transition duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>

            {{-- Divider --}}
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gold-900"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-dark-card text-text-secondary uppercase tracking-wider">Atau daftar dengan</span>
                </div>
            </div>

            {{-- Social Register --}}
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="flex items-center justify-center px-4 py-3 border-2 border-gold-900 rounded-gym text-text-secondary hover:border-gold hover:text-gold transition duration-200">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span class="text-sm font-semibold">Google</span>
                </button>
                
                <button type="button" class="flex items-center justify-center px-4 py-3 border-2 border-gold-900 rounded-gym text-text-secondary hover:border-gold hover:text-gold transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="text-sm font-semibold">Facebook</span>
                </button>
            </div>
        </form>

        {{-- Login Link --}}
        <div class="text-center mt-8 pt-6 border-t border-gold-900">
            <p class="text-sm text-text-secondary">
                Sudah punya akun? 
                <a href="{{ route('login') }}" 
                   class="text-gold hover:text-gold-400 font-semibold underline transition duration-200">
                    Masuk Sekarang
                </a>
            </p>
        </div>
    </div>

    {{-- KOLOM KANAN: BENEFITS (40% Lebar) --}}
    <div class="w-2/5 hidden lg:block relative overflow-hidden bg-gradient-to-br from-primary to-primary-900">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(200,168,112,.3) 35px, rgba(200,168,112,.3) 70px);"></div>
        </div>
        
        {{-- Content --}}
        <div class="relative h-full flex flex-col justify-center p-10 text-center">
            {{-- Logo --}}
            <div class="mb-8">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="BETA GYM Logo" 
                     class="h-20 w-auto mx-auto"
                     style="filter: drop-shadow(0 0 20px rgba(200, 168, 112, 0.8));">
            </div>
            
            {{-- Title --}}
            <h3 class="text-3xl font-heading text-gold mb-2 tracking-wider">
                BERGABUNGLAH
            </h3>
            <h4 class="text-2xl font-heading text-accent mb-6 tracking-wider">
                DENGAN KAMI
            </h4>
            
            <div class="w-16 h-1 bg-gold mx-auto mb-8"></div>
            
            {{-- Benefits List --}}
            <div class="space-y-6 text-left">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 bg-gold rounded-gym flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-900" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h5 class="text-gold font-semibold mb-1">Akses 24/7</h5>
                        <p class="text-text-secondary text-sm">Latihan kapan saja sesuai jadwal Anda</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 bg-gold rounded-gym flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-900" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h5 class="text-gold font-semibold mb-1">Personal Trainer</h5>
                        <p class="text-text-secondary text-sm">Bimbingan dari trainer profesional</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 bg-gold rounded-gym flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-900" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h5 class="text-gold font-semibold mb-1">Peralatan Modern</h5>
                        <p class="text-text-secondary text-sm">Fasilitas lengkap dan terawat</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 bg-gold rounded-gym flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-900" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h5 class="text-gold font-semibold mb-1">Komunitas Solid</h5>
                        <p class="text-text-secondary text-sm">Bergabung dengan member lainnya</p>
                    </div>
                </div>
            </div>
            
            {{-- CTA Text --}}
            <div class="mt-8 p-4 bg-gold-900/30 rounded-gym border border-gold-800">
                <p class="text-gold text-sm font-semibold">
                    💪 Mulai transformasi tubuh Anda hari ini!
                </p>
            </div>
        </div>
    </div>
    
</div>

@endsection
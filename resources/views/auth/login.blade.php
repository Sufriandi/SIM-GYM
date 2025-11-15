@extends('layouts.guest')

@section('content')

{{-- CONTAINER SPLIT SCREEN --}}
<div class="bg-dark-card rounded-premium overflow-hidden border-2 border-gold-900 flex max-w-5xl mx-auto my-8">

    {{-- KOLOM KIRI: GAMBAR GYM (50% Lebar) --}}
    <div class="w-1/2 hidden lg:block relative overflow-hidden">
        <img 
            src="{{ asset('images/gym-bg.jpg') }}" 
            alt="BETA GYM Background" 
            class="object-cover w-full h-full transform hover:scale-105 transition duration-700"
        >
        {{-- Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-br from-primary/80 via-primary/60 to-transparent"></div>
        
        {{-- Content Overlay --}}
        <div class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
            {{-- Logo --}}
            <div class="mb-6">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="BETA GYM Logo" 
                     class="h-24 w-auto mx-auto"
                     style="filter: drop-shadow(0 0 20px rgba(200, 168, 112, 0.8));">
            </div>
            
            {{-- Title --}}
            <h1 class="text-6xl font-heading text-gold mb-4 tracking-wider animate-pulse-gold">
                BETA
            </h1>
            <h2 class="text-5xl font-heading text-accent mb-6 tracking-wider">
                GYM
            </h2>
            
            {{-- Tagline --}}
            <div class="w-24 h-1 bg-gold mb-6"></div>
            <p class="text-text-primary text-lg font-semibold uppercase tracking-widest">
                Transform Your Body
            </p>
            <p class="text-text-secondary text-sm mt-2 max-w-sm">
                Join the elite community of champions. Your fitness journey starts here.
            </p>
            
            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-6 mt-8 w-full max-w-md">
                <div class="text-center">
                    <div class="text-3xl font-heading text-gold">500+</div>
                    <div class="text-xs text-text-secondary uppercase">Members</div>
                </div>
                <div class="text-center border-x border-gold-800">
                    <div class="text-3xl font-heading text-gold">50+</div>
                    <div class="text-xs text-text-secondary uppercase">Trainers</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-heading text-gold">24/7</div>
                    <div class="text-xs text-text-secondary uppercase">Access</div>
                </div>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: FORM LOGIN (50% Lebar) --}}
    <div class="w-full lg:w-1/2 p-12 bg-gradient-to-br from-dark-card to-dark-surface">

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
                SELAMAT DATANG
            </h2>
            <div class="w-16 h-1 bg-accent mx-auto mb-3"></div>
            <p class="text-text-secondary text-sm">Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        {{-- Form Login --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

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
                           autofocus 
                           autocomplete="username" 
                           placeholder="nama@email.com" />
                </div>
                @error('email')
                    <p class="mt-2 text-xs text-accent">{{ $message }}</p>
                @enderror
            </div>

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
                           autocomplete="current-password"
                           placeholder="••••••••" />
                </div>
                @error('password')
                    <p class="mt-2 text-xs text-accent">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember & Forgot --}}
            <div class="flex justify-between items-center">
                <label for="remember_me" class="flex items-center group cursor-pointer">
                    <input id="remember_me" 
                           type="checkbox" 
                           class="rounded border-gold-800 text-gold focus:ring-gold focus:ring-offset-dark-background bg-dark-background" 
                           name="remember">
                    <span class="ml-2 text-sm text-text-secondary group-hover:text-gold transition duration-200">
                        Ingat Saya
                    </span>
                </label>
                
                @if (Route::has('password.request'))
                    <a class="text-sm text-gold-700 hover:text-gold transition duration-200" 
                       href="{{ route('password.request') }}">
                        Lupa Password?
                    </a>
                @endif
            </div>

            {{-- Submit Button --}}
            <button type="submit" 
                    class="w-full bg-gold hover:bg-gold-600 text-primary-900 font-bold py-4 rounded-gym uppercase transition duration-300 shadow-lg border-2 border-gold hover:border-gold-400 tracking-widest transform hover:scale-[1.02] flex items-center justify-center group">
                <span>Masuk</span>
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
                    <span class="px-4 bg-dark-card text-text-secondary uppercase tracking-wider">Atau</span>
                </div>
            </div>

            {{-- Social Login (Optional) --}}
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

        {{-- Register Link --}}
        <div class="text-center mt-8 pt-6 border-t border-gold-900">
            <p class="text-sm text-text-secondary">
                Belum punya akun? 
                <a href="{{ route('register') }}" 
                   class="text-gold hover:text-gold-400 font-semibold underline transition duration-200">
                    Daftar Sekarang
                </a>
            </p>
        </div>
    </div>
    
</div>

@endsection
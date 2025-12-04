{{-- resources/views/member/dashboard.blade.php --}}

<x-layouts.member
    pageTitle="Dashboard Member"
    pageSubtitle="Overview Membership"
>
    @php
        // --- LOGIC PHP ---
        $user = auth()->user();
        $member = $member ?? null;
        
        // Greeting Time
        $h = now()->hour;
        $greeting = $h < 11 ? 'Selamat Pagi' : ($h < 15 ? 'Selamat Siang' : ($h < 18 ? 'Selamat Sore' : 'Selamat Malam'));

        // Logic Membership
        $tglAkhir = $member?->tanggal_akhir ? \Carbon\Carbon::parse($member->tanggal_akhir) : null;
        $sisaHari = $tglAkhir ? now()->diffInDays($tglAkhir, false) : 0;
        $sisaHari = $sisaHari > 0 ? intval($sisaHari) : 0;
        $isActive = $sisaHari > 0;
        
        // ID Format
        $rawId = substr(md5($user->id), 0, 16);
        $formattedId = implode(' ', str_split($rawId, 4));

        // Stats Logic
        $izinStats = $izinStats ?? ['pending' => 0, 'total' => 0];
        
        // Progress Bar Calculation (Max 30 days scale)
        $progressPercent = min(($sisaHari/30)*100, 100);
    @endphp

    <div class="space-y-8 pb-12">

        {{-- 
            =============================================
            1. HEADER SECTION (Gaya Stardust - Favorit User)
            =============================================
        --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1a1a1a] via-[#2a2a2a] to-[#1a1a1a] p-8 lg:p-10 border border-white/5 shadow-2xl">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-5"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-gold-500/10 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>
            
            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gold-500/20 border border-gold-500/30 backdrop-blur-sm mb-4">
                        <span class="w-2 h-2 rounded-full bg-gold-400 animate-pulse"></span>
                        <p class="text-gold-400 font-bold uppercase tracking-wider text-xs">Member Dashboard</p>
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-heading font-black text-white tracking-tight leading-tight">
                        {{ $greeting }},
                        <span class="block mt-2 bg-gradient-to-r from-gold-400 to-gold-600 bg-clip-text text-transparent">
                            {{ explode(' ', $member->nama ?? $user->name)[0] }}!
                        </span>
                    </h1>
                    <p class="text-base text-white/60 mt-3 max-w-lg">
                        Pantau status membership dan aktivitas latihan Anda hari ini.
                    </p>
                </div>
                
                {{-- Date Widget --}}
                <div class="hidden lg:flex items-center gap-4 bg-white/5 border border-white/10 px-6 py-3 rounded-2xl backdrop-blur-sm">
                    <div class="p-3 bg-gold-500/20 rounded-xl text-gold-500">
                        <i data-lucide="calendar-days" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-white/50 font-bold uppercase">Hari Ini</p>
                        <p class="text-lg font-bold text-white">{{ now()->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
            =============================================
            2. MEMBERSHIP PANEL (SPLIT 40:60)
            =============================================
        --}}
        <div class="rounded-3xl bg-[#151515] border border-white/10 shadow-2xl overflow-hidden relative group">
            {{-- Background Effects --}}
            <div class="absolute inset-0 bg-gradient-to-r from-gold-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            
            <div class="grid grid-cols-1 lg:grid-cols-5">
                
                {{-- 
                    LEFT SIDE (40%): VISUAL CARD
                --}}
                <div class="lg:col-span-2 p-6 lg:p-8 bg-[#1a1a1a] border-b lg:border-b-0 lg:border-r border-white/5 flex items-center justify-center relative overflow-hidden">
                    {{-- Decor --}}
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-gold-600 to-transparent"></div>
                    
                    {{-- THE CARD --}}
                    <div class="relative w-full aspect-[1.586/1] max-w-[400px] perspective-1000 group/card">
                        <div class="relative h-full w-full rounded-2xl bg-[#0a0a0a] border border-white/10 shadow-2xl overflow-hidden transform transition-transform duration-500 group-hover/card:scale-105 group-hover/card:rotate-1">
                            
                            {{-- Card Texture --}}
                            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-20"></div>
                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-black/80"></div>
                            
                            {{-- Card Content --}}
                            <div class="relative z-10 h-full p-6 flex flex-col justify-between text-white">
                                <div class="flex justify-between items-start">
                                    <span class="font-heading font-black italic text-xl tracking-wide text-gold-500">BETA GYM</span>
                                    <i data-lucide="wifi" class="w-5 h-5 text-white/30 rotate-90"></i>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="w-10 h-7 rounded bg-gradient-to-br from-yellow-200 to-yellow-600 shadow-md border border-white/20"></div>
                                    <p class="font-mono text-lg md:text-xl font-bold tracking-widest text-white/90 drop-shadow-md">
                                        {{ $formattedId }}
                                    </p>
                                </div>

                                <div class="flex justify-between items-end text-[10px] uppercase tracking-wider font-bold text-white/50">
                                    <div>
                                        <span class="block mb-0.5">Holder</span>
                                        <span class="text-white text-xs">{{ $member->nama ?? $user->name }}</span>
                                    </div>
                                    <div>
                                        <span class="block mb-0.5">Exp</span>
                                        <span class="text-gold-500 text-xs">{{ $tglAkhir ? $tglAkhir->format('m/y') : 'XX/XX' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 
                    RIGHT SIDE (60%): INFO & ACTIONS (No Popup)
                --}}
                <div class="lg:col-span-3 p-6 lg:p-10 flex flex-col justify-center relative">
                    
                    {{-- Status Header --}}
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <p class="text-xs font-bold text-white/40 uppercase tracking-widest mb-1">Status Keanggotaan</p>
                            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                                {{ $isActive ? 'Membership Aktif' : 'Membership Berakhir' }}
                                @if($isActive)
                                    <i data-lucide="check-circle-2" class="w-6 h-6 text-green-500"></i>
                                @else
                                    <i data-lucide="x-circle" class="w-6 h-6 text-red-500"></i>
                                @endif
                            </h2>
                        </div>
                        {{-- Status Badge --}}
                        <div class="px-4 py-2 rounded-xl border {{ $isActive ? 'bg-green-500/10 border-green-500/20 text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-400' }}">
                            <span class="text-xs font-black uppercase tracking-wider">
                                {{ $isActive ? '● ACTIVE' : '● EXPIRED' }}
                            </span>
                        </div>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <p class="text-xs font-bold text-white/40 uppercase tracking-widest mb-2">Sisa Masa Aktif</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-heading font-black {{ $isActive ? 'text-white' : 'text-red-500' }}">
                                    {{ $sisaHari }}
                                </span>
                                <span class="text-sm font-bold text-white/40">Hari</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white/40 uppercase tracking-widest mb-2">Berlaku Sampai</p>
                            <p class="text-xl font-bold text-gold-500 font-mono">
                                {{ $tglAkhir ? $tglAkhir->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Progress Bar & Action --}}
                    <div class="space-y-6">
                        {{-- Progress Bar --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-white/40">
                                <span>Durasi Terpakai</span>
                                <span>{{ $isActive ? 'Aktif' : 'Non-Aktif' }}</span>
                            </div>
                            <div class="w-full h-3 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 {{ $isActive ? 'bg-gradient-to-r from-gold-600 to-gold-400' : 'bg-red-500/50' }}" 
                                     style="width: {{ $isActive ? $progressPercent : 100 }}%">
                                </div>
                            </div>
                        </div>

                        {{-- ACTION BUTTONS (INLINE - NO POPUP) --}}
                        <div class="flex gap-4 pt-2">
                            @if(!$isActive)
                                {{-- Tombol Perpanjang (Jika Expired) --}}
                                <a href="#" class="flex-1 py-3.5 px-6 bg-red-600 hover:bg-red-500 text-white rounded-xl font-bold uppercase tracking-wide text-sm flex items-center justify-center gap-2 shadow-lg shadow-red-900/20 transition-all hover:scale-[1.02]">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    Perpanjang Sekarang
                                </a>
                            @else
                                {{-- Tombol Info (Jika Aktif) --}}
                                <button class="flex-1 py-3.5 px-6 bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-colors">
                                    <i data-lucide="file-text" class="w-4 h-4 text-gold-500"></i>
                                    Riwayat Tagihan
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- 
            =============================================
            3. BOTTOM SECTION: QUICK ACTIONS & STATS
            =============================================
        --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            {{-- Quick Action: Izin --}}
            <div class="lg:col-span-2 relative overflow-hidden rounded-3xl bg-[#1a1a1a] border border-white/10 p-1 group">
                <div class="absolute inset-0 bg-gradient-to-r from-gold-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative h-full bg-[#151515] rounded-[22px] p-6 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500">
                            <i data-lucide="calendar-off" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Berhalangan Hadir?</h3>
                            <p class="text-sm text-white/50">Bekukan kehadiran Anda sementara waktu.</p>
                        </div>
                    </div>
                    <a href="{{ route('member.izin_latihan.create') }}" class="w-full sm:w-auto">
                        <button class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gold-500 hover:bg-gold-400 text-black font-bold text-sm flex items-center justify-center gap-2 transition-transform active:scale-95">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Buat Izin
                        </button>
                    </a>
                </div>
            </div>

            {{-- Pending Stats --}}
            <div class="rounded-3xl bg-[#1a1a1a] border border-white/10 p-6 flex flex-col justify-between hover:border-gold-500/30 transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-white/40 uppercase tracking-widest">Izin Pending</p>
                        <h3 class="text-4xl font-heading font-black text-white mt-2">{{ $izinStats['pending'] }}</h3>
                    </div>
                    <div class="p-2 rounded-lg bg-white/5">
                        <i data-lucide="file-clock" class="w-5 h-5 text-gold-500"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/5 flex justify-between items-center">
                    <span class="text-xs text-white/40">Total diajukan: {{ $izinStats['total'] }}</span>
                    <a href="{{ route('member.izin_latihan.index') }}" class="text-xs font-bold text-gold-500 hover:text-gold-400 flex items-center gap-1">
                        Detail <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</x-layouts.member>
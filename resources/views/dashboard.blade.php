@php
    $pageTitle = $pageTitle ?? 'Dashboard Pengunjung';

    $highlights = $highlights ?? [
        ['label' => 'Akses Gym', 'value' => '24/7'],
        ['label' => 'Alat Pro', 'value' => '50+'],
        ['label' => 'Coach', 'value' => '15+'],
        ['label' => 'Rating', 'value' => '4.9/5'],
    ];

    $nextClasses = $nextClasses ?? [
        ['name' => 'HIIT Cardio', 'day' => 'Senin', 'time' => '18:30', 'level' => 'Intense'],
        ['name' => 'Power Yoga', 'day' => 'Rabu', 'time' => '19:00', 'level' => 'Beginner'],
        ['name' => 'Heavy Lifting', 'day' => 'Jumat', 'time' => '17:30', 'level' => 'Strength'],
    ];
@endphp

<x-layouts.guest :title="$pageTitle">

    <section class="py-16 bg-brand-dark">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row items-end justify-between gap-6">
                <div>
                    <p class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Visitor Dashboard</p>
                    <h1 class="text-4xl md:text-hero font-display font-bold text-brand-white mt-2">{{ $pageTitle }}</h1>
                    <p class="text-brand-silver/80 mt-3 max-w-2xl">
                        Ringkasan untuk pengunjung: highlight fasilitas, kelas terdekat, dan rute cepat untuk join.
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ url('/contact') }}" class="px-6 py-3 rounded-pill border border-brand-borderSoft/20 text-brand-white font-bold hover:bg-brand-white/10 transition">
                        Tanya Admin
                    </a>
                    @php
                        $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/register');
                    @endphp
                    <a href="{{ $registerUrl }}" class="px-6 py-3 rounded-pill bg-gold-500 text-brand-nav font-bold hover:bg-gold-400 transition shadow-gold-glow">
                        Daftar Member
                    </a>
                </div>
            </div>

            <div class="grid md:grid-cols-4 gap-4 mt-10">
                @foreach($highlights as $h)
                    <div class="p-6 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/10 shadow-card">
                        <p class="text-xs uppercase tracking-widest text-brand-silver/70 font-bold">{{ $h['label'] }}</p>
                        <p class="text-3xl font-display font-bold text-brand-white mt-2">{{ $h['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid lg:grid-cols-3 gap-6 mt-10">
                <div class="lg:col-span-2 p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/10 shadow-card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold font-heading text-brand-white">Kelas Terdekat</h2>
                        <a href="{{ url('/schedule') }}" class="text-gold-500 font-bold hover:text-gold-400 transition inline-flex items-center gap-2">
                            Lihat Jadwal <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>

                    <div class="mt-6 grid md:grid-cols-3 gap-4">
                        @foreach($nextClasses as $c)
                            <div class="p-5 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10">
                                <p class="text-[11px] font-bold tracking-widest uppercase text-gold-500">{{ $c['level'] }}</p>
                                <p class="text-lg font-bold text-brand-white mt-1">{{ $c['name'] }}</p>
                                <p class="text-sm text-brand-silver/80 mt-2">{{ $c['day'] }} • {{ $c['time'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/10 shadow-card">
                    <h2 class="text-2xl font-bold font-heading text-brand-white">Quick Actions</h2>
                    <p class="text-sm text-brand-silver/80 mt-2">Arahkan pengunjung ke langkah yang benar.</p>

                    <div class="mt-6 space-y-3">
                        <a href="{{ url('/coaches') }}" class="block p-4 rounded-2xl border border-brand-borderSoft/10 hover:bg-brand-white/5 transition">
                            <p class="font-bold text-brand-white">Pilih Coach</p>
                            <p class="text-xs text-brand-silver/70 mt-1">Lihat spesialisasi & paket.</p>
                        </a>

                        <a href="{{ url('/marketplace') }}" class="block p-4 rounded-2xl border border-brand-borderSoft/10 hover:bg-brand-white/5 transition">
                            <p class="font-bold text-brand-white">Cek Produk</p>
                            <p class="text-xs text-brand-silver/70 mt-1">Suplemen, apparel, equipment.</p>
                        </a>

                        <a href="{{ url('/contact') }}" class="block p-4 rounded-2xl bg-gold-500 text-brand-nav font-bold text-center hover:bg-gold-400 transition">
                            Hubungi Admin
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

</x-layouts.guest>

{{-- resources/views/member/dashboard.blade.php --}}
{{-- Pastikan route ini dipanggil hanya untuk role member --}}

<x-layouts.member
    pageTitle="Dashboard Member"
    pageSubtitle="Ringkasan membership & aktivitas latihan Anda."
>
    <div class="space-y-8">

        {{-- HEADER SECTION --}}
        <x-ui.section-header
            title="Selamat datang, {{ auth()->user()->name ?? 'Member' }}"
            subtitle="Kelola membership, pantau izin latihan, dan ikuti progres Anda di BETA GYM."
        />

        {{-- GRID ATAS: Membership + Ringkasan --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- KARTU MEMBERSHIP --}}
            <x-ui.card class="lg:col-span-2" title="Status Membership" subtitle="Informasi masa aktif dan tipe membership Anda.">
                @php
                    /** @var \App\Models\Member|null $member */
                    $member = $member ?? null;
                    $tanggalAkhir = $member?->tanggal_akhir ? \Carbon\Carbon::parse($member->tanggal_akhir) : null;
                    $hariTersisa = $tanggalAkhir ? now()->diffInDays($tanggalAkhir, false) : null;
                @endphp

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm text-text-muted">Nama Member</p>
                        <p class="text-lg font-semibold text-text-main">
                            {{ $member->nama ?? auth()->user()->name ?? 'Member' }}
                        </p>

                        <p class="mt-3 text-sm text-text-muted">Masa aktif hingga</p>
                        <p class="text-xl font-heading text-gold-700">
                            @if($tanggalAkhir)
                                {{ $tanggalAkhir->translatedFormat('d F Y') }}
                            @else
                                <span class="text-text-muted italic">Belum ada data membership</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex gap-4 md:gap-6">
                        {{-- Hari tersisa --}}
                        <div class="px-4 py-3 rounded-2xl bg-brand-shell/60 border border-brand-borderSoft text-center min-w-[120px]">
                            <p class="text-[11px] uppercase tracking-wide text-text-muted">Hari Tersisa</p>
                            <p class="mt-1 text-2xl font-heading text-text-main">
                                @if(!is_null($hariTersisa))
                                    {{ $hariTersisa > 0 ? $hariTersisa : 0 }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>

                        {{-- Status --}}
                        <div class="px-4 py-3 rounded-2xl bg-brand-shell/60 border border-brand-borderSoft text-center min-w-[120px]">
                            <p class="text-[11px] uppercase tracking-wide text-text-muted">Status</p>
                            <p class="mt-1 text-sm font-semibold">
                                @if(is_null($tanggalAkhir))
                                    <span class="text-text-muted">Tidak aktif</span>
                                @elseif($hariTersisa > 0)
                                    <span class="text-success">Aktif</span>
                                @else
                                    <span class="text-danger">Berakhir</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- RINGKASAN IZIN --}}
            @php
                $izinStats = $izinStats ?? [
                    'total'     => $totalIzin ?? 0,
                    'pending'   => $izinPending ?? 0,
                    'disetujui' => $izinDisetujui ?? 0,
                ];
            @endphp

            <x-ui.card title="Ringkasan Izin Latihan" subtitle="Status pengajuan izin latihan Anda.">
                <div class="space-y-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-text-muted">Total Pengajuan</span>
                        <span class="font-semibold text-text-main">{{ $izinStats['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-text-muted">Menunggu Persetujuan</span>
                        <span class="font-semibold text-warning">{{ $izinStats['pending'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-text-muted">Disetujui</span>
                        <span class="font-semibold text-success">{{ $izinStats['disetujui'] }}</span>
                    </div>

                    <div class="pt-3">
                        <a href="{{ route('member.izin_latihan.index') }}">
                            <x-ui.button-primary class="w-full justify-center">
                                Kelola Izin Latihan
                                <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                            </x-ui.button-primary>
                        </a>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- GRID BAWAH: Quick Action Izin / Info --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-ui.card
                title="Ajukan Izin Latihan"
                subtitle="Tidak bisa datang latihan? Ajukan izin dengan mudah dan sistematis."
            >
                <p class="text-sm text-text-muted mb-4">
                    Pengajuan izin akan menghentikan sementara kehadiran Anda dan dapat digunakan sebagai dasar perpanjangan masa membership sesuai persetujuan Admin.
                </p>

                <ul class="space-y-2 text-sm text-text-muted mb-5">
                    <li class="flex gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-success mt-0.5"></i>
                        <span>Isi tanggal mulai dan selesai izin latihan.</span>
                    </li>
                    <li class="flex gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-success mt-0.5"></i>
                        <span>Sertakan alasan yang jelas, dan bukti jika diperlukan.</span>
                    </li>
                    <li class="flex gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-success mt-0.5"></i>
                        <span>Tunggu persetujuan Admin. Status bisa dipantau di halaman Izin Latihan.</span>
                    </li>
                </ul>

                <a href="{{ route('member.izin_latihan.create') }}">
                    <x-ui.button-primary class="inline-flex items-center">
                        Ajukan Izin Sekarang
                        <i data-lucide="calendar-plus" class="w-4 h-4 ml-2"></i>
                    </x-ui.button-primary>
                </a>
            </x-ui.card>

            {{-- Kartu Info Ringan / Rules --}}
            <x-ui.card
                title="Aturan Izin & Membership"
                subtitle="Beberapa poin penting yang perlu Anda perhatikan."
            >
                <ul class="space-y-2 text-xs text-text-muted leading-relaxed">
                    <li class="flex gap-2">
                        <span class="mt-[3px] w-1.5 h-1.5 rounded-full bg-gold-500"></span>
                        <span>Izin sebaiknya diajukan sebelum periode izin dimulai, kecuali keadaan darurat.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="mt-[3px] w-1.5 h-1.5 rounded-full bg-gold-500"></span>
                        <span>Perpanjangan masa membership tergantung kebijakan dan persetujuan Admin.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="mt-[3px] w-1.5 h-1.5 rounded-full bg-gold-500"></span>
                        <span>Gunakan fitur ini secara bijak untuk menjaga kredibilitas dan keaktifan akun member Anda.</span>
                    </li>
                </ul>
            </x-ui.card>
        </div>
    </div>
</x-layouts.member>

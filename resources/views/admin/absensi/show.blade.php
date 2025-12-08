{{-- resources/views/admin/absensi/sesi/show.blade.php --}}

@php
    use Illuminate\Support\Carbon;

    $pageTitle = $pageTitle ?? 'Detail Sesi Absensi';

    $tanggal = $sesi->tanggal ? Carbon::parse($sesi->tanggal) : null;
    $mulai   = $sesi->jam_mulai ? Carbon::parse($sesi->jam_mulai) : null;
    $selesai = $sesi->jam_selesai ? Carbon::parse($sesi->jam_selesai) : null;

    $totalHadir = $sesi->kehadiranMembers->count();
@endphp

<x-layouts.admin
    :page-title="$pageTitle"
    page-subtitle="Lihat detail sesi dan data kehadiran member."
>
    <div class="space-y-4">

        {{-- HEADER + BACK --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Ringkasan sesi absensi dan kehadiran member."
        />

        <x-ui.back-button
            href="{{ route('admin.absensi.sesi.index') }}"
            text="Kembali ke daftar sesi"
            class="mb-2"
        />

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <x-ui.toast type="success" class="mb-2">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        @if (session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- CARD UTAMA: INFO SESI + QR --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- KOLOM KIRI: INFO SESI + STATISTIK --}}
            <div class="lg:col-span-2 space-y-4">
                <x-ui.card class="border border-brand-borderSoft">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-brand-borderSoft">
                        <div>
                            <h2 class="text-lg font-semibold text-text-main">
                                {{ $sesi->nama_sesi }}
                            </h2>
                            <p class="text-xs text-text-muted mt-0.5">
                                Kode sesi: <span class="font-mono text-xs">{{ $sesi->kode_qr }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($sesi->status === 'aktif')
                                <x-ui.badge variant="success">Sesi Aktif</x-ui.badge>
                            @else
                                <x-ui.badge variant="secondary">Sesi Ditutup</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <div class="pt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-[11px] text-text-muted uppercase tracking-wider mb-1">Tanggal</p>
                            <p class="text-text-main font-medium">
                                {{ $tanggal ? $tanggal->translatedFormat('d M Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] text-text-muted uppercase tracking-wider mb-1">Waktu</p>
                            <p class="text-text-main font-medium">
                                @if($mulai || $selesai)
                                    {{ $mulai?->format('H:i') ?? '?' }} — {{ $selesai?->format('H:i') ?? '?' }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] text-text-muted uppercase tracking-wider mb-1">Total Kehadiran</p>
                            <p class="text-text-main font-semibold">
                                {{ $totalHadir }} member
                            </p>
                        </div>
                    </div>

                    @if($sesi->catatan_admin)
                        <div class="mt-4 pt-3 border-t border-brand-borderSoft/60">
                            <p class="text-[11px] text-text-muted uppercase tracking-wider mb-1">
                                Catatan Admin
                            </p>
                            <p class="text-sm text-text-main leading-relaxed">
                                {{ $sesi->catatan_admin }}
                            </p>
                        </div>
                    @endif

                    {{-- AKSI ADMIN --}}
                    <div class="mt-4 pt-4 border-t border-brand-borderSoft flex flex-wrap gap-3 items-center justify-between">
                        <p class="text-[11px] text-text-muted">
                            Anda dapat menutup sesi setelah jam absensi berakhir.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @if($sesi->status === 'aktif')
                                <form
                                    action="{{ route('admin.absensi.sesi.close', $sesi->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Tutup sesi absensi ini? Data kehadiran tetap tersimpan.');"
                                >
                                    @csrf
                                    <x-ui.button-secondary type="submit" class="inline-flex items-center gap-1">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                        Tutup Sesi
                                    </x-ui.button-secondary>
                                </form>
                            @else
                                <form
                                    action="{{ route('admin.absensi.sesi.reopen', $sesi->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Buka kembali sesi ini? QR bisa digunakan lagi oleh member.');"
                                >
                                    @csrf
                                    <x-ui.button-secondary type="submit" class="inline-flex items-center gap-1">
                                        <i data-lucide="unlock" class="w-4 h-4"></i>
                                        Buka Kembali
                                    </x-ui.button-secondary>
                                </form>
                            @endif

                            <form
                                action="{{ route('admin.absensi.sesi.regenerate-qr', $sesi->id) }}"
                                method="POST"
                                onsubmit="return confirm('Regenerasi kode QR akan mengganti link sebelumnya. Lanjutkan?');"
                            >
                                @csrf
                                <x-ui.button-ghost type="submit" class="inline-flex items-center gap-1 text-xs">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    Regenerasi QR
                                </x-ui.button-ghost>
                            </form>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- KOLOM KANAN: QR CODE --}}
            <div>
                <x-ui.card class="border border-brand-borderSoft">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-text-main">
                            Kode QR Sesi Absensi
                        </h3>
                        <p class="text-[11px] text-text-muted mt-0.5">
                            Tampilkan kode ini di area gym. Member melakukan scan menggunakan aplikasi SIM-GYM.
                        </p>
                    </div>

                    <div class="flex items-center justify-center py-4">
                        @if(!empty($qrSvg ?? null))
                            <div class="bg-white p-3 rounded-2xl border border-brand-borderSoft shadow-inner">
                                {!! $qrSvg !!}
                            </div>
                        @else
                            <div class="w-48 h-48 rounded-2xl border-2 border-dashed border-brand-borderSoft flex flex-col items-center justify-center text-text-muted text-xs text-center px-4">
                                <i data-lucide="qr-code" class="w-8 h-8 mb-2"></i>
                                QR code belum tersedia. Pastikan controller mengirim variabel <code>$qrSvg</code>.
                            </div>
                        @endif
                    </div>

                    {{-- LINK SCAN MANUAL --}}
                    <div class="mt-4">
                        <p class="text-[11px] text-text-muted uppercase tracking-wider mb-1">
                            Link Scan Manual
                        </p>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                readonly
                                value="{{ $scanUrl ?? route('member.kehadiran.scan', $sesi->kode_qr) }}"
                                class="flex-1 text-xs rounded-xl border border-brand-borderSoft bg-brand-shell text-text-main px-3 py-2 font-mono"
                            >
                            <button
                                type="button"
                                onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $scanUrl ?? route('member.kehadiran.scan', $sesi->kode_qr) }}')"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-brand-borderSoft bg-brand-card text-xs text-text-main hover:bg-brand-shell transition-colors"
                            >
                                <i data-lucide="copy" class="w-4 h-4 mr-1"></i>
                                Salin
                            </button>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        {{-- TABEL KEHADIRAN MEMBER --}}
        <x-ui.card class="overflow-hidden border border-brand-borderSoft">
            <div class="px-4 sm:px-6 py-4 border-b border-brand-borderSoft">
                <h3 class="text-base sm:text-lg font-semibold text-text-main">
                    Daftar Kehadiran Member
                </h3>
                <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                    Riwayat scan QR untuk sesi ini.
                </p>
            </div>

            <div class="px-2 sm:px-3 pb-2 pt-1 overflow-x-auto custom-scrollbar">
                <table class="w-full table-auto text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-shell/60">
                            <th class="pl-3 pr-2 py-3 text-left text-xs font-semibold tracking-wide text-text-muted/90 uppercase whitespace-nowrap">
                                Member
                            </th>
                            <th class="px-3 py-3 text-center text-xs font-semibold tracking-wide text-text-muted/90 uppercase whitespace-nowrap">
                                Waktu Scan
                            </th>
                            <th class="px-3 py-3 text-center text-xs font-semibold tracking-wide text-text-muted/90 uppercase whitespace-nowrap">
                                Status
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold tracking-wide text-text-muted/90 uppercase">
                                Informasi Device
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse($kehadiran as $row)
                            @php
                                $scanAt = $row->waktu_scan ? Carbon::parse($row->waktu_scan) : null;
                                $user   = optional($row->member)->user;
                            @endphp
                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                <td class="pl-3 pr-2 py-3 align-top">
                                    <div class="text-text-main font-medium">
                                        {{ $row->member->nama ?? ($user->name ?? '-') }}
                                    </div>
                                    <div class="text-[11px] text-text-muted mt-0.5">
                                        Username:
                                        <span class="font-mono text-[11px]">
                                            {{ $row->member->username ?? ($user->username ?? '-') }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-center align-top whitespace-nowrap text-text-main">
                                    {{ $scanAt ? $scanAt->translatedFormat('d M Y, H:i') : '-' }}
                                </td>

                                <td class="px-3 py-3 text-center align-top whitespace-nowrap">
                                    @if($row->status === 'hadir')
                                        <x-ui.badge variant="success">Hadir</x-ui.badge>
                                    @elseif($row->status === 'terlambat')
                                        <x-ui.badge variant="warning">Terlambat</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="secondary">{{ ucfirst($row->status ?? 'N/A') }}</x-ui.badge>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-left align-top text-xs text-text-muted">
                                    {{ $row->device_info ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-text-muted text-sm italic">
                                    Belum ada member yang melakukan absensi pada sesi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION (jika pakai paginate) --}}
            @if(method_exists($kehadiran, 'links'))
                <div class="mt-4 px-4 pb-4">
                    {{ $kehadiran->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- SCROLLBAR STYLING LITE --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 148, 148, 0.6);
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(120, 120, 120, 0.9);
        }
    </style>
</x-layouts.admin>

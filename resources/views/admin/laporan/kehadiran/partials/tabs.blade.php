@php
    use Illuminate\Support\Facades\Route;

    $active = $active ?? 'ringkasan';
    $qs = $qs ?? http_build_query(request()->query());

    $baseUrl = Route::has('admin.laporan.kehadiran.index')
        ? route('admin.laporan.kehadiran.index')
        : url()->to('/admin/laporan/kehadiran');

    $absensiUrl = Route::has('admin.laporan.kehadiran.absensi')
        ? route('admin.laporan.kehadiran.absensi')
        : rtrim($baseUrl, '/') . '/absensi';

    $kompensasiUrl = Route::has('admin.laporan.kehadiran.kompensasi')
        ? route('admin.laporan.kehadiran.kompensasi')
        : rtrim($baseUrl, '/') . '/kompensasi';

    $auditUrl = Route::has('admin.laporan.kehadiran.audit')
        ? route('admin.laporan.kehadiran.audit')
        : rtrim($baseUrl, '/') . '/audit';

    $clsActive = "px-4 py-2 text-xs font-bold rounded-md bg-gold-50 text-gold-700 border border-gold-200 cursor-default whitespace-nowrap";
    $clsLink   = "px-4 py-2 text-xs font-medium rounded-md text-text-muted hover:text-text-main hover:bg-gray-50 transition-all whitespace-nowrap";
@endphp

<div class="inline-flex bg-white border border-brand-borderSoft rounded-lg p-1 shadow-sm overflow-x-auto custom-scrollbar">
    <div class="flex items-center gap-1">
        @if($active === 'ringkasan')
            <span class="{{ $clsActive }}">Ringkasan</span>
        @else
            <a href="{{ $baseUrl }}{{ $qs ? ('?'.$qs) : '' }}" class="{{ $clsLink }}">Ringkasan</a>
        @endif

        @if($active === 'absensi')
            <span class="{{ $clsActive }}">Absensi</span>
        @else
            <a href="{{ $absensiUrl }}{{ $qs ? ('?'.$qs) : '' }}" class="{{ $clsLink }}">Absensi</a>
        @endif

        @if($active === 'kompensasi')
            <span class="{{ $clsActive }}">Kompensasi</span>
        @else
            <a href="{{ $kompensasiUrl }}{{ $qs ? ('?'.$qs) : '' }}" class="{{ $clsLink }}">Kompensasi</a>
        @endif

        {{-- @if($active === 'audit')
            <span class="{{ $clsActive }}">Audit Data</span>
        @else
            <a href="{{ $auditUrl }}{{ $qs ? ('?'.$qs) : '' }}" class="{{ $clsLink }}">Audit Data</a>
        @endif --}}
    </div>
</div>

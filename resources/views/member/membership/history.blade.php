{{-- resources/views/member/membership/history.blade.php --}}

@php
    use Illuminate\Support\Facades\Route;

    $pageTitle    = 'Riwayat Membership';
    $pageSubtitle = 'Daftar membership yang terhubung dengan akun Anda.';

    $rows = $rows ?? collect();
    $paginator = $paginator ?? null;

    $isRowsPaginator = $rows instanceof \Illuminate\Pagination\AbstractPaginator;
    $items = $isRowsPaginator ? $rows : collect($rows);

    $startNo = $isRowsPaginator ? (($rows->currentPage() - 1) * $rows->perPage()) : 0;

    $dashboardUrl = Route::has('member.dashboard') ? route('member.dashboard') : null;
    $paketUrl     = Route::has('member.membership.index') ? route('member.membership.index') : null;

    $badgeMeta = function (string $key) {
        return match ($key) {
            'active'   => ['class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'dot' => 'bg-emerald-500', 'label' => 'Aktif'],
            'upcoming' => ['class' => 'bg-sky-50 text-sky-700 border border-sky-200',             'dot' => 'bg-sky-500',     'label' => 'Belum Mulai'],
            'expired'  => ['class' => 'bg-red-50 text-red-700 border border-red-200',             'dot' => 'bg-red-500',     'label' => 'Berakhir'],
            default    => ['class' => 'bg-brand-surface-50/50 text-brand-textSoft border border-brand-borderSoft/60', 'dot' => 'bg-brand-borderSoft', 'label' => '—'],
        };
    };
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-6xl mx-auto space-y-4 pb-10">

        {{-- FLASH MESSAGE --}}
        @if(session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        @if(session('success'))
            <x-ui.toast type="success" class="mb-2">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        {{-- HEADER --}}
        <x-ui.section-header :title="$pageTitle" :subtitle="$pageSubtitle" />

        {{-- ACTIONS: bawah header, atas garis emas --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                @if($dashboardUrl)
                    <a href="{{ $dashboardUrl }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-gold-600 hover:text-gold-500 whitespace-nowrap">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Kembali ke Dashboard
                    </a>
                @endif
            </div>

            <div class="sm:ml-auto">
                @if($paketUrl)
    <a href="{{ $paketUrl }}" class="inline-block">
        <x-ui.button-primary type="button">
            <span class="inline-flex items-center gap-2">
                Lihat Paket Membership
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </span>
        </x-ui.button-primary>
    </a>
@endif

            </div>
        </div>

        {{-- GARIS EMAS --}}
        <hr class="border-t border-gold-500/30">

        {{-- CARD --}}
        <x-ui.card class="overflow-hidden border border-brand-borderSoft">

            <div class="px-6 md:px-8 py-5 md:py-6 border-b border-brand-borderSoft/50">
                <h3 class="text-xl font-heading font-semibold text-brand-text">
                    Riwayat Membership
                </h3>
                <p class="text-sm text-brand-textSoft mt-1">
                    Daftar membership yang pernah tercatat untuk akun Anda.
                </p>
            </div>

            {{-- DESKTOP --}}
            <div class="hidden md:block overflow-x-auto" data-animate-scope>
                <table class="min-w-full text-sm table-fixed">
                    <thead>
                        <tr class="bg-brand-surface-75 text-brand-textSoft uppercase text-[11px] tracking-wider">
                            <th class="w-16 px-6 md:px-8 py-3 text-left font-semibold whitespace-nowrap">No</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Paket / Periode</th>
                            <th class="w-[420px] px-4 py-3 text-left font-semibold whitespace-nowrap">Progress</th>
                            <th class="w-40 px-4 py-3 text-center font-semibold whitespace-nowrap">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/60">
                        @if($items->isEmpty())
                            <tr>
                                <td colspan="4" class="px-6 md:px-8 py-10 text-center text-sm text-brand-textSoft">
                                    Belum ada data riwayat membership.
                                </td>
                            </tr>
                        @else
                            @foreach($items as $i => $row)
                                @php
                                    $trx   = is_array($row) ? ($row['trx'] ?? null) : $row;
                                    $mulai = is_array($row) ? ($row['mulai'] ?? null) : null;
                                    $akhir = is_array($row) ? ($row['akhir'] ?? null) : null;

                                    $statusKey = (string) (is_array($row) ? ($row['status_key'] ?? 'unknown') : 'unknown');
                                    $meta = $badgeMeta($statusKey);

                                    $paketNama = $trx?->paket?->nama ?? $trx?->paket?->tipe ?? 'Paket Membership';
                                    $nota      = $trx?->no_nota ?? null;

                                    $used   = (int) (is_array($row) ? ($row['hari_terpakai'] ?? 0) : 0);
                                    $total  = (int) (is_array($row) ? ($row['total_durasi'] ?? 0) : 0);

                                    $remain = (int) (is_array($row) ? ($row['sisa_hari'] ?? max(0, $total - $used)) : max(0, $total - $used));
                                    $pct    = (int) (is_array($row) ? ($row['percent'] ?? ($total > 0 ? round(($used / $total) * 100) : 0)) : ($total > 0 ? round(($used / $total) * 100) : 0));
                                    $pct    = max(0, min(100, $pct));

                                    $periode = ($mulai && $akhir)
                                        ? ($mulai->format('d M Y') . ' - ' . $akhir->format('d M Y'))
                                        : '-';
                                @endphp

                                <tr class="hover:bg-brand-surface-50/60 transition-colors">
                                    <td class="px-6 md:px-8 py-4 align-middle text-brand-textSoft">
                                        {{ $startNo + $i + 1 }}
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <div class="font-semibold text-brand-text whitespace-nowrap truncate max-w-[520px]">
                                            {{ $paketNama }}
                                        </div>

                                        <div class="text-[11px] text-brand-textSoft mt-1 whitespace-nowrap">
                                            Periode: <span class="font-semibold text-brand-text">{{ $periode }}</span>
                                        </div>

                                        @if($nota)
                                            <div class="text-[11px] text-brand-textSoft mt-0.5 whitespace-nowrap">
                                                Nota: <span class="font-semibold text-brand-text">{{ $nota }}</span>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <div class="flex flex-col gap-2">
                                            <div class="flex items-center justify-between">
                                                <div class="text-brand-text font-semibold whitespace-nowrap">
                                                    {{ $used }}/{{ $total ?: 0 }} hari
                                                    <span class="text-brand-textSoft">•</span>
                                                    Sisa:
                                                    <span class="font-semibold text-brand-text" data-counter data-count-from="0" data-count-to="{{ $remain }}">{{ $remain }}</span>
                                                    hari
                                                </div>

                                                <div class="text-[11px] text-brand-textSoft whitespace-nowrap">
                                                    <span class="font-semibold text-brand-text" data-counter data-count-from="0" data-count-to="{{ $pct }}">{{ $pct }}</span>%
                                                </div>
                                            </div>

                                            <div class="w-full h-2.5 rounded-full bg-brand-card border border-brand-borderSoft/70 overflow-hidden">
                                                {{-- width awal tetap pct (fallback). JS akan prime: opacity 0 + width 0 -> animate ke pct --}}
                                                <div class="h-full rounded-full bg-gradient-to-r from-gold-600 via-gold-400 to-emerald-400"
                                                     data-progress-bar data-progress-to="{{ $pct }}"
                                                     style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-middle text-center">
                                        <div class="flex items-center justify-center">
                                            <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-[11px] font-semibold whitespace-nowrap {{ $meta['class'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $meta['dot'] }}"></span>
                                                {{ $meta['label'] }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- MOBILE --}}
            <div class="md:hidden px-4 py-4" data-animate-scope>
                @if($items->count() === 0)
                    <div class="px-2 py-10 text-center text-sm text-brand-textSoft">
                        Belum ada data riwayat membership.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($items as $i => $row)
                            @php
                                $trx   = is_array($row) ? ($row['trx'] ?? null) : $row;
                                $mulai = is_array($row) ? ($row['mulai'] ?? null) : null;
                                $akhir = is_array($row) ? ($row['akhir'] ?? null) : null;

                                $statusKey = (string) (is_array($row) ? ($row['status_key'] ?? 'unknown') : 'unknown');
                                $meta = $badgeMeta($statusKey);

                                $paketNama = $trx?->paket?->nama ?? $trx?->paket?->tipe ?? 'Paket Membership';
                                $nota      = $trx?->no_nota ?? null;

                                $used   = (int) (is_array($row) ? ($row['hari_terpakai'] ?? 0) : 0);
                                $total  = (int) (is_array($row) ? ($row['total_durasi'] ?? 0) : 0);

                                $remain = (int) (is_array($row) ? ($row['sisa_hari'] ?? max(0, $total - $used)) : max(0, $total - $used));
                                $pct    = (int) (is_array($row) ? ($row['percent'] ?? ($total > 0 ? round(($used / $total) * 100) : 0)) : ($total > 0 ? round(($used / $total) * 100) : 0));
                                $pct    = max(0, min(100, $pct));

                                $periode = ($mulai && $akhir)
                                    ? ($mulai->format('d M Y') . ' - ' . $akhir->format('d M Y'))
                                    : '-';
                            @endphp

                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-surface-50/35 px-4 py-4 hover:bg-brand-surface-50/60 transition-colors">
                                <div class="grid grid-cols-[36px_1fr_auto] items-start gap-3">
                                    <div class="w-9 h-9 rounded-full bg-brand-shell border border-brand-borderSoft flex items-center justify-center shrink-0 mt-0.5">
                                        <span class="text-[12px] font-semibold text-text-muted">
                                            {{ $startNo + $i + 1 }}
                                        </span>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-brand-text truncate">
                                            {{ $paketNama }}
                                        </div>

                                        <div class="mt-1 text-xs text-brand-textSoft whitespace-nowrap">
                                            Periode: <span class="font-semibold text-brand-text">{{ $periode }}</span>
                                        </div>

                                        @if($nota)
                                            <div class="mt-1 text-xs text-brand-textSoft whitespace-nowrap">
                                                Nota: <span class="font-semibold text-brand-text">{{ $nota }}</span>
                                            </div>
                                        @endif

                                        <div class="mt-3">
                                            <div class="flex items-center justify-between text-xs text-brand-textSoft">
                                                <span class="whitespace-nowrap">
                                                    <span class="font-semibold text-brand-text">{{ $used }}</span>/{{ $total ?: 0 }} hari
                                                </span>

                                                <span class="whitespace-nowrap">
                                                    Sisa:
                                                    <span class="font-semibold text-brand-text" data-counter data-count-from="0" data-count-to="{{ $remain }}">{{ $remain }}</span>
                                                    hari
                                                </span>
                                            </div>

                                            <div class="mt-2 w-full h-2.5 rounded-full bg-brand-card border border-brand-borderSoft/70 overflow-hidden">
                                                <div class="h-full rounded-full bg-gradient-to-r from-gold-600 via-gold-400 to-emerald-400"
                                                     data-progress-bar data-progress-to="{{ $pct }}"
                                                     style="width: {{ $pct }}%"></div>
                                            </div>

                                            <div class="mt-2 text-[11px] text-brand-textSoft whitespace-nowrap">
                                                Progress:
                                                <span class="font-semibold text-brand-text" data-counter data-count-from="0" data-count-to="{{ $pct }}">{{ $pct }}</span>%
                                            </div>
                                        </div>
                                    </div>

                                    <div class="shrink-0 pt-0.5">
                                        <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-[11px] font-semibold whitespace-nowrap {{ $meta['class'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $meta['dot'] }}"></span>
                                            {{ $meta['label'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- PAGINATION --}}
            @if($isRowsPaginator && $rows->hasPages())
                <div class="px-6 md:px-8 py-4 border-t border-brand-borderSoft/50">
                    {{ $rows->links() }}
                </div>
            @elseif(($paginator instanceof \Illuminate\Pagination\AbstractPaginator) && $paginator->hasPages())
                <div class="px-6 md:px-8 py-4 border-t border-brand-borderSoft/50">
                    {{ $paginator->links() }}
                </div>
            @endif

        </x-ui.card>
    </div>

    @vite('resources/js/member/member_history.jsx')
</x-layouts.member>

{{-- resources/views/admin/izin_latihan/modals/approve_form.blade.php --}}
@props(['izin'])

@php
    $memberName = $izin->member->user?->name ?? '[Member dihapus]';
    $memberUsername = optional($izin->member?->user)->username;
@endphp

<div
    x-show="openApproveId === {{ $izin->id }}"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @keydown.escape.window="openApproveId = null"
    @click.self="openApproveId = null"
>
    <div class="relative w-full max-w-xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell overflow-hidden">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Setujui Izin</h2>
                <p class="text-sm text-text-main mt-0.5">
                    Member: <span class="font-semibold text-gold-600">
                        {{ $memberName }}{!! $memberUsername ? ' <span class="text-text-muted">(' . e($memberUsername) . ')</span>' : '' !!}
                    </span>
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openApproveId = null"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 py-5">
            {{-- Error validasi (kalau ada) --}}
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-danger/40 bg-danger-soft/30 px-4 py-3 text-sm text-danger">
                    <div class="font-semibold mb-1">Input tidak valid:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.izin_latihan.approve', $izin) }}" class="space-y-4">
                @csrf

                {{-- Jumlah hari disetujui --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wide text-text-main mb-1">
                        Jumlah Hari Disetujui
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="approved_days"
                            min="0"
                            max="{{ $izin->jumlah_hari }}"
                            required
                            value="{{ old('approved_days', $izin->jumlah_hari) }}"
                            class="w-full rounded-xl border bg-brand-shell text-xl font-bold text-text-main px-4 py-2.5
                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        >
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-text-muted">
                            Hari
                        </span>
                    </div>
                    <p class="text-[11px] text-text-muted mt-1">
                        Maksimal {{ $izin->jumlah_hari }} hari. Jika 0, izin disetujui tanpa perpanjangan membership.
                    </p>
                </div>

                {{-- Catatan admin --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wide text-text-main mb-1">
                        Catatan Admin <span class="font-normal normal-case text-text-muted">(opsional)</span>
                    </label>
                    <textarea
                        name="keterangan_admin"
                        rows="4"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-4 py-2.5
                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        placeholder="Tulis catatan persetujuan..."
                    >{{ old('keterangan_admin') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="pt-2 flex items-center justify-end gap-2">
                    <x-ui.button-secondary type="button" @click="openApproveId = null">
                        Batal
                    </x-ui.button-secondary>

                    <x-ui.button-primary type="submit">
                        Setujui
                    </x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- resources/views/member/izin_latihan/form.blade.php --}}

@php
    /** @var \Illuminate\Support\Collection|\App\Models\IzinLatihan[] $blockedRanges */
    $pageTitle = $pageTitle ?? 'Ajukan Izin Latihan';
@endphp

<x-layouts.member
    :pageTitle="$pageTitle"
    pageSubtitle="Isi form berikut dengan jujur dan lengkap."
>
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- HEADER + BACK --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Tanggal mulai, durasi, dan alasan izin akan digunakan sebagai dasar penilaian Admin."
        />

        <x-ui.back-button
            href="{{ route('member.izin_latihan.index') }}"
            text="Kembali ke Pengajuan Saat Ini"
        />

        <hr class="border-t border-brand-borderSoft mb-4">

        {{-- ERROR SUMMARY --}}
        @if($errors->any())
            <x-ui.toast type="danger" class="mb-4">
                <div class="text-sm font-semibold mb-1">Mohon periksa kembali input Anda.</div>
                <ul class="text-xs list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.toast>
        @endif

        {{-- FORM CARD --}}
        <x-ui.card
            title="Formulir Pengajuan Izin"
            subtitle="Tanggal mulai dan durasi izin akan dihitung otomatis sebagai periode izin Anda."
        >
            @php
                // Pesan clash dari server (jika ada)
                $serverClashMessage = $errors->first('tanggal_mulai');
            @endphp

            <form
                method="POST"
                action="{{ route('member.izin_latihan.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                {{-- Periode Tanggal & Durasi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Tanggal Mulai --}}
                    <div>
                        <x-ui.label for="tanggal_mulai">Tanggal Mulai Izin</x-ui.label>
                        <div class="mt-1 relative">
                            <input
                                type="date"
                                id="tanggal_mulai"
                                name="tanggal_mulai"
                                value="{{ old('tanggal_mulai') }}"
                                class="w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 pr-9 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                                required
                            >
                            <i
                                data-lucide="calendar"
                                class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            </i>
                        </div>

                        {{-- Pesan overlap / error tanggal --}}
                        <p id="clash-message"
                           class="text-xs text-danger mt-1 {{ $serverClashMessage ? '' : 'hidden' }}">
                            {{ $serverClashMessage }}
                        </p>

                        <p class="text-[11px] text-text-muted mt-1">
                            Pilih tanggal pertama Anda mulai tidak dapat mengikuti latihan.
                        </p>
                    </div>

                    {{-- Durasi Izin --}}
                    <div>
                        <x-ui.label for="jumlah_hari">Durasi Izin (Hari)</x-ui.label>
                        <input
                            type="number"
                            id="jumlah_hari"
                            name="jumlah_hari"
                            min="1"
                            max="30"
                            value="{{ old('jumlah_hari') }}"
                            class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                            placeholder="Masukkan jumlah hari izin yang dibutuhkan"
                            required
                        >
                        @error('jumlah_hari')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-text-muted mt-1">
                            Masukkan jumlah hari izin yang Anda perlukan untuk periode tersebut.
                        </p>
                    </div>
                </div>

                {{-- Alasan --}}
                <div>
                    <x-ui.label for="alasan">Alasan Pengajuan Izin</x-ui.label>
                    <textarea
                        id="alasan"
                        name="alasan"
                        rows="4"
                        class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                        placeholder="Tuliskan alasan Anda tidak dapat mengikuti latihan pada periode tersebut."
                        required
                    >{{ old('alasan') }}</textarea>
                    @error('alasan')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bukti (opsional) --}}
                <div>
                    <x-ui.label for="bukti_alasan">Lampiran Bukti (opsional)</x-ui.label>
                    <input
                        type="file"
                        id="bukti_alasan"
                        name="bukti_alasan"
                        accept="image/*,.pdf,.doc,.docx"
                        class="mt-1 block w-full text-sm text-text-main file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gold-500/90 file:text-brand-black hover:file:bg-gold-500/80"
                    >
                    <p class="text-[11px] text-text-muted mt-1">
                        Contoh: surat keterangan dokter, tiket perjalanan, atau bukti lain
                        (gambar / PDF / DOC, maksimal 2MB).
                    </p>
                    @error('bukti_alasan')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- FOOTER ACTION --}}
                <div class="pt-2 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <p class="text-[11px] text-text-muted max-w-md">
                        Dengan mengajukan izin, Anda menyatakan bahwa data yang Anda kirimkan adalah benar
                        dan dapat dipertanggungjawabkan.
                    </p>

                    <x-ui.button-primary
                        id="btn-kirim-izin"
                        type="submit"
                        class="justify-center sm:w-auto w-full"
                    >
                        Kirim Pengajuan
                        <i data-lucide="send" class="w-4 h-4 ml-2"></i>
                    </x-ui.button-primary>
                </div>
            </form>
        </x-ui.card>
    </div>

    {{-- JS untuk blok tombol ketika periode izin bentrok --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Range izin yang diblokir dari controller (pending + disetujui)
            const blocked = @json(
                ($blockedRanges ?? collect())->map(function ($i) {
                    return [
                        'start' => $i->tanggal_mulai,
                        'end'   => $i->tanggal_selesai,
                    ];
                })
            );

            const startInput   = document.getElementById('tanggal_mulai');
            const durasiInput  = document.getElementById('jumlah_hari');
            const submitBtn    = document.getElementById('btn-kirim-izin');
            const clashMessage = document.getElementById('clash-message');

            function parseDate(str) {
                const d = new Date(str);
                return isNaN(d.getTime()) ? null : d;
            }

            function setDisabledState(disabled, message = '') {
                if (!submitBtn) return;

                submitBtn.disabled = disabled;

                if (disabled) {
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }

                if (clashMessage) {
                    if (message) {
                        clashMessage.textContent = message;
                        clashMessage.classList.remove('hidden');
                    } else {
                        clashMessage.textContent = '';
                        clashMessage.classList.add('hidden');
                    }
                }
            }

            function checkOverlap() {
                const startStr = startInput?.value;
                const days     = parseInt(durasiInput?.value || '0', 10);

                if (!startStr || !days) {
                    // Tidak cukup data → tombol boleh diklik & pesan clash disembunyikan
                    setDisabledState(false, '');
                    return;
                }

                const start = parseDate(startStr);
                if (!start || days <= 0) {
                    setDisabledState(false, '');
                    return;
                }

                const end = new Date(start);
                end.setDate(end.getDate() + days - 1);

                let conflictRange = null;

                for (const r of blocked) {
                    const rStart = parseDate(r.start);
                    const rEnd   = parseDate(r.end);
                    if (!rStart || !rEnd) continue;

                    // overlap?
                    if (start <= rEnd && end >= rStart) {
                        conflictRange = { start: rStart, end: rEnd };
                        break;
                    }
                }

                if (conflictRange) {
                    const opt   = { day: '2-digit', month: 'short', year: 'numeric' };
                    const sText = conflictRange.start.toLocaleDateString('id-ID', opt);
                    const eText = conflictRange.end.toLocaleDateString('id-ID', opt);

                    const msg = `Sudah ada izin lain pada ${sText}–${eText}.`;

                    // Tombol diblokir ketika bentrok
                    setDisabledState(true, msg);
                } else {
                    setDisabledState(false, '');
                }
            }

            startInput?.addEventListener('change', checkOverlap);
            durasiInput?.addEventListener('input', checkOverlap);

            // Jalankan sekali saat halaman selesai dimuat
            checkOverlap();
        });
    </script>
</x-layouts.member>

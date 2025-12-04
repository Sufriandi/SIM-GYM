{{-- resources/views/member/izin_latihan/form.blade.php --}}

<x-layouts.member
    pageTitle="Ajukan Izin Latihan"
    pageSubtitle="Ajukan izin latihan jika Anda berhalangan hadir."
>
    <div class="space-y-6">

        {{-- HEADER + BACK --}}
        <x-ui.section-header
            title="Ajukan Izin Latihan"
            subtitle="Isi form berikut dengan jujur dan lengkap."
        />

        <div class="mb-4">
            <x-ui.back-button
                href="{{ route('member.izin_latihan.index') }}"
                text="Kembali ke Daftar Izin"
            />
        </div>

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
            subtitle="Tanggal, durasi, dan alasan izin akan digunakan sebagai dasar penilaian Admin."
        >
            <form
                method="POST"
                action="{{ route('member.izin_latihan.store') }}"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                @csrf

                {{-- Periode Tanggal --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="tanggal_mulai">Tanggal Mulai Izin</x-ui.label>
                        <input
                            type="date"
                            id="tanggal_mulai"
                            name="tanggal_mulai"
                            value="{{ old('tanggal_mulai') }}"
                            class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                            required
                        >
                        @error('tanggal_mulai')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.label for="tanggal_selesai">Tanggal Selesai Izin</x-ui.label>
                        <input
                            type="date"
                            id="tanggal_selesai"
                            name="tanggal_selesai"
                            value="{{ old('tanggal_selesai') }}"
                            class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                            required
                        >
                        @error('tanggal_selesai')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Durasi (info, dihitung otomatis di backend) --}}
                <div>
                    <x-ui.label for="jumlah_hari_display">Durasi Izin (Hari)</x-ui.label>
                    <input
                        type="number"
                        id="jumlah_hari_display"
                        name="jumlah_hari_display"
                        min="1"
                        value="{{ old('jumlah_hari_display') }}"
                        class="mt-1 w-full rounded-xl border border-brand-borderSoft bg-brand-card text-sm text-text-main px-3 py-2.5 focus:ring-2 focus:ring-gold-500/60 focus:border-gold-500"
                        placeholder="Akan dihitung otomatis"
                        readonly
                    >
                    <p class="text-[11px] text-text-muted mt-1">
                        Durasi izin akan dihitung otomatis dari selisih tanggal mulai dan tanggal selesai
                        di sistem. Angka di atas hanya informasi untuk Anda.
                    </p>
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
                        Contoh: surat keterangan dokter, tiket perjalanan, atau bukti lain (gambar / PDF / DOC, maksimal 2MB).
                    </p>
                    @error('bukti_alasan')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <p class="text-[11px] text-text-muted max-w-md">
                        Dengan mengajukan izin, Anda menyatakan bahwa data yang Anda kirimkan adalah benar dan dapat dipertanggungjawabkan.
                    </p>

                    <x-ui.button-primary type="submit" class="justify-center sm:w-auto w-full">
                        Kirim Pengajuan
                        <i data-lucide="send" class="w-4 h-4 ml-2"></i>
                    </x-ui.button-primary>
                </div>
            </form>
        </x-ui.card>
    </div>

    {{-- JS kecil buat hitung durasi secara live (frontend saja) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startInput  = document.getElementById('tanggal_mulai');
            const endInput    = document.getElementById('tanggal_selesai');
            const durasiInput = document.getElementById('jumlah_hari_display');

            function updateDurasi() {
                if (!startInput.value || !endInput.value) {
                    durasiInput.value = '';
                    return;
                }

                const start = new Date(startInput.value);
                const end   = new Date(endInput.value);

                if (isNaN(start.getTime()) || isNaN(end.getTime()) || end < start) {
                    durasiInput.value = '';
                    return;
                }

                const diffMs   = end - start;
                const diffHari = Math.round(diffMs / (1000 * 60 * 60 * 24)) + 1;

                durasiInput.value = diffHari;
            }

            startInput.addEventListener('change', updateDurasi);
            endInput.addEventListener('change', updateDurasi);
        });
    </script>
</x-layouts.member>

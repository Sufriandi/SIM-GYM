{{-- resources/views/components/ui/toast.blade.php --}}
@php
    /**
     * Prioritas toast:
     * 1. session('toast') -> ['type' => 'success', 'message' => '...']
     * 2. session('success')
     * 3. session('error')
     * 4. session('warning')
     * 5. session('info')
     * 6. (opsional) error validasi pertama
     */

    $toast = session('toast');

    if (! $toast) {
        if (session('success')) {
            $toast = ['type' => 'success', 'message' => session('success')];
        } elseif (session('error')) {
            $toast = ['type' => 'error', 'message' => session('error')];
        } elseif (session('warning')) {
            $toast = ['type' => 'warning', 'message' => session('warning')];
        } elseif (session('info')) {
            $toast = ['type' => 'info', 'message' => session('info')];
        } elseif ($errors->any()) {
            // kalau kamu nggak mau error validasi muncul sebagai toast, hapus block ini
            $toast = [
                'type'    => 'error',
                'message' => $errors->first(),
            ];
        }
    }
@endphp

@if($toast ?? false)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // pastikan Swal (SweetAlert2) sudah tersedia global
            if (typeof Swal === 'undefined') {
                console.warn('SweetAlert2 (Swal) tidak ditemukan. Pastikan sudah di-load.');
                return;
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: @json($toast['type'] ?? 'success'),
                title: @json($toast['message'] ?? ''),
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1f2937', // sedikit gelap biar kontras
                color: '#f9fafb',
                customClass: {
                    popup: 'shadow-btn-primary rounded-xl',
                    title: 'text-[13px] font-medium',
                },
            });
        });
    </script>
@endif

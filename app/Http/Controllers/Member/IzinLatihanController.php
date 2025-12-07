<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class IzinLatihanController extends Controller
{
    /**
     * Ambil record Member milik user yang login.
     * Jika tidak ada, akan melempar 404.
     */
    protected function getCurrentMember(): Member
    {
        $userId = Auth::id();

        return Member::where('user_id', $userId)->firstOrFail();
    }

    /**
     * Menampilkan daftar izin yang HANYA berstatus 'pending'
     * untuk member yang sedang login.
     */
    public function index()
    {
        $pageTitle = 'Izin Membership';

        $member = $this->getCurrentMember();

        $daftar_izin = IzinLatihan::where('member_id', $member->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('member.izin_latihan.index', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan riwayat LENGKAP semua izin (Pending, Disetujui, Ditolak)
     * untuk member yang sedang login.
     */
    public function history()
    {
        $pageTitle = 'Riwayat Pengajuan Izin Lengkap';

        $member = $this->getCurrentMember();

        $daftar_izin = IzinLatihan::where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('member.izin_latihan.history', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan formulir untuk membuat izin baru.
     */
    public function create()
    {
        $pageTitle = 'Formulir Izin Baru';

        $member = $this->getCurrentMember();

        // Range tanggal yang sudah dipakai izin (pending + disetujui)
        $blockedRanges = IzinLatihan::where('member_id', $member->id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->orderBy('tanggal_mulai')
            ->get(['tanggal_mulai', 'tanggal_selesai']);

        return view('member.izin_latihan.form', compact('pageTitle', 'blockedRanges'));
    }

    /**
     * Menyimpan data pengajuan izin baru ke database.
     *
     * Sinkron dengan struktur yang dipakai admin:
     * - pakai kolom member_id
     * - kolom jumlah_hari diinput user
     * - kolom tanggal_selesai dihitung otomatis: tanggal_mulai + (jumlah_hari - 1)
     */
    public function store(Request $request)
    {
        $member = $this->getCurrentMember();

        // 1. Validasi input
        $validated = $request->validate(
            [
                'tanggal_mulai' => 'required|date|after_or_equal:today',
                'jumlah_hari'   => 'required|integer|min:1|max:30',
                'alasan'        => 'required|string|max:1000',
                'bukti_alasan'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ],
            [
                'tanggal_mulai.required'       => 'Tanggal mulai izin wajib diisi.',
                'tanggal_mulai.date'           => 'Format tanggal mulai tidak valid.',
                'tanggal_mulai.after_or_equal' => 'Tanggal mulai izin minimal hari ini.',
                'jumlah_hari.required'         => 'Durasi izin wajib diisi.',
                'jumlah_hari.integer'          => 'Durasi izin harus berupa angka.',
                'jumlah_hari.min'              => 'Durasi izin minimal 1 hari.',
                'jumlah_hari.max'              => 'Durasi izin maksimal 30 hari.',
                'alasan.required'              => 'Alasan izin wajib diisi.',
                'bukti_alasan.mimes'           => 'Bukti harus berformat JPG, JPEG, PNG, PDF, DOC, atau DOCX.',
                'bukti_alasan.max'             => 'Ukuran file bukti maksimal 2MB.',
            ]
        );

        // 2. Hitung tanggal mulai & tanggal selesai berdasarkan durasi
        $tglMulai   = Carbon::parse($validated['tanggal_mulai'])->startOfDay();
        $jumlahHari = (int) $validated['jumlah_hari'];

        // tanggal_selesai = tanggal_mulai + (jumlah_hari - 1)
        $tglSelesai = (clone $tglMulai)->addDays($jumlahHari - 1);

        $tglMulaiBaru   = $tglMulai->toDateString();
        $tglSelesaiBaru = $tglSelesai->toDateString();

        // 3. Anti–spam: cek overlap tanggal dengan izin lain (pending/disetujui)
        // (tetap ada di backend untuk keamanan, walau sudah diblok di frontend)
        $conflict = IzinLatihan::where('member_id', $member->id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->where(function ($q) use ($tglMulaiBaru, $tglSelesaiBaru) {
                $q->whereBetween('tanggal_mulai', [$tglMulaiBaru, $tglSelesaiBaru])
                  ->orWhereBetween('tanggal_selesai', [$tglMulaiBaru, $tglSelesaiBaru])
                  ->orWhere(function ($q2) use ($tglMulaiBaru, $tglSelesaiBaru) {
                      $q2->where('tanggal_mulai', '<=', $tglMulaiBaru)
                         ->where('tanggal_selesai', '>=', $tglSelesaiBaru);
                  });
            })
            ->orderBy('tanggal_mulai')
            ->first();

        if ($conflict) {
            $izinMulai   = Carbon::parse($conflict->tanggal_mulai)->translatedFormat('d M Y');
            $izinSelesai = Carbon::parse($conflict->tanggal_selesai)->translatedFormat('d M Y');

            $message = "Sudah ada izin lain pada {$izinMulai}–{$izinSelesai}.";

            // Kembali ke form dengan error di field tanggal_mulai (tanpa session('error'))
            return back()
                ->withErrors(['tanggal_mulai' => $message])
                ->withInput();
        }

        // 4. Upload bukti (jika ada)
        $path_bukti = null;
        if ($request->hasFile('bukti_alasan')) {
            $path_bukti = $request->file('bukti_alasan')
                ->store('uploads/bukti_izin', 'public');
        }

        // 5. Simpan ke database
        IzinLatihan::create([
            'member_id'       => $member->id,
            'tanggal_mulai'   => $tglMulaiBaru,
            'tanggal_selesai' => $tglSelesaiBaru,
            'jumlah_hari'     => $jumlahHari,
            'alasan'          => trim($validated['alasan']),
            'bukti_alasan'    => $path_bukti,
            'status'          => 'pending',
            // durasi_izin_disetujui, keterangan_admin, tanggal_persetujuan
            // diisi oleh admin saat approve/reject
        ]);

        // 6. Redirect ke halaman pending
        return redirect()
            ->route('member.izin_latihan.index')
            ->with(
                'success',
                'Formulir izin latihan Anda telah berhasil diajukan dan sedang menunggu persetujuan Admin.'
            );
    }

    /**
     * Menampilkan detail izin latihan member.
     * Hanya bisa melihat izin milik dirinya sendiri.
     */
    public function detail($id)
    {
        $pageTitle = 'Detail Izin Latihan';

        $member = $this->getCurrentMember();

        $izin = IzinLatihan::where('id', $id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        return view('member.izin_latihan.detail', compact('izin', 'pageTitle'));
    }
}

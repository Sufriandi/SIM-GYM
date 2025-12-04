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
     * Ambil record member milik user yang login.
     */
    protected function getCurrentMember()
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

        $member   = $this->getCurrentMember();
        $memberId = $member->id;

        $daftar_izin = IzinLatihan::where('member_id', $memberId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('member.izin_latihan.index', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan riwayat LENGKAP semua izin (Pending, Disetujui, Ditolak)
     * untuk member yang sedang login.
     */
    public function history()
    {
        $pageTitle = 'Riwayat Pengajuan Izin Lengkap';

        $member   = $this->getCurrentMember();
        $memberId = $member->id;

        $daftar_izin = IzinLatihan::where('member_id', $memberId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('member.izin_latihan.history', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan formulir untuk membuat izin baru.
     */
    public function create()
    {
        $pageTitle = 'Formulir Izin Baru';

        return view('member.izin_latihan.form', compact('pageTitle'));
    }

    /**
     * Menyimpan data pengajuan izin baru ke database.
     * Sinkron dengan struktur yang dipakai admin:
     *  - pakai kolom member_id
     *  - kolom jumlah_hari dihitung otomatis dari tanggal_mulai & tanggal_selesai
     */
    public function store(Request $request)
    {
        $member = $this->getCurrentMember();

        // 1. Validasi input
        $request->validate(
            [
                'tanggal_mulai'   => 'required|date|after_or_equal:today',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'alasan'          => 'required|string|max:1000',
                'bukti_alasan'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ],
            [
                'tanggal_mulai.required'   => 'Tanggal mulai izin wajib diisi.',
                'tanggal_mulai.after_or_equal' => 'Tanggal mulai izin minimal hari ini.',
                'tanggal_selesai.required' => 'Tanggal selesai izin wajib diisi.',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai izin tidak boleh sebelum tanggal mulai.',
                'alasan.required'          => 'Alasan izin wajib diisi.',
                'bukti_alasan.mimes'       => 'Bukti harus berformat JPG, JPEG, PNG, PDF, DOC, atau DOCX.',
                'bukti_alasan.max'         => 'Ukuran file bukti maksimal 2MB.',
            ]
        );

        // 2. Anti–spam: cek overlap tanggal dengan izin lain (pending/disetujui) milik member yang sama
        $tglMulaiBaru   = Carbon::parse($request->tanggal_mulai)->toDateString();
        $tglSelesaiBaru = Carbon::parse($request->tanggal_selesai)->toDateString();

        $existingIzin = IzinLatihan::where('member_id', $member->id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->get();

        foreach ($existingIzin as $izin) {
            $overlap =
                $tglMulaiBaru <= $izin->tanggal_selesai &&
                $tglSelesaiBaru >= $izin->tanggal_mulai;

            if ($overlap) {
                $izinMulai   = Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y');
                $izinSelesai = Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y');

                return redirect()
                    ->route('member.izin_latihan.index')
                    ->with('error', "Gagal! Periode izin Anda bertabrakan dengan izin yang sudah ada pada tanggal {$izinMulai} s/d {$izinSelesai}.");
            }
        }

        // 3. Upload bukti (jika ada)
        $path_bukti = null;
        if ($request->hasFile('bukti_alasan')) {
            $path_bukti = $request->file('bukti_alasan')
                ->store('uploads/bukti_izin', 'public');
        }

        // 4. Hitung jumlah hari dari tanggal_mulai & tanggal_selesai
        $tglMulai   = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tglSelesai = Carbon::parse($request->tanggal_selesai)->startOfDay();
        $jumlahHari = $tglMulai->diffInDays($tglSelesai) + 1;

        // 5. Simpan ke database (sinkron dengan struktur di sisi admin)
        IzinLatihan::create([
            'member_id'       => $member->id,
            'tanggal_mulai'   => $tglMulai->toDateString(),
            'tanggal_selesai' => $tglSelesai->toDateString(),
            'jumlah_hari'     => $jumlahHari,
            'alasan'          => trim($request->alasan),
            'bukti_alasan'    => $path_bukti,
            'status'          => 'pending',
            // kolom lain (durasi_izin_disetujui, keterangan_admin, tanggal_persetujuan) diisi oleh admin saat approve/reject
        ]);

        // 6. Redirect ke halaman pending
        return redirect()
            ->route('member.izin_latihan.index')
            ->with('success', 'Formulir izin latihan Anda telah berhasil diajukan dan sedang menunggu persetujuan Admin.');
    }
    /**
     * Menampilkan detail izin latihan member.
     */
    public function detail($id)
    {
        $pageTitle = 'Detail Izin Latihan';
        
        // Ambil member dari user yang sedang login
        // (Saya ubah ini biar aman, jaga-jaga kalau function getCurrentMember gak ada)
        $member = auth()->user()->member; 
        
        if (!$member) {
            return redirect()->back()->with('error', 'Data member tidak ditemukan.');
        }

        $memberId = $member->id;

        // Cari izin berdasarkan ID dan pastikan milik member tersebut
        $izin = IzinLatihan::where('id', $id)
            ->where('member_id', $memberId)
            ->firstOrFail();

        // Arahkan ke view detail
        return view('member.izin_latihan.detail', compact('izin', 'pageTitle'));
    } 
    
}

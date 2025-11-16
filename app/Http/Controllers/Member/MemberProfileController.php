<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Member;

// Pastikan file ini dinamai MemberProfileController.php

class MemberProfileController extends Controller
{
    /**
     * Helper untuk mengecek apakah data wajib member sudah terisi.
     */
    private function isProfileComplete()
    {
        $member = Auth::user()->member;

        // Cek apakah kolom-kolom wajib unik sudah terisi
        return $member && $member->no_hp && $member->alamat && $member->jenis_kelamin;
    }

    /**
     * Menampilkan formulir pelengkapan profil.
     */
    public function showCompletionForm()
    {
        $member = Auth::user()->member;

        // Jika profil sudah lengkap, redirect ke dashboard
        if ($this->isProfileComplete()) {
            // Asumsi route dashboard adalah 'member.dashboard'
            return redirect()->route('member.dashboard')->with('info', 'Profil Anda sudah lengkap.');
        }

        $pageTitle = 'Lengkapi Data Profil';

        return view('member.profile.complete', compact('pageTitle', 'member'));
    }

    /**
     * Menyimpan data pelengkapan profil.
     */
    public function completeProfile(Request $request)
    {
        $member = Auth::user()->member;

        // Validasi data member (perhatikan unique:members,no_hp,ID)
        $request->validate([
            'no_hp' => 'required|string|max:15|unique:members,no_hp,' . $member->id,
            'alamat' => 'required|string|max:1000',
            'jenis_kelamin' => ['required', Rule::in(['laki-laki', 'perempuan'])],

            // Aturan validasi membership
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        // 🚨 LOGIKA MEMBERSHIP TAMBAHAN
        $tglMulai = $request->tanggal_mulai;
        $tglAkhir = $request->tanggal_akhir;

        // Jika Tanggal Akhir diisi, tapi Tanggal Mulai kosong, set Tanggal Mulai ke Hari Ini
        if ($tglAkhir && !$tglMulai) {
             $tglMulai = now()->toDateString();
        }


        // Update data member
        $member->update([
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'jenis_kelamin' => $request->jenis_kelamin,

            // Menyimpan data membership
            'tanggal_mulai' => $tglMulai,
            'tanggal_akhir' => $tglAkhir,

            // Pastikan status di-update menjadi 'aktif'
            'status' => 'aktif',
        ]);

        // Redirect ke dashboard setelah selesai
        return redirect()->route('member.dashboard')->with('success', 'Data profil dan masa membership Anda berhasil dilengkapi! Selamat Datang.');
    }
}

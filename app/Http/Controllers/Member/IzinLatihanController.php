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
    protected function getCurrentMember(): Member
    {
        $userId = Auth::id();
        return Member::where('user_id', $userId)->firstOrFail();
    }

    protected function isMembershipActive(Member $member): bool
    {
        if (! $member->tanggal_mulai || ! $member->tanggal_akhir) {
            return false;
        }

        $today = Carbon::today();
        $mulai = Carbon::parse($member->tanggal_mulai)->startOfDay();
        $akhir = Carbon::parse($member->tanggal_akhir)->endOfDay();

        return $today->gte($mulai) && $today->lte($akhir);
    }

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

    public function history()
    {
        $pageTitle = 'Riwayat Pengajuan Izin Lengkap';
        $member = $this->getCurrentMember();

        $daftar_izin = IzinLatihan::where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('member.izin_latihan.history', compact('daftar_izin', 'pageTitle'));
    }

    public function create()
    {
        $pageTitle = 'Formulir Izin Baru';
        $member = $this->getCurrentMember();

        if (! $this->isMembershipActive($member)) {
            return redirect()
                ->route('member.izin_latihan.index')
                ->with('error', 'Anda hanya dapat mengajukan izin jika membership Anda masih aktif.');
        }

        $blockedRanges = IzinLatihan::where('member_id', $member->id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->orderBy('tanggal_mulai')
            ->get(['tanggal_mulai', 'tanggal_selesai']);

        return view('member.izin_latihan.form', compact('pageTitle', 'blockedRanges'));
    }

    public function store(Request $request)
    {
        $member = $this->getCurrentMember();

        if (! $this->isMembershipActive($member)) {
            return redirect()
                ->route('member.izin_latihan.index')
                ->with('error', 'Anda hanya dapat mengajukan izin jika membership Anda masih aktif.');
        }

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

        $tglMulai   = Carbon::parse($validated['tanggal_mulai'])->startOfDay();
        $jumlahHari = (int) $validated['jumlah_hari'];
        $tglSelesai = (clone $tglMulai)->addDays($jumlahHari - 1);

        $tglMulaiBaru   = $tglMulai->toDateString();
        $tglSelesaiBaru = $tglSelesai->toDateString();

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

            return back()
                ->withErrors(['tanggal_mulai' => $message])
                ->withInput();
        }

        $path_bukti = null;
        if ($request->hasFile('bukti_alasan')) {
            $path_bukti = $request->file('bukti_alasan')
                ->store('uploads/bukti_izin', 'public');
        }

        IzinLatihan::create([
            'member_id'       => $member->id,
            'tanggal_mulai'   => $tglMulaiBaru,
            'tanggal_selesai' => $tglSelesaiBaru,
            'jumlah_hari'     => $jumlahHari,
            'alasan'          => trim($validated['alasan']),
            'bukti_alasan'    => $path_bukti,
            'status'          => 'pending',
        ]);

        return redirect()
            ->route('member.izin_latihan.index')
            ->with('success', 'Formulir izin latihan Anda telah berhasil diajukan dan sedang menunggu persetujuan Admin.');
    }

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

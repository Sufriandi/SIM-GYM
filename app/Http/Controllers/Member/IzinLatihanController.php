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
        return $member->hasActiveMembershipOn(\Carbon\Carbon::today());

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

    // (tetap) gate aktif membership Anda
    if (! $this->isMembershipActive($member)) {
        return redirect()
            ->route('member.izin_latihan.index')
            ->with('error', 'Anda hanya dapat mengajukan izin jika membership Anda masih aktif.');
    }
    if ($this->hasSubmittedToday($member)) {
    return redirect()
        ->route('member.izin_latihan.index')
        ->with('error', 'Hari ini Anda sudah mengajukan izin. Silakan ajukan lagi besok.');
}


    // Range tanggal yang diblokir:
    // - pending: pakai tanggal_mulai - tanggal_selesai (yang diajukan)
    // - disetujui: pakai tanggal_mulai - (tanggal_mulai + durasi_izin_disetujui - 1)
    $blockedRanges = IzinLatihan::where('member_id', $member->id)
        ->whereIn('status', ['pending', 'disetujui'])
        ->orderBy('tanggal_mulai')
        ->get(['status', 'tanggal_mulai', 'tanggal_selesai', 'durasi_izin_disetujui', 'jumlah_hari'])
        ->map(function ($i) {
            $start = Carbon::parse($i->tanggal_mulai)->startOfDay();

            if ($i->status === 'pending') {
                $end = Carbon::parse($i->tanggal_selesai)->startOfDay();
                return [
                    'start' => $start->toDateString(),
                    'end'   => $end->toDateString(),
                ];
            }

            // disetujui -> pakai durasi_izin_disetujui (fallback jumlah_hari)
            $days = (int) ($i->durasi_izin_disetujui ?? $i->jumlah_hari ?? 0);
            if ($days <= 0) return null; // 0 hari disetujui: tidak memblok apa pun

            $end = $start->copy()->addDays($days - 1);

            return [
                'start' => $start->toDateString(),
                'end'   => $end->toDateString(),
            ];
        })
        ->filter()
        ->values();

    return view('member.izin_latihan.form', compact('pageTitle', 'blockedRanges'));
}

    public function store(Request $request)
{
    $member = $this->getCurrentMember();

    // Gate: membership harus aktif (pakai transaksi, bukan field di members)
    if (!\App\Models\TransaksiMembership::isAktifUntukMember($member->id, \Carbon\Carbon::today())) {
        return redirect()
            ->route('member.izin_latihan.index')
            ->with('error', 'Anda hanya dapat mengajukan izin jika membership Anda masih aktif.');
    }
    if ($this->hasSubmittedToday($member)) {
    return redirect()
        ->route('member.izin_latihan.index')
        ->with('error', 'Hari ini Anda sudah mengajukan izin. Silakan ajukan lagi besok.');
}


    // 1) Validasi input
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

    // 2) Hitung tanggal mulai & tanggal selesai (yang diajukan)
    $newStart   = \Carbon\Carbon::parse($validated['tanggal_mulai'])->startOfDay();
    $jumlahHari = (int) $validated['jumlah_hari'];

    $newEnd = $newStart->copy()->addDays($jumlahHari - 1);

    $tglMulaiBaru   = $newStart->toDateString();
    $tglSelesaiBaru = $newEnd->toDateString();

    // 3) Anti-overlap:
    // - pending: rentang = tanggal_mulai .. tanggal_selesai (yang diajukan)
    // - disetujui: rentang efektif = tanggal_mulai .. (tanggal_mulai + durasi_izin_disetujui - 1)
    //   (fallback ke jumlah_hari bila durasi_izin_disetujui null)
    //
    // Ambil kandidat dengan coarse filter agar tetap cepat
    $candidates = \App\Models\IzinLatihan::query()
        ->where('member_id', $member->id)
        ->whereIn('status', ['pending', 'disetujui'])
        ->whereDate('tanggal_mulai', '<=', $tglSelesaiBaru)
        ->whereDate('tanggal_selesai', '>=', $tglMulaiBaru)
        ->orderBy('tanggal_mulai')
        ->get(['status', 'tanggal_mulai', 'tanggal_selesai', 'durasi_izin_disetujui', 'jumlah_hari']);

    foreach ($candidates as $ex) {
        $exStart = \Carbon\Carbon::parse($ex->tanggal_mulai)->startOfDay();

        if ($ex->status === 'pending') {
            $exEnd = \Carbon\Carbon::parse($ex->tanggal_selesai)->startOfDay();
        } else {
            $days = (int) ($ex->durasi_izin_disetujui ?? $ex->jumlah_hari ?? 0);

            // Jika admin menyetujui 0 hari, maka tidak memblok tanggal apa pun
            if ($days <= 0) {
                continue;
            }

            $exEnd = $exStart->copy()->addDays($days - 1);
        }

        // Overlap check (inklusif)
        if ($newStart->lte($exEnd) && $newEnd->gte($exStart)) {
            $msg = "Sudah ada izin lain pada {$exStart->translatedFormat('d M Y')}–{$exEnd->translatedFormat('d M Y')}.";

            return back()
                ->withErrors(['tanggal_mulai' => $msg])
                ->withInput();
        }
    }

    // 4) Upload bukti (jika ada)
    $path_bukti = null;
    if ($request->hasFile('bukti_alasan')) {
        $path_bukti = $request->file('bukti_alasan')->store('uploads/bukti_izin', 'public');
    }

    // 5) Simpan izin baru (pending)
    \App\Models\IzinLatihan::create([
        'member_id'       => $member->id,
        'tanggal_mulai'   => $tglMulaiBaru,
        'tanggal_selesai' => $tglSelesaiBaru,
        'jumlah_hari'     => $jumlahHari,
        'alasan'          => trim($validated['alasan']),
        'bukti_alasan'    => $path_bukti,
        'status'          => 'pending',
        // durasi_izin_disetujui, keterangan_admin, tanggal_persetujuan -> diisi admin
    ]);

    // 6) Redirect
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
    protected function hasSubmittedToday(Member $member): bool
{
    $start = now()->startOfDay();
    $end   = now()->endOfDay();

    return IzinLatihan::query()
        ->where('member_id', $member->id)
        ->whereBetween('created_at', [$start, $end])
        ->exists();
}

}

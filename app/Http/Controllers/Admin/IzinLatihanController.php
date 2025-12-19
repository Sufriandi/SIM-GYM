<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IzinLatihan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class IzinLatihanController extends Controller
{
    public function __construct()
    {
        $izinPending = IzinLatihan::where('status', 'pending')->count();
        View::share('izinPending', $izinPending);
    }

    public function index(Request $request)
    {
        $pageTitle = 'Permintaan Izin Baru';

        $query = IzinLatihan::with(['member.user'])
            ->where('status', 'pending');

        // ==========================
        // SEARCH (sesuai DB terbaru)
        // users.name / users.username
        // ==========================
        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($q) use ($search) {
                $q->whereHas('member.user', function ($u) use ($search) {
                    $u->where('name', 'like', $search . '%')
                      ->orWhere('username', 'like', $search . '%');
                });
            });
        }

        // ==========================
        // SORTING
        // ==========================
        $sort = $request->input('sort', 'newest');

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'days_max':
                $query->orderBy('jumlah_hari', 'desc')
                      ->orderBy('created_at', 'desc');
                break;

            case 'days_min':
                $query->orderBy('jumlah_hari', 'asc')
                      ->orderBy('created_at', 'desc');
                break;

            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $daftar_izin = $query->paginate(20)->withQueryString();

        // ==========================
        // MEMBERS UNTUK SELECT (sesuai DB terbaru)
        // ==========================
        $membersForSelect = Member::query()
    ->with(['user:id,name,username,role'])
    ->whereHas('user', function ($q) {
        $q->where('role', 'member');
    })
    ->orderBy(
        User::select('name')
            ->whereColumn('users.id', 'members.user_id')
            ->limit(1)
    )
    ->get(['id', 'user_id']);


        return view('admin.izin_latihan.index', compact(
            'daftar_izin',
            'pageTitle',
            'membersForSelect',
        ));
    }

    public function storeManual(Request $request)
{
    $validated = $request->validateWithBag(
        'izin_manual',
        [
            'member_id'     => 'required|exists:members,id',
            'jumlah_hari'   => 'required|integer|min:1|max:30',
            'tanggal_mulai' => 'required|date',
            'bukti_alasan'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',

            // FIX: samakan dengan input di Blade (textarea name="alasan")
            'alasan'        => 'nullable|string|max:5000',
        ],
        [
            'member_id.required'     => 'Silakan pilih member terlebih dahulu.',
            'member_id.exists'       => 'Member yang dipilih tidak ditemukan.',
            'jumlah_hari.required'   => 'Jumlah hari wajib diisi.',
            'jumlah_hari.integer'    => 'Jumlah hari harus berupa angka.',
            'jumlah_hari.min'        => 'Jumlah hari minimal 1 hari.',
            'jumlah_hari.max'        => 'Jumlah hari maksimal 30 hari.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date'     => 'Format tanggal mulai tidak valid.',
            'bukti_alasan.max'       => 'Ukuran file maksimal 2MB.',
            'bukti_alasan.mimes'     => 'Format file harus JPG, JPEG, PNG, PDF, DOC, atau DOCX.',
        ]
    );

    $jumlahHari = (int) $validated['jumlah_hari'];

    $member = Member::with('user')->findOrFail($validated['member_id']);

    $tanggalMulai = Carbon::parse($validated['tanggal_mulai'])->startOfDay();

    // durasi inklusif
    $tanggalSelesai = (clone $tanggalMulai)->addDays($jumlahHari - 1);

    $buktiPath = null;
    if ($request->hasFile('bukti_alasan')) {
        $buktiPath = $request->file('bukti_alasan')
            ->store('uploads/bukti_izin', 'public');
    }

    $alasanText = isset($validated['alasan']) ? trim($validated['alasan']) : '';

    IzinLatihan::create([
        'member_id'             => $member->id,
        'jumlah_hari'           => $jumlahHari,
        'tanggal_mulai'         => $tanggalMulai->toDateString(),
        'tanggal_selesai'       => $tanggalSelesai->toDateString(),
        'status'                => 'disetujui',
        'durasi_izin_disetujui' => $jumlahHari,
        'bukti_alasan'          => $buktiPath,
        'alasan'                => $alasanText !== '' ? $alasanText : '[Ditambahkan manual oleh admin]',
        'keterangan_admin'      => 'Izin manual ditambahkan dan langsung disetujui oleh Admin.',
        'tanggal_persetujuan'   => now(),
    ]);

    // Perpanjang tanggal_akhir membership
    if ($member->tanggal_akhir) {
        $akhirLama = Carbon::parse($member->tanggal_akhir)->startOfDay();
        $akhirBaru = $akhirLama->addDays($jumlahHari);
    } else {
        $akhirBaru = (clone $tanggalSelesai);
    }

    $member->update([
        'tanggal_akhir' => $akhirBaru->toDateString(),
    ]);

    return redirect()
        ->route('admin.izin_latihan.index')
        ->with('success', 'Izin manual berhasil ditambahkan dan langsung disetujui, membership member ikut diperpanjang.');
}


    public function history(Request $request)
{
    $pageTitle = 'Riwayat Persetujuan Izin';

    $query = IzinLatihan::with(['member.user'])
        ->whereIn('status', ['disetujui', 'ditolak']);

    // Default: diproses terbaru
    $sort = $request->input('sort', 'processed_newest');

    // "Tanggal diproses admin" -> aman untuk data lama / null
    $processedAtExpr = "COALESCE(tanggal_persetujuan, updated_at, created_at)";

    // Disetujui (H): kalau ditolak dianggap 0 agar sorting stabil
    $approvedDaysExpr = "CASE
        WHEN status = 'disetujui' THEN COALESCE(durasi_izin_disetujui, 0)
        ELSE 0
    END";

    switch ($sort) {
        // === Diproses ===
        case 'processed_oldest':
            $query->orderByRaw("$processedAtExpr ASC")
                  ->orderBy('id', 'ASC');
            break;

        case 'processed_newest':
        default:
            $query->orderByRaw("$processedAtExpr DESC")
                  ->orderBy('id', 'DESC');
            break;

        // === Disetujui (H) ===
        case 'approved_max':
            $query->orderByRaw("$approvedDaysExpr DESC")
                  ->orderByRaw("$processedAtExpr DESC")
                  ->orderBy('id', 'DESC');
            break;

        case 'approved_min':
            $query->orderByRaw("$approvedDaysExpr ASC")
                  ->orderByRaw("$processedAtExpr DESC")
                  ->orderBy('id', 'DESC');
            break;

        // === Diajukan (H) ===
        case 'requested_max':
            $query->orderBy('jumlah_hari', 'DESC')
                  ->orderByRaw("$processedAtExpr DESC")
                  ->orderBy('id', 'DESC');
            break;

        case 'requested_min':
            $query->orderBy('jumlah_hari', 'ASC')
                  ->orderByRaw("$processedAtExpr DESC")
                  ->orderBy('id', 'DESC');
            break;
    }

    $riwayat_izin = $query->paginate(15)->withQueryString();

    return view('admin.izin_latihan.history', compact('riwayat_izin', 'pageTitle'));
}



    public function show($id)
    {
        $pageTitle = 'Detail Izin Member';

        $izin = IzinLatihan::with(['member.user'])->findOrFail($id);

        return view('admin.izin_latihan.detail', compact('izin', 'pageTitle'));
    }

    public function approveForm(IzinLatihan $izinLatihan)
    {
        if ($izinLatihan->status !== 'pending') {
            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', 'Izin sudah diproses.');
        }

        $pageTitle = 'Formulir Persetujuan Izin';
        $izin = $izinLatihan->load(['member.user']);

        return view('admin.izin_latihan.approve_form', compact('izin', 'pageTitle'));
    }

    public function approveIzin(Request $request, IzinLatihan $izinLatihan)
    {
        $request->validate([
            'approved_days'    => 'required|integer|min:0|max:' . $izinLatihan->jumlah_hari,
            'keterangan_admin' => 'nullable|string|max:1000',
        ], [
            'approved_days.max' => 'Hari yang disetujui tidak boleh melebihi durasi permintaan member (' . $izinLatihan->jumlah_hari . ' hari).'
        ]);

        $approvedDays = (int) $request->approved_days;

        if ($izinLatihan->status !== 'pending') {
            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', 'Izin sudah diproses.');
        }

        $member = $izinLatihan->member?->load('user');
        if (!$member) {
            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', 'Member tidak ditemukan.');
        }

        $memberName = optional($member->user)->name ?? 'Member';

        DB::beginTransaction();

        try {
            $izinLatihan->update([
                'status'                => 'disetujui',
                'durasi_izin_disetujui' => $approvedDays,
                'keterangan_admin'      => $request->keterangan_admin,
                'tanggal_persetujuan'   => now(),
            ]);

            if ($approvedDays > 0) {
                // Aman saat tanggal_akhir null
                if ($member->tanggal_akhir) {
                    $newEndDate = Carbon::parse($member->tanggal_akhir)->startOfDay()
                        ->addDays($approvedDays)
                        ->toDateString();
                } else {
                    // set dari hari ini secara inklusif
                    $newEndDate = now()->startOfDay()
                        ->addDays($approvedDays - 1)
                        ->toDateString();
                }

                $member->update(['tanggal_akhir' => $newEndDate]);

                $message = "Membership {$memberName} diperpanjang {$approvedDays} hari.";
            } else {
                $message = "Izin {$memberName} disetujui. Tanpa perpanjangan membership (0 hari).";
            }

            DB::commit();

            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses persetujuan. Terjadi kesalahan sistem.');
        }
    }

    /**
     * Proses untuk menolak izin latihan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'keterangan_admin' => 'nullable|string|max:1000',
        ]);

        $izin = IzinLatihan::with(['member.user'])->findOrFail($id);

        if ($izin->status === 'pending') {
            $izin->status = 'ditolak';
            $izin->keterangan_admin = $request->input('keterangan_admin') ?: null;
            $izin->tanggal_persetujuan = now();
            $izin->save();

            $memberName = optional($izin->member?->user)->name ?? '';

            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('success', 'Izin member ' . $memberName . ' telah DITOLAK.');
        }

        return redirect()
            ->route('admin.izin_latihan.index')
            ->with('info', 'Izin latihan ini sudah diproses sebelumnya.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IzinLatihan;
use App\Models\Member;
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

        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($q) use ($search) {
                $q->whereHas('member', function ($q2) use ($search) {
                    $q2->where('nama', 'like', $search . '%');
                });

                $q->orWhereHas('member.user', function ($q3) use ($search) {
                    $q3->where('username', 'like', $search . '%');
                });
            });
        }

        $sort = $request->sort ?? 'newest';

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

        $daftar_izin = $query->paginate(15)->withQueryString();

        $membersForSelect = Member::with(['user:id,username'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'member');
            })
            ->orderBy('nama')
            ->get(['id', 'nama', 'user_id']);

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
                'keterangan'    => 'nullable|string|max:5000',
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

        $member = Member::findOrFail($validated['member_id']);

        $tanggalMulai   = Carbon::parse($validated['tanggal_mulai'])->startOfDay();
        $tanggalSelesai = (clone $tanggalMulai)->addDays($jumlahHari);

        $buktiPath = null;
        if ($request->hasFile('bukti_alasan')) {
            $buktiPath = $request->file('bukti_alasan')
                ->store('uploads/bukti_izin', 'public');
        }

        $alasanText = isset($validated['keterangan'])
            ? trim($validated['keterangan'])
            : '';

        $izin = IzinLatihan::create([
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

    public function history()
    {
        $pageTitle = 'Riwayat Persetujuan Izin';

        $riwayat_izin = IzinLatihan::with(['member.user'])
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->orderBy('tanggal_persetujuan', 'desc')
            ->paginate(15);

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

        $member = $izinLatihan->member;
        if (!$member) {
            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', 'Member tidak ditemukan.');
        }

        DB::beginTransaction();

        try {
            $izinLatihan->update([
                'status'                 => 'disetujui',
                'durasi_izin_disetujui'  => $approvedDays,
                'keterangan_admin'       => $request->keterangan_admin,
                'tanggal_persetujuan'    => now(),
            ]);

            $newEndDate = $member->tanggal_akhir;

            if ($approvedDays > 0) {
                $currentEndDate = Carbon::parse($member->tanggal_akhir);
                $newEndDate = $currentEndDate->addDays($approvedDays)->toDateString();

                $member->update([
                    'tanggal_akhir' => $newEndDate,
                ]);

                $message = "Membership {$member->nama} diperpanjang {$approvedDays} hari.";
            } else {
                $message = "Izin {$member->nama} disetujui. Tanpa perpanjangan membership (0 hari).";
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
        // ⬅️ VALIDASI BARU: opsional, TIDAK required
        $request->validate([
            'keterangan_admin' => 'nullable|string|max:1000',
        ]);

        $izin = IzinLatihan::with(['member.user'])->findOrFail($id);

        if ($izin->status === 'pending') {
            $izin->status = 'ditolak';

            // Simpan keterangan admin kalau diisi
            $izin->keterangan_admin = $request->input('keterangan_admin') ?: null;

            $izin->tanggal_persetujuan = now();
            $izin->save();

            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('success', 'Izin member ' . ($izin->member?->nama ?? '') . ' telah DITOLAK.');
        }

        return redirect()
            ->route('admin.izin_latihan.index')
            ->with('info', 'Izin latihan ini sudah diproses sebelumnya.');
    }
}

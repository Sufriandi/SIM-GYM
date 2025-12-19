<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class IzinLatihanController extends Controller
{
    /**
     * List izin latihan (untuk admin / history).
     * GET /api/izin-latihan
     */
    public function index(Request $request)
    {
        $query = IzinLatihan::query()->with('member');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $izin = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data izin latihan berhasil diambil',
            'data'    => $izin,
        ]);
    }

    /**
     * Riwayat izin latihan untuk 1 member (dipakai Android).
     * GET /api/izin-latihan/member/{member}
     */
    public function historyByMember($member)
    {
        $izinList = IzinLatihan::where('member_id', $member)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id'              => $item->id,
                    'member_id'       => $item->member_id,
                    'tanggal_mulai'   => $item->tanggal_mulai,
                    'tanggal_selesai' => $item->tanggal_selesai,
                    'jumlah_hari'     => $item->jumlah_hari,
                    'alasan'          => $item->alasan,
                    'status'          => $item->status,
                    'created_at'      => optional($item->created_at)->format('Y-m-d H:i:s'),
                    'bukti_url'       => $item->bukti_alasan
                        ? Storage::disk('public')->url($item->bukti_alasan)
                        : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat izin latihan member',
            'data'    => $izinList,
        ]);
    }

    /**
     * Ajukan izin dari Android.
     * POST /api/izin-latihan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id'       => 'required|integer',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_hari'     => 'required|integer|min:1',
            'alasan'          => 'required|string',
            'bukti_alasan'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // handle upload bukti
        $buktiPath = null;
        if ($request->hasFile('bukti_alasan')) {
            $file = $request->file('bukti_alasan');
            $filename = time() . '_' . $file->getClientOriginalName();
            // simpan di storage/app/public/izin_bukti
            $buktiPath = $file->storeAs('izin_bukti', $filename, 'public');
        }

        $izin = IzinLatihan::create([
            'member_id'       => $request->member_id,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari'     => $request->jumlah_hari,
            'alasan'          => $request->alasan,
            'bukti_alasan'    => $buktiPath,   // path relative di disk 'public'
            'status'          => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan izin latihan berhasil dikirim',
            'data'    => $izin,
        ], 201);
    }

    /**
     * Update status oleh admin.
     * PUT /api/izin-latihan/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'                => 'required|in:pending,disetujui,ditolak',
            'durasi_izin_disetujui' => 'nullable|integer|min:1',
            'keterangan_admin'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $izin = IzinLatihan::findOrFail($id);

        $izin->status                = $request->status;
        $izin->durasi_izin_disetujui = $request->durasi_izin_disetujui;
        $izin->keterangan_admin      = $request->keterangan_admin;
        $izin->tanggal_persetujuan   = Carbon::now();

        $izin->save();

        return response()->json([
            'success' => true,
            'message' => 'Status izin latihan berhasil diperbarui',
            'data'    => $izin,
        ]);
    }
}

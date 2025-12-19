<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SesiAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SesiAbsensiController extends Controller
{
    public function index()
    {
        $data = SesiAbsensi::orderBy('tanggal', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sesi' => 'required|string|max:100',
            'tanggal'   => 'required|date',
            'jam_mulai' => 'nullable',
            'jam_selesai' => 'nullable',
            'created_by' => 'required|exists:users,id'
        ]);

        $sesi = SesiAbsensi::create([
            'kode_qr'     => strtoupper(Str::random(32)),
            'nama_sesi'   => $request->nama_sesi,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'created_by'  => $request->created_by,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi absensi berhasil dibuat',
            'data' => $sesi
        ], 201);
    }

    public function show($id)
    {
        $sesi = SesiAbsensi::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $sesi
        ]);
    }

    public function closeSesi($id)
    {
        $sesi = SesiAbsensi::findOrFail($id);
        $sesi->status = 'selesai';
        $sesi->save();

        return response()->json([
            'success' => true,
            'message' => 'Sesi absensi berhasil ditutup'
        ]);
    }

    public function destroy($id)
    {
        $sesi = SesiAbsensi::findOrFail($id);
        $sesi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi absensi berhasil dihapus'
        ]);
    }
}

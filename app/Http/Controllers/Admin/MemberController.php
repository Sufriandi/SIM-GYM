<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    /**
     * Tampilkan daftar member (index).
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Member::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('no_hp', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $members = $query->orderBy('nama')->paginate(10);

        return view('admin.members.index', compact('members', 'search'));
    }

    /**
     * Store member baru (admin tambah member).
     * Di sini kita buat USER + MEMBER sekaligus.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'         => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'no_hp'         => ['nullable', 'string', 'max:20', 'unique:users,no_hp'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'alamat'        => ['nullable', 'string'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'foto'          => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            // 1. Buat user
            $user = User::create([
                'name'     => $request->nama,
                'username' => $request->username,
                'email'    => $request->email,
                'no_hp'    => $request->no_hp,
                'password' => $request->password, // auto di-hash oleh cast
                'role'     => 'member',
            ]);

            // Event created di model User otomatis membuat satu record Member.
            // Kita ambil record member-nya dan update detailnya.
            $member = $user->member;

            if (!$member) {
                // jaga-jaga kalau event tidak jalan
                $member = Member::create([
                    'user_id'        => $user->id,
                    'nama'           => $request->nama,
                    'tanggal_daftar' => now(),
                    // kolom status sudah tidak ada, jadi tidak diisi lagi
                ]);
            }

            // 2. Upload foto kalau ada
            $fotoPath = $member->foto;
            if ($request->hasFile('foto')) {
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }

                $fotoPath = $request->file('foto')->store('members', 'public');
            }

            // 3. Update data member
            $member->update([
                'nama'           => $request->nama,
                'alamat'         => $request->alamat,
                'tanggal_mulai'  => $request->tanggal_mulai,
                'tanggal_akhir'  => $request->tanggal_akhir,
                'foto'           => $fotoPath,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.members.index')
                ->with('success', 'Member baru berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan member: ' . $th->getMessage());
        }
    }

    /**
     * Update data member (dan no_hp di user).
     */
    public function update(Request $request, Member $member)
    {
        $user = $member->user;

        $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'no_hp'         => ['nullable', 'string', 'max:20', 'unique:users,no_hp,' . ($user->id ?? 'NULL')],
            'alamat'        => ['nullable', 'string'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            // 'status' dihapus karena kolomnya sudah tidak ada
            'foto'          => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            // Data member yang boleh di-update
            $dataMember = [
                'nama'          => $request->nama,
                'alamat'        => $request->alamat,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_akhir' => $request->tanggal_akhir,
            ];

            // Foto baru?
            if ($request->hasFile('foto')) {
                if ($member->foto && Storage::disk('public')->exists($member->foto)) {
                    Storage::disk('public')->delete($member->foto);
                }
                $dataMember['foto'] = $request->file('foto')->store('members', 'public');
            }

            $member->update($dataMember);

            // Update no_hp di user (kalau relasi ada)
            if ($user) {
                $user->update([
                    'no_hp' => $request->no_hp,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.members.index')
                ->with('success', 'Data member berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate member: ' . $th->getMessage());
        }
    }

    /**
     * Hapus member.
     * NOTE: untuk sekarang hanya hapus record member.
     * Kalau mau sekalian hapus user, hati-hati dengan relasi ke tabel lain.
     */
    public function destroy(Member $member)
    {
        try {
            // hapus foto kalau ada
            if ($member->foto && Storage::disk('public')->exists($member->foto)) {
                Storage::disk('public')->delete($member->foto);
            }

            $member->delete();

            // Kalau mau: $member->user?->delete();

            return redirect()
                ->route('admin.members.index')
                ->with('success', 'Member berhasil dihapus.');
        } catch (\Throwable $th) {
            return back()
                ->with('error', 'Tidak dapat menghapus member: ' . $th->getMessage());
        }
    }

    // method create/show/edit bisa dikosongkan karena kamu pakai modal di index
    public function create() {}
    public function show(Member $member) {}
    public function edit(Member $member) {}
}

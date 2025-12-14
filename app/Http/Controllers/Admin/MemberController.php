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
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Member::query()
            ->with('user')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->select('members.*');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.no_hp', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('users.name')->paginate(10);

        return view('admin.members.index', compact('members', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'         => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'no_hp'         => ['nullable', 'string', 'max:20', 'unique:users,no_hp'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],

            'alamat'        => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'],

            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],

            'foto'          => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        $fotoPath = null;

        try {
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('users', 'public');
            }

            $user = User::create([
                'name'          => $request->nama,
                'username'      => $request->username,
                'email'         => $request->email,
                'no_hp'         => $request->no_hp,
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
                'foto'          => $fotoPath,
                'password'      => $request->password, // hashed by cast
                'role'          => 'member',
            ]);

            // dibuat otomatis oleh booted() di User model
            $member = $user->member;

            // jaga-jaga kalau event tidak jalan
            if (!$member) {
                $member = Member::create([
                    'user_id'        => $user->id,
                    'tanggal_daftar' => now()->toDateString(),
                ]);
            }

            $member->update([
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_akhir' => $request->tanggal_akhir,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.members.index')
                ->with('success', 'Member baru berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            if (!empty($fotoPath) && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan member: ' . $th->getMessage());
        }
    }

    public function update(Request $request, Member $member)
    {
        $user = $member->user;

        $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:users,username,' . ($user->id ?? 'NULL')],
            'email'         => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . ($user->id ?? 'NULL')],
            'no_hp'         => ['nullable', 'string', 'max:20', 'unique:users,no_hp,' . ($user->id ?? 'NULL')],

            'alamat'        => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'],

            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],

            'foto'          => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            $member->update([
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_akhir' => $request->tanggal_akhir,
            ]);

            if ($user) {
                $dataUser = [
                    'name'          => $request->nama,
                    'username'      => $request->username,
                    'email'         => $request->email,
                    'no_hp'         => $request->no_hp,
                    'alamat'        => $request->alamat,
                    'jenis_kelamin' => $request->jenis_kelamin,
                ];

                if ($request->hasFile('foto')) {
                    if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                        Storage::disk('public')->delete($user->foto);
                    }
                    $dataUser['foto'] = $request->file('foto')->store('users', 'public');
                }

                $user->update($dataUser);
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

    public function destroy(Member $member)
    {
        try {
            // Schema baru: foto ada di users, jadi tidak ada file yang dihapus di sini
            $member->delete();

            return redirect()
                ->route('admin.members.index')
                ->with('success', 'Member berhasil dihapus.');
        } catch (\Throwable $th) {
            return back()
                ->with('error', 'Tidak dapat menghapus member: ' . $th->getMessage());
        }
    }

    public function create() {}
    public function show(Member $member) {}
    public function edit(Member $member) {}
}

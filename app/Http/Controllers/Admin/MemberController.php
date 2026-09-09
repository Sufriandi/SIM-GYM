<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Services\ImageUploadService;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', ''); // aktif | belum_aktif | expired | ''
        $sort   = (string) $request->input('sort', 'name_asc'); // name_asc|name_desc|daftar_newest|daftar_oldest

        // Base query (Dioptimalkan urutan select agar ID tidak bentrok)
        $query = Member::query()
            ->with(['user' => fn($q) => $q->withTrashed()])
            ->select('members.*')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->whereNull('members.deleted_at');

        // SEARCH
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.no_hp', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        /**
         * OPTIMASI PERFORMA: Menggunakan Cache agar tidak mengecek Schema database berkali-kali
         */
        $periodeTable = Cache::rememberForever('transaksi_memberships_table', function () {
            foreach (['transaksi_memberships', 'transaksi_membership'] as $tbl) {
                if (Schema::hasTable($tbl)) {
                    return $tbl;
                }
            }
            return null;
        });

        if ($periodeTable) {
            $today = now()->toDateString();

            /**
             * Subquery: latest transaksi per BUYER (per member)
             */
            $latest = DB::table($periodeTable)
                ->select('buyer_member_id', DB::raw('MAX(id) as latest_id'))
                ->whereNull('canceled_at')
                ->groupBy('buyer_member_id');

            $query->leftJoinSub($latest, 'tm_latest', function ($join) {
                $join->on('tm_latest.buyer_member_id', '=', 'members.id');
            });

            $query->leftJoin(DB::raw($periodeTable . ' as tm'), function ($join) {
                $join->on('tm.id', '=', 'tm_latest.latest_id');
            });

            $query->leftJoin('paket_memberships as tm_paket', 'tm_paket.id', '=', 'tm.paket_id');

            $query->addSelect([
                'tm.id as latest_tm_id',
                'tm.tanggal_mulai as latest_tm_tanggal_mulai',
                'tm.tanggal_akhir as latest_tm_tanggal_akhir',
                'tm_paket.nama as latest_tm_paket_nama',
            ]);

            // FILTER STATUS
            if ($status === 'aktif') {
                $query->whereNotNull('tm.id')
                    ->whereDate('tm.tanggal_mulai', '<=', $today)
                    ->whereDate('tm.tanggal_akhir', '>=', $today);
            } elseif ($status === 'expired') {
                $query->whereNotNull('tm.id')
                    ->whereDate('tm.tanggal_akhir', '<', $today);
            } elseif ($status === 'belum_aktif') {
                $query->where(function ($q) use ($today) {
                    $q->whereNull('tm.id')
                        ->orWhereDate('tm.tanggal_mulai', '>', $today);
                });
            }
        }

        // SORT
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('users.name', 'desc');
                break;
            case 'daftar_newest':
                $query->orderBy('members.tanggal_daftar', 'desc');
                break;
            case 'daftar_oldest':
                $query->orderBy('members.tanggal_daftar', 'asc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('users.name', 'asc');
                break;
        }

        $members = $query->paginate(20)->appends([
            'search' => $search,
            'status' => $status,
            'sort'   => $sort,
        ]);

        $todayStr = now()->toDateString();
        $members->getCollection()->transform(function ($member) use ($todayStr) {
            if (!empty($member->latest_tm_id)) {
                $mulai = (string) ($member->latest_tm_tanggal_mulai ?? '');
                $akhir = (string) ($member->latest_tm_tanggal_akhir ?? '');

                if ($mulai !== '' && $akhir !== '' && $mulai <= $todayStr && $akhir >= $todayStr) {
                    $member->computed_status = 'aktif';
                    $member->computed_paket = $member->latest_tm_paket_nama;
                } elseif ($akhir !== '' && $akhir < $todayStr) {
                    $member->computed_status = 'expired';
                    $member->computed_paket = $member->latest_tm_paket_nama;
                } else {
                    $member->computed_status = 'belum_aktif';
                    $member->computed_paket = $member->latest_tm_paket_nama;
                }
            } else {
                $member->computed_status = 'belum_aktif';
                $member->computed_paket = null;
            }
            return $member;
        });

        return view('admin.members.index', compact('members', 'search', 'status', 'sort'));
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
            'foto'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        DB::beginTransaction();
        $fotoPath = null;

        try {
            // OPTIMASI GAMBAR: Resize HD dan Convert ke WebP
            if ($request->hasFile('foto')) {
                $fotoPath = ImageUploadService::uploadAsWebp(
                    $request->file('foto'),
                    'users',
                    null,
                    800,
                    85
                );
            }

            $user = User::create([
                'name'          => $request->nama,
                'username'      => $request->username,
                'email'         => $request->email,
                'no_hp'         => $request->no_hp,
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
                'foto'          => $fotoPath,
                'password'      => $request->password,
                'role'          => 'member',
            ]);

            $member = $user->member;

            if (! $member) {
                $member = Member::create([
                    'user_id'        => $user->id,
                    'tanggal_daftar' => now()->toDateString(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.members.index')
                ->with('success', 'Member baru berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            if (!empty($fotoPath)) {
                ImageUploadService::delete($fotoPath);
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
            'foto'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        DB::beginTransaction();

        try {
            if ($user) {
                $dataUser = [
                    'name'          => $request->nama,
                    'username'      => $request->username,
                    'email'         => $request->email,
                    'no_hp'         => $request->no_hp,
                    'alamat'        => $request->alamat,
                    'jenis_kelamin' => $request->jenis_kelamin,
                ];

                // OPTIMASI GAMBAR: Update dengan Resize HD dan Convert ke WebP
                if ($request->hasFile('foto')) {
                    $dataUser['foto'] = ImageUploadService::uploadAsWebp(
                        $request->file('foto'),
                        'users',
                        $user->foto,
                        800,
                        85
                    );
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
        DB::beginTransaction();

        try {
            $user = $member->user;
            $member->delete();

            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()
                ->route('admin.members.index')
                ->with('success', 'Member dan akun pengguna berhasil dinonaktifkan (Soft Delete).');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Gagal menonaktifkan member: ' . $th->getMessage());
        }
    }

    public function restore($id)
    {
        $member = Member::withTrashed()->findOrFail($id);

        DB::beginTransaction();

        try {
            $member->restore();

            if ($member->user) {
                $member->user->restore();
            }

            DB::commit();

            return back()->with('success', 'Member dan akun berhasil diaktifkan kembali.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulihkan member: ' . $th->getMessage());
        }
    }

    public function create() {}
    public function show(Member $member) {}
    public function edit(Member $member) {}
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PaketMembership;
use App\Models\TransaksiMembership;
use App\Models\TransaksiMembershipMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiMembershipController extends Controller
{
    public function index(Request $request)
    {
        $q     = trim((string) $request->get('q', ''));
        $tipe  = $request->get('tipe');           // single|double|triple|null
        $sort  = $request->get('sort', 'newest'); // newest|oldest
        $jenis = $request->get('jenis');          // pembayaran|kompensasi|null (UI)

        $query = TransaksiMembership::with([
            'buyer.user',
            'creator',
            'paket',
            'participants.member.user',
        ]);

        // Search
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereHas('buyer.user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('buyer.user', fn($u) => $u->where('username', 'like', "%{$q}%"))
                    ->orWhereHas('paket', fn($p) => $p->where('nama', 'like', "%{$q}%"));
            });
        }

        // Filter tipe paket
        if (!empty($tipe)) {
            $query->whereHas('paket', fn($p) => $p->where('tipe', $tipe));
        }

        // Filter jenis transaksi (normalisasi input -> konstanta)
        $jenisNormalized = match ($jenis) {
            'pembayaran', TransaksiMembership::JENIS_PEMBAYARAN => TransaksiMembership::JENIS_PEMBAYARAN,
            'kompensasi', TransaksiMembership::JENIS_KOMPENSASI => TransaksiMembership::JENIS_KOMPENSASI,
            default => null,
        };

        if ($jenisNormalized !== null) {
            $query->where('jenis_transaksi', $jenisNormalized);
        }

        // Sort
        $isOldest = ($sort === 'oldest');
        $query->orderBy('tanggal_transaksi', $isOldest ? 'asc' : 'desc')
            ->orderBy('id', $isOldest ? 'asc' : 'desc');

        $transaksis = $query->paginate(20)->withQueryString();

        // Data dropdown modal
        $members = Member::query()
            ->with('user')
            ->whereHas('user', fn($u) => $u->where('role', 'member'))
            ->join('users', 'users.id', '=', 'members.user_id')
            ->select('members.*')
            ->orderBy('users.name')
            ->get();

        $paketList = PaketMembership::orderBy('tipe')->orderBy('durasi')->get();

        return view('admin.transaksi_membership.index', compact(
            'transaksis',
            'members',
            'paketList',
            'q',
            'tipe',
            'sort',
            'jenis'
        ));
    }

    /**
     * PEMBAYARAN ONLY
     */
    public function store(Request $request)
    {
        [$participantIds, $metodePembayaran] = $this->normalizeInputs($request);

        $validated = $request->merge([
            'participant_ids'   => $participantIds,
            'metode_pembayaran' => $metodePembayaran,
        ])->validate([
            'buyer_member_id'   => ['required', 'exists:members,id'],
            'paket_id'          => ['required', 'exists:paket_memberships,id'],
            'tanggal_transaksi' => ['nullable', 'date'],

            'metode_pembayaran' => ['required', 'in:cash,transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],

            'participant_ids'   => ['nullable', 'array'],
            'participant_ids.*' => ['integer', 'distinct', 'exists:members,id'],
        ]);

        $paket         = PaketMembership::findOrFail($validated['paket_id']);
        $buyerId        = (int) $validated['buyer_member_id'];
        $participantIds = $validated['participant_ids'] ?? [];

        $err = $this->validateParticipantsBusiness($paket->tipe, $buyerId, $participantIds);
        if ($err) {
            return back()->withErrors($err)->withInput();
        }

        DB::transaction(function () use ($validated, $paket, $buyerId, $participantIds) {

            $tanggalTransaksi = isset($validated['tanggal_transaksi'])
                ? Carbon::parse($validated['tanggal_transaksi'])
                : now();

            $transDay = $tanggalTransaksi->copy()->startOfDay();

            $allMemberIds = array_values(array_unique(array_merge([$buyerId], $participantIds)));

            // penting: lock agar tidak tabrakan kalau input bersamaan
            $lastEnd = $this->getLastEndDateForMembers($allMemberIds, null, true);

            $tanggalMulai = $lastEnd
                ? ($lastEnd->gte($transDay) ? $lastEnd->copy()->addDay() : $transDay)
                : $transDay;

            $tanggalAkhir = $tanggalMulai->copy()->addDays(((int) $paket->durasi) - 1);

            $trx = TransaksiMembership::create([
                'buyer_member_id'   => $buyerId,
                'created_by'        => auth()->id(),
                'paket_id'          => $paket->id,
                'tanggal_transaksi' => $tanggalTransaksi,
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_akhir'     => $tanggalAkhir,
                'jenis_transaksi'   => TransaksiMembership::JENIS_PEMBAYARAN,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'keterangan'        => $validated['keterangan'] ?? null,
            ]);

            TransaksiMembershipMember::create([
                'transaksi_membership_id' => $trx->id,
                'member_id'               => $buyerId,
                'role'                    => 'primary',
            ]);

            foreach ($participantIds as $pid) {
                TransaksiMembershipMember::create([
                    'transaksi_membership_id' => $trx->id,
                    'member_id'               => (int) $pid,
                    'role'                    => 'member',
                ]);
            }
        });

        return redirect()
            ->route('admin.transaksi_membership.index')
            ->with('success', 'Membership berhasil ditambahkan (pembayaran).');
    }

    /**
     * KOMPENSASI MANUAL (BONUS / TRIAL)
     */
    public function storeKompensasiManual(Request $request)
    {
        $validated = $request->validate([
            'member_id'         => ['required', 'exists:members,id'],
            'paket_id'          => ['required', 'exists:paket_memberships,id'],
            'jumlah_hari'       => ['required', 'integer', 'min:1', 'max:365'],
            'tanggal_transaksi' => ['nullable', 'date'],
            'keterangan'        => ['nullable', 'string', 'max:255'],
        ]);

        $memberId   = (int) $validated['member_id'];
        $paketId    = (int) $validated['paket_id'];
        $jumlahHari = (int) $validated['jumlah_hari'];

        DB::transaction(function () use ($validated, $memberId, $paketId, $jumlahHari) {

            $tanggalTransaksi = isset($validated['tanggal_transaksi'])
                ? Carbon::parse($validated['tanggal_transaksi'])
                : now();

            $transDay = $tanggalTransaksi->copy()->startOfDay();

            $lastEnd = $this->getLastEndDateForMembers([$memberId], null, true);

            $tanggalMulai = $lastEnd
                ? ($lastEnd->gte($transDay) ? $lastEnd->copy()->addDay() : $transDay)
                : $transDay;

            $tanggalAkhir = $tanggalMulai->copy()->addDays($jumlahHari - 1);

            $trx = TransaksiMembership::create([
                'buyer_member_id'   => $memberId,
                'created_by'        => auth()->id(),
                'paket_id'          => $paketId,
                'tanggal_transaksi' => $tanggalTransaksi,
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_akhir'     => $tanggalAkhir,
                'jenis_transaksi'   => TransaksiMembership::JENIS_KOMPENSASI,
                'metode_pembayaran' => null,
                'keterangan'        => $validated['keterangan'] ?: "Bonus/Trial admin {$jumlahHari} hari",
            ]);

            TransaksiMembershipMember::create([
                'transaksi_membership_id' => $trx->id,
                'member_id'               => $memberId,
                'role'                    => 'primary',
            ]);
        });

        return redirect()
            ->route('admin.transaksi_membership.index')
            ->with('success', 'Bonus/Trial berhasil ditambahkan (kompensasi).');
    }

    public function show(TransaksiMembership $transaksiMembership)
    {
        $transaksiMembership->load(['buyer.user', 'creator', 'paket', 'participants.member.user']);

        return view('admin.transaksi_membership.modals.detail', [
            'trx' => $transaksiMembership,
        ]);
    }

    public function destroy(TransaksiMembership $transaksiMembership)
    {
        DB::transaction(function () use ($transaksiMembership) {

            if ($transaksiMembership->canceled_at) {
                abort(403, 'Transaksi ini sudah dibatalkan sebelumnya.');
            }

            if ($transaksiMembership->jenis_transaksi === TransaksiMembership::JENIS_KOMPENSASI) {
                abort(403, 'Transaksi kompensasi tidak bisa dibatalkan dari modul transaksi membership.');
            }

            $today = Carbon::today();
            $mulai = Carbon::parse($transaksiMembership->tanggal_mulai)->startOfDay();

            if (!$today->lt($mulai)) {
                abort(403, 'Hanya transaksi yang belum aktif yang bisa dibatalkan.');
            }

            $memberIds = TransaksiMembershipMember::where('transaksi_membership_id', $transaksiMembership->id)
                ->pluck('member_id')
                ->unique()
                ->values()
                ->all();

            if (empty($memberIds)) {
                $memberIds = [(int) $transaksiMembership->buyer_member_id];
            }

            $adaLebihBaru = TransaksiMembership::query()
                ->valid()
                ->where('id', '!=', $transaksiMembership->id)
                ->where(function ($w) use ($transaksiMembership) {
                    $w->whereDate('tanggal_mulai', '>', $transaksiMembership->tanggal_mulai)
                        ->orWhere(function ($w2) use ($transaksiMembership) {
                            $w2->whereDate('tanggal_mulai', '=', $transaksiMembership->tanggal_mulai)
                                ->where(function ($w3) use ($transaksiMembership) {
                                    $w3->where('tanggal_transaksi', '>', $transaksiMembership->tanggal_transaksi)
                                        ->orWhere(function ($w4) use ($transaksiMembership) {
                                            $w4->where('tanggal_transaksi', '=', $transaksiMembership->tanggal_transaksi)
                                                ->where('id', '>', $transaksiMembership->id);
                                        });
                                });
                        });
                })
                ->where(function ($q) use ($memberIds) {
                    $q->whereIn('buyer_member_id', $memberIds)
                        ->orWhereHas('participants', fn($p) => $p->whereIn('member_id', $memberIds));
                })
                ->exists();

            if ($adaLebihBaru) {
                abort(403, 'Tidak bisa membatalkan transaksi ini karena ada transaksi membership yang lebih baru untuk salah satu anggota yang terlibat.');
            }

            $transaksiMembership->update(['canceled_at' => now()]);
        });

        return redirect()
            ->route('admin.transaksi_membership.index')
            ->with('success', 'Transaksi membership berhasil dibatalkan.');
    }

    /**
     * $lock=true dipakai saat create agar aman dari race condition.
     */
    private function getLastEndDateForMembers(array $memberIds, ?int $excludeTrxId = null, bool $lock = false): ?Carbon
    {
        $q = TransaksiMembership::query()
            ->valid()
            ->where(function ($w) use ($memberIds) {
                $w->whereIn('buyer_member_id', $memberIds)
                    ->orWhereHas('participants', fn($p) => $p->whereIn('member_id', $memberIds));
            });

        if ($excludeTrxId) {
            $q->where('id', '!=', $excludeTrxId);
        }

        if ($lock) {
            $q->lockForUpdate();
        }

        $lastEnd = $q->max('tanggal_akhir');
        return $lastEnd ? Carbon::parse($lastEnd)->startOfDay() : null;
    }

    private function normalizeInputs(Request $request): array
    {
        $raw = (array) $request->input('participant_ids', []);
        $participantIds = array_values(array_unique(array_map(
            fn($v) => (int) $v,
            array_filter($raw, fn($v) => filled($v))
        )));

        $metode = $request->input('metode_pembayaran');
        $metodePembayaran = filled($metode) ? $metode : null;

        return [$participantIds, $metodePembayaran];
    }

    private function validateParticipantsBusiness(string $tipe, int $buyerId, array $participantIds): ?array
    {
        if (in_array($buyerId, $participantIds, true)) {
            return ['participant_ids' => 'Peserta tambahan tidak boleh sama dengan pembeli (primary).'];
        }

        $expectedAdditional = match ($tipe) {
            'single' => 0,
            'double' => 1,
            'triple' => 2,
            default  => 0,
        };

        if (count($participantIds) !== $expectedAdditional) {
            return ['participant_ids' => "Jumlah peserta tambahan harus {$expectedAdditional} untuk paket {$tipe}."];
        }

        return null;
    }
}

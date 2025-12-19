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
        $q    = $request->get('q');
        $tipe = $request->get('tipe');
        $sort = $request->get('sort', 'newest');

        $query = TransaksiMembership::with([
            'buyer.user',
            'creator',
            'paket',
            'participants.member.user',
        ]);

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->whereHas('buyer.user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('paket', fn($p) => $p->where('nama', 'like', "%{$q}%"));
            });
        }

        if ($tipe) {
            $query->whereHas('paket', fn($p) => $p->where('tipe', $tipe));
        }

        $query->orderBy('tanggal_transaksi', $sort === 'oldest' ? 'asc' : 'desc');

        $transaksis = $query->paginate(20)->withQueryString();

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
            'sort'
        ));
    }

    public function store(Request $request)
    {
        // Normalisasi input agar robust:
        // - participant_ids: buang "" (option kosong), cast int
        // - metode_pembayaran: kalau "" jadikan null (lebih aman)
        [$participantIds, $metodePembayaran] = $this->normalizeInputs($request);

        $validated = $request->merge([
            'participant_ids'   => $participantIds,
            'metode_pembayaran' => $metodePembayaran,
        ])->validate([
            'buyer_member_id'   => ['required', 'exists:members,id'],
            'paket_id'          => ['required', 'exists:paket_memberships,id'],
            'tanggal_transaksi' => ['nullable', 'date'],

            'jenis_transaksi'   => ['required', 'in:sale,adjustment'],
            'metode_pembayaran' => ['nullable', 'in:cash,transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],

            'participant_ids'   => ['nullable', 'array'],
            'participant_ids.*' => ['integer', 'distinct', 'exists:members,id'],
        ]);

        if ($validated['jenis_transaksi'] === 'sale' && empty($validated['metode_pembayaran'])) {
            return back()
                ->withErrors(['metode_pembayaran' => 'Metode pembayaran wajib untuk transaksi sale.'])
                ->withInput();
        }

        $paket = PaketMembership::findOrFail($validated['paket_id']);
        $buyerId = (int) $validated['buyer_member_id'];
        $participantIds = $validated['participant_ids'] ?? [];

        // Validasi bisnis peserta (tegas sesuai tipe paket)
        $err = $this->validateParticipantsBusiness($paket->tipe, $buyerId, $participantIds);
        if ($err) {
            return back()->withErrors($err)->withInput();
        }

        DB::transaction(function () use ($validated, $paket, $buyerId, $participantIds) {
            $tanggalTransaksi = isset($validated['tanggal_transaksi'])
                ? Carbon::parse($validated['tanggal_transaksi'])
                : now();

            $transDay = $tanggalTransaksi->copy()->startOfDay();

            // AUTO-EXTEND: cari tanggal_akhir terakhir dari transaksi yg melibatkan buyer
            $lastEnd = TransaksiMembership::notCanceled()
                ->where(function ($q) use ($buyerId) {
                    $q->where('buyer_member_id', $buyerId)
                        ->orWhereHas('participants', fn($p) => $p->where('member_id', $buyerId));
                })
                ->orderByDesc('tanggal_akhir')
                ->value('tanggal_akhir');

            if ($lastEnd) {
                $lastEnd = Carbon::parse($lastEnd)->startOfDay();
                $tanggalMulai = $lastEnd->gte($transDay) ? $lastEnd->copy()->addDay() : $transDay;
            } else {
                $tanggalMulai = $transDay;
            }

            $tanggalAkhir = $tanggalMulai->copy()->addDays(((int) $paket->durasi) - 1);

            $trx = TransaksiMembership::create([
                'buyer_member_id'   => $buyerId,
                'created_by'        => auth()->id(),
                'paket_id'          => $paket->id,
                'tanggal_transaksi' => $tanggalTransaksi,
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_akhir'     => $tanggalAkhir,
                'jenis_transaksi'   => $validated['jenis_transaksi'],
                'metode_pembayaran' => $validated['metode_pembayaran'] ?? null,
                'keterangan'        => $validated['keterangan'] ?? null,
            ]);

            // Insert peserta:
            // 1) buyer selalu dibuat baris primary
            TransaksiMembershipMember::create([
                'transaksi_membership_id' => $trx->id,
                'member_id'               => $buyerId,
                'role'                    => 'primary',
            ]);

            // 2) tambahan sesuai paket
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
            ->with('success', 'Transaksi membership berhasil ditambahkan.');
    }

    public function show(TransaksiMembership $transaksiMembership)
    {
        $transaksiMembership->load(['buyer.user', 'creator', 'paket', 'participants.member.user']);

        return view('admin.transaksi_membership.modals.detail', [
            'trx' => $transaksiMembership,
        ]);
    }

    public function update(Request $request, TransaksiMembership $transaksiMembership)
    {
        // edit hanya kalau belum aktif & belum canceled
        $today = Carbon::today();
        $mulai = Carbon::parse($transaksiMembership->tanggal_mulai)->startOfDay();

        if ($transaksiMembership->canceled_at || ! $today->lt($mulai)) {
            abort(403, 'Transaksi yang sudah aktif/expired atau sudah dibatalkan tidak bisa diedit.');
        }

        [$participantIds, $metodePembayaran] = $this->normalizeInputs($request);

        $validated = $request->merge([
            'participant_ids'   => $participantIds,
            'metode_pembayaran' => $metodePembayaran,
        ])->validate([
            'paket_id'          => ['required', 'exists:paket_memberships,id'],
            'tanggal_transaksi' => ['nullable', 'date'],
            'jenis_transaksi'   => ['required', 'in:sale,adjustment'],
            'metode_pembayaran' => ['nullable', 'in:cash,transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],

            'participant_ids'   => ['nullable', 'array'],
            'participant_ids.*' => ['integer', 'distinct', 'exists:members,id'],
        ]);

        if ($validated['jenis_transaksi'] === 'sale' && empty($validated['metode_pembayaran'])) {
            return back()
                ->withErrors(['metode_pembayaran' => 'Metode pembayaran wajib untuk transaksi sale.'])
                ->withInput();
        }

        $paket = PaketMembership::findOrFail($validated['paket_id']);
        $buyerId = (int) $transaksiMembership->buyer_member_id;
        $participantIds = $validated['participant_ids'] ?? [];

        $err = $this->validateParticipantsBusiness($paket->tipe, $buyerId, $participantIds);
        if ($err) {
            return back()->withErrors($err)->withInput();
        }

        DB::transaction(function () use ($validated, $transaksiMembership, $paket, $buyerId, $participantIds) {
            $tanggalTransaksi = isset($validated['tanggal_transaksi'])
                ? Carbon::parse($validated['tanggal_transaksi'])
                : Carbon::parse($transaksiMembership->tanggal_transaksi);

            $transDay = $tanggalTransaksi->copy()->startOfDay();

            // lastEnd selain transaksi ini
            $lastEnd = TransaksiMembership::notCanceled()
                ->where('id', '!=', $transaksiMembership->id)
                ->where(function ($q) use ($buyerId) {
                    $q->where('buyer_member_id', $buyerId)
                        ->orWhereHas('participants', fn($p) => $p->where('member_id', $buyerId));
                })
                ->orderByDesc('tanggal_akhir')
                ->value('tanggal_akhir');

            if ($lastEnd) {
                $lastEnd = Carbon::parse($lastEnd)->startOfDay();
                $tanggalMulai = $lastEnd->gte($transDay) ? $lastEnd->copy()->addDay() : $transDay;
            } else {
                $tanggalMulai = $transDay;
            }

            $tanggalAkhir = $tanggalMulai->copy()->addDays(((int) $paket->durasi) - 1);

            $transaksiMembership->update([
                'paket_id'          => $paket->id,
                'tanggal_transaksi' => $tanggalTransaksi,
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_akhir'     => $tanggalAkhir,
                'jenis_transaksi'   => $validated['jenis_transaksi'],
                'metode_pembayaran' => $validated['metode_pembayaran'] ?? null,
                'keterangan'        => $validated['keterangan'] ?? null,
            ]);

            // refresh peserta: delete lalu insert ulang
            TransaksiMembershipMember::where('transaksi_membership_id', $transaksiMembership->id)->delete();

            TransaksiMembershipMember::create([
                'transaksi_membership_id' => $transaksiMembership->id,
                'member_id'               => $buyerId,
                'role'                    => 'primary',
            ]);

            foreach ($participantIds as $pid) {
                TransaksiMembershipMember::create([
                    'transaksi_membership_id' => $transaksiMembership->id,
                    'member_id'               => (int) $pid,
                    'role'                    => 'member',
                ]);
            }
        });

        return redirect()
            ->route('admin.transaksi_membership.index')
            ->with('success', 'Transaksi membership berhasil diperbarui.');
    }

    public function destroy(TransaksiMembership $transaksiMembership)
    {
        DB::transaction(function () use ($transaksiMembership) {
            if ($transaksiMembership->canceled_at) {
                abort(403, 'Transaksi ini sudah dibatalkan sebelumnya.');
            }

            $today = Carbon::today();
            $mulai = Carbon::parse($transaksiMembership->tanggal_mulai)->startOfDay();

            if (! $today->lt($mulai)) {
                abort(403, 'Hanya transaksi membership yang belum aktif yang bisa dibatalkan.');
            }

            // Cegah cancel jika ada transaksi lebih baru utk buyer (primary)
            $adaLebihBaru = TransaksiMembership::notCanceled()
                ->where('buyer_member_id', $transaksiMembership->buyer_member_id)
                ->where('id', '!=', $transaksiMembership->id)
                ->whereDate('tanggal_mulai', '>', $transaksiMembership->tanggal_mulai)
                ->exists();

            if ($adaLebihBaru) {
                abort(403, 'Tidak bisa membatalkan transaksi ini karena ada transaksi membership yang lebih baru.');
            }

            $transaksiMembership->update(['canceled_at' => now()]);
        });

        return redirect()
            ->route('admin.transaksi_membership.index')
            ->with('success', 'Transaksi membership berhasil dibatalkan.');
    }

    /**
     * Normalisasi input agar tidak kena bug:
     * - participant_ids: buang empty string "", cast int, unique (biar request bersih)
     * - metode_pembayaran: "" => null
     */
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

    /**
     * Validasi bisnis peserta harus sesuai tipe paket.
     * single: 0 tambahan
     * double: 1 tambahan
     * triple: 2 tambahan
     */
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

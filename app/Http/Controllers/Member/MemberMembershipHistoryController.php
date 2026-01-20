<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\TransaksiMembership;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MemberMembershipHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) abort(403, 'Silakan login terlebih dahulu.');

        /** @var Member|null $member */
        $member = $user->member ?? null;
        if (! $member) abort(403, 'Akun ini belum terhubung dengan data member.');

        $today = Carbon::today()->startOfDay();

        $q      = trim((string) $request->get('q', ''));
        $status = $request->get('status'); // active|expired|upcoming|null
        $sort   = $request->get('sort', 'latest'); // latest|oldest

        $hasMembershipTables =
            Schema::hasTable('transaksi_memberships') &&
            Schema::hasTable('transaksi_membership_members');

        if (! $hasMembershipTables) {
            // Safety fallback
            return view('member.membership.history', [
                'user'      => $user,
                'member'    => $member,
                'q'         => $q,
                'status'    => $status,
                'sort'      => $sort,
                'stats'     => ['total' => 0, 'active' => 0, 'expired' => 0, 'upcoming' => 0],
                'rows'      => collect(),
                'paginator' => null,
            ]);
        }

        // Base query transaksi untuk member ini
        $query = TransaksiMembership::with([
                'paket',
                // ambil pivot participant khusus member ini
                'participants' => fn ($p) => $p->where('member_id', (int) $member->id),
            ])
            ->when(method_exists(TransaksiMembership::class, 'valid'), fn ($q) => $q->valid())
            ->whereHas('participants', fn ($p) => $p->where('member_id', (int) $member->id));

        // Search (no_nota atau nama paket)
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('no_nota', 'like', "%{$q}%")
                  ->orWhereHas('paket', fn ($p) => $p->where('nama', 'like', "%{$q}%"));
            });
        }

        // Filter status berdasar pivot tanggal_mulai/tanggal_akhir
        if ($status === 'active') {
            $query->whereHas('participants', function ($p) use ($member, $today) {
                $p->where('member_id', (int) $member->id)
                  ->whereDate('tanggal_mulai', '<=', $today->toDateString())
                  ->whereDate('tanggal_akhir', '>=', $today->toDateString());
            });
        } elseif ($status === 'expired') {
            $query->whereHas('participants', function ($p) use ($member, $today) {
                $p->where('member_id', (int) $member->id)
                  ->whereDate('tanggal_akhir', '<', $today->toDateString());
            });
        } elseif ($status === 'upcoming') {
            $query->whereHas('participants', function ($p) use ($member, $today) {
                $p->where('member_id', (int) $member->id)
                  ->whereDate('tanggal_mulai', '>', $today->toDateString());
            });
        }

        // Sort
        if ($sort === 'oldest') {
            $query->orderBy('tanggal_transaksi', 'asc')->orderBy('id', 'asc');
        } else {
            $query->orderBy('tanggal_transaksi', 'desc')->orderBy('id', 'desc');
        }

        $paginator = $query->paginate(10)->withQueryString();

        // Statistik ringkas
        $baseStats = TransaksiMembership::query()
            ->when(method_exists(TransaksiMembership::class, 'valid'), fn ($q) => $q->valid())
            ->whereHas('participants', fn ($p) => $p->where('member_id', (int) $member->id));

        $stats = [
            'total'    => (clone $baseStats)->count(),
            'active'   => (clone $baseStats)->whereHas('participants', function ($p) use ($member, $today) {
                $p->where('member_id', (int) $member->id)
                  ->whereDate('tanggal_mulai', '<=', $today->toDateString())
                  ->whereDate('tanggal_akhir', '>=', $today->toDateString());
            })->count(),
            'expired'  => (clone $baseStats)->whereHas('participants', function ($p) use ($member, $today) {
                $p->where('member_id', (int) $member->id)
                  ->whereDate('tanggal_akhir', '<', $today->toDateString());
            })->count(),
            'upcoming' => (clone $baseStats)->whereHas('participants', function ($p) use ($member, $today) {
                $p->where('member_id', (int) $member->id)
                  ->whereDate('tanggal_mulai', '>', $today->toDateString());
            })->count(),
        ];

        // Transform untuk kebutuhan UI (durasi/progress/status)
        $rows = collect($paginator->items())->map(function ($trx) use ($member, $today) {
            $pivot = $trx->participants->first(); // karena sudah difilter per-member

            $mulai = $pivot?->tanggal_mulai ? Carbon::parse($pivot->tanggal_mulai)->startOfDay() : null;
            $akhir = $pivot?->tanggal_akhir ? Carbon::parse($pivot->tanggal_akhir)->endOfDay() : null;

            $statusKey = 'unknown';
            $statusLabel = 'Tidak diketahui';

            $totalDurasi = 0;
            $hariTerpakai = 0;
            $sisaHari = 0;
            $percent = 0;

            if ($mulai && $akhir) {
                $akhirDay = $akhir->copy()->startOfDay();
                $mulaiDay = $mulai->copy()->startOfDay();

                $totalDurasi = $mulaiDay->diffInDays($akhirDay) + 1;

                if ($today->between($mulaiDay, $akhirDay, true)) {
                    $statusKey = 'active';
                    $statusLabel = 'Aktif';

                    $hariTerpakai = $mulaiDay->diffInDays($today) + 1;
                    $sisaHari     = $today->diffInDays($akhirDay) + 1;

                    $hariTerpakai = max(0, min($totalDurasi, $hariTerpakai));
                    $sisaHari     = max(0, min($totalDurasi, $sisaHari));
                } elseif ($today->lt($mulaiDay)) {
                    $statusKey = 'upcoming';
                    $statusLabel = 'Belum Mulai';

                    $hariTerpakai = 0;
                    $sisaHari     = $totalDurasi;
                } else {
                    $statusKey = 'expired';
                    $statusLabel = 'Berakhir';

                    $hariTerpakai = $totalDurasi;
                    $sisaHari     = 0;
                }

                $percent = $totalDurasi > 0
                    ? (int) max(0, min(100, round(($hariTerpakai / $totalDurasi) * 100)))
                    : 0;
            }

            return [
                'trx'          => $trx,
                'pivot'        => $pivot,
                'mulai'        => $mulai,
                'akhir'        => $akhir,
                'status_key'   => $statusKey,
                'status_label' => $statusLabel,

                'total_durasi' => $totalDurasi,
                'hari_terpakai'=> $hariTerpakai,
                'sisa_hari'    => $sisaHari,
                'percent'      => $percent,
            ];
        });

        return view('member.membership.history', [
            'user'      => $user,
            'member'    => $member,
            'q'         => $q,
            'status'    => $status,
            'sort'      => $sort,
            'stats'     => $stats,
            'rows'      => $rows,
            'paginator' => $paginator,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRead;
use App\Models\NotificationDelete;
use App\Models\Produk;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MemberNotifikasiController extends Controller
{
    /**
     * Query notifikasi yang visible untuk member tertentu.
     * - target=all: broadcast setelah user daftar
     * - target=user: personal
     * - exclude: yang sudah di-hide user tsb
     */
    private function visibleQuery(int $userId, Carbon $userCreatedAt)
    {
        return Notification::query()
            ->where(function ($q) use ($userId, $userCreatedAt) {
                $q->where(function ($all) use ($userCreatedAt) {
                    $all->where('target', 'all')
                        ->where('created_at', '>=', $userCreatedAt);
                })
                ->orWhere(function ($u) use ($userId) {
                    $u->where('target', 'user')
                      ->where('target_user_id', $userId);
                });
            })
            ->whereNotIn('id', function ($sub) use ($userId) {
                $sub->select('notification_id')
                    ->from('notification_deletes')
                    ->where('user_id', $userId);
            })
            ->orderByDesc('created_at');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $search = trim((string) $request->query('search', ''));
        $filter = (string) $request->query('filter', 'all'); // all | unread

        $query = $this->visibleQuery($userId, $userCreatedAt);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($filter === 'unread') {
            $query->whereNotIn('id', function ($sub) use ($userId) {
                $sub->select('notification_id')
                    ->from('notification_reads')
                    ->where('user_id', $userId);
            });
        }

        $notifications = $query->paginate(12)->withQueryString();

        $ids = collect($notifications->items())->pluck('id')->values();

        $readIds = $ids->isEmpty()
            ? []
            : NotificationRead::where('user_id', $userId)
                ->whereIn('notification_id', $ids)
                ->pluck('notification_id')
                ->all();

        // count untuk badge navbar (kalau kamu pass dari layout)
        $unreadCount = $this->visibleQuery($userId, $userCreatedAt)
            ->whereNotIn('id', function ($sub) use ($userId) {
                $sub->select('notification_id')
                    ->from('notification_reads')
                    ->where('user_id', $userId);
            })
            ->count();

        return view('member.notifikasi.index', [
            'pageTitle'          => 'Notifikasi',
            'notifications'      => $notifications,
            'readIds'            => $readIds,
            'search'             => $search,
            'filter'             => $filter,
            'notificationCount'  => $unreadCount,
        ]);
    }

    public function readOne(Request $request, int $id)
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $notif = $this->visibleQuery($userId, $userCreatedAt)->where('id', $id)->firstOrFail();

        NotificationRead::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $userId],
            ['read_at' => Carbon::now()]
        );

        return back();
    }

    public function readAll(Request $request)
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $ids = $this->visibleQuery($userId, $userCreatedAt)->pluck('id');
        $now = Carbon::now();

        foreach ($ids as $nid) {
            NotificationRead::updateOrCreate(
                ['notification_id' => $nid, 'user_id' => $userId],
                ['read_at' => $now]
            );
        }

        return back();
    }

    public function hideOne(Request $request, int $id)
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $notif = $this->visibleQuery($userId, $userCreatedAt)->where('id', $id)->firstOrFail();

        NotificationDelete::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $userId],
            ['deleted_at' => Carbon::now()]
        );

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function hideAll(Request $request)
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $ids = $this->visibleQuery($userId, $userCreatedAt)->pluck('id');

        foreach ($ids as $nid) {
            NotificationDelete::updateOrCreate(
                ['notification_id' => $nid, 'user_id' => $userId],
                ['deleted_at' => Carbon::now()]
            );
        }

        return back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }

    /**
     * Klik item notifikasi:
     * - mark read
     * - redirect sesuai data['route']
     */
    public function go(Request $request, int $id)
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $notif = $this->visibleQuery($userId, $userCreatedAt)->where('id', $id)->firstOrFail();

        // mark read
        NotificationRead::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $userId],
            ['read_at' => Carbon::now()]
        );

        $data = is_array($notif->data) ? $notif->data : (array) $notif->data;
        $key  = strtolower((string) ($data['route'] ?? ''));

        /**
         * ROUTE KEY yang disarankan untuk kamu pakai di observer/service:
         * - member_produk_detail (data: slug OR produk_id + produk_nama)
         * - member_produk_index
         * - member_coach_index
         * - member_coach_detail (data: slug OR coach_id + coach_nama)
         * - member_izin_latihan_index
         * - member_izin_latihan_detail (data: izin_id)
         * - member_kehadiran_index
         */
        return match ($key) {
            // ===== PRODUK =====
            'member_produk_index' => redirect()->route('member.produk_gym.index'),

            'member_produk_detail' => $this->redirectProdukDetail($data),

            // fallback lama (jika observer lama masih kirim route=produk_detail)
            'produk_detail' => $this->redirectProdukDetail($data),

            // ===== COACH =====
            'member_coach_index' => redirect()->route('member.coach.index'),

            'member_coach_detail' => $this->redirectCoachDetail($data),

            // ===== IZIN LATIHAN =====
            'member_izin_latihan_index' => redirect()->route('member.izin_latihan.index'),
            'member_izin_latihan_detail' => redirect()->route('member.izin_latihan.detail', [
                'id' => (int)($data['izin_id'] ?? 0)
            ]),

            // ===== KEHADIRAN =====
            'member_kehadiran_index' => redirect()->route('member.kehadiran.index'),

            // default: balik ke halaman notifikasi
            default => redirect()->route('member.notifikasi.index'),
        };
    }

    private function redirectProdukDetail(array $data)
    {
        // Prioritas: slug (id-nama) jika sudah disimpan
        $slug = (string)($data['slug'] ?? '');
        if ($slug !== '') {
            return redirect()->route('member.produk_gym.show', $slug);
        }

        // Kalau hanya ada produk_id + produk_nama
        $produkId = (int)($data['produk_id'] ?? 0);
        $produkNama = (string)($data['produk_nama'] ?? '');

        if ($produkId > 0) {
            $nama = $produkNama !== '' ? $produkNama : 'produk';
            $finalSlug = $produkId . '-' . Str::slug($nama);

            return redirect()->route('member.produk_gym.show', $finalSlug);
        }

        // Last resort: cari produk dari DB jika ada id
        if ($produkId > 0) {
            $p = Produk::find($produkId);
            if ($p) {
                $finalSlug = $p->id . '-' . Str::slug($p->nama ?? 'produk');
                return redirect()->route('member.produk_gym.show', $finalSlug);
            }
        }

        return redirect()->route('member.produk_gym.index');
    }

    private function redirectCoachDetail(array $data)
    {
        $slug = (string)($data['slug'] ?? '');
        if ($slug !== '') {
            return redirect()->route('member.coach.show', $slug);
        }

        $coachId = (int)($data['coach_id'] ?? 0);
        $coachNama = (string)($data['coach_nama'] ?? '');

        if ($coachId > 0) {
            $nama = $coachNama !== '' ? $coachNama : 'coach';
            $finalSlug = $coachId . '-' . Str::slug($nama);
            return redirect()->route('member.coach.show', $finalSlug);
        }

        // Last resort: DB
        if ($coachId > 0) {
            $c = Coach::find($coachId);
            if ($c) {
                $finalSlug = $c->id . '-' . Str::slug($c->nama ?? 'coach');
                return redirect()->route('member.coach.show', $finalSlug);
            }
        }

        return redirect()->route('member.coach.index');
    }

    /**
     * REALTIME via polling:
     * GET /member/notifikasi/poll?since_id=123
     */
    public function poll(Request $request)
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $sinceId = (int) $request->query('since_id', 0);

        $unreadCount = $this->visibleQuery($userId, $userCreatedAt)
            ->whereNotIn('id', function ($sub) use ($userId) {
                $sub->select('notification_id')
                    ->from('notification_reads')
                    ->where('user_id', $userId);
            })
            ->count();

        $q = $this->visibleQuery($userId, $userCreatedAt);

        if ($sinceId > 0) {
            $q->where('id', '>', $sinceId);
        }

        $items = $q->take(10)->get(['id', 'title', 'body', 'type', 'data', 'created_at']);

        $ids = $items->pluck('id')->values();

        $readIds = $ids->isEmpty()
            ? []
            : NotificationRead::where('user_id', $userId)
                ->whereIn('notification_id', $ids)
                ->pluck('notification_id')
                ->all();

        $payload = $items->map(function ($n) use ($readIds) {
            $data = is_array($n->data) ? $n->data : (array) $n->data;

            return [
                'id'         => $n->id,
                'title'      => $n->title ?? 'Notifikasi',
                'body'       => $n->body ?? '-',
                'type'       => $n->type,
                'route'      => $data['route'] ?? null,
                'created_at' => $n->created_at?->toIsoString(),
                'is_read'    => in_array($n->id, $readIds, true),
                'go_url'     => route('member.notifikasi.go', $n->id),
            ];
        });

        $maxId = $items->max('id') ?? $sinceId;

        return response()->json([
            'success'     => true,
            'unreadCount' => $unreadCount,
            'items'       => $payload,
            'maxId'       => $maxId,
            'serverTime'  => now()->toIsoString(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRead;
use App\Models\NotificationDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminNotifikasiController extends Controller
{
    /**
     * Query notifikasi untuk admin:
     * - target=user, target_user_id=adminId
     * - exclude yang di-hide admin
     */
    private function baseQuery(int $adminId)
    {
        return Notification::query()
            ->where('target', 'user')
            ->where('target_user_id', $adminId)
            ->whereNotIn('id', function ($sub) use ($adminId) {
                $sub->select('notification_id')
                    ->from('notification_deletes')
                    ->where('user_id', $adminId);
            })
            ->orderByDesc('created_at');
    }

    public function index(Request $request)
    {
        $adminId = (int) $request->user()->id;

        $search = trim((string) $request->query('search', ''));
        $filter = (string) $request->query('filter', 'all'); // all | unread

        $query = $this->baseQuery($adminId);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($filter === 'unread') {
            $query->whereNotIn('id', function ($sub) use ($adminId) {
                $sub->select('notification_id')
                    ->from('notification_reads')
                    ->where('user_id', $adminId);
            });
        }

        $notifications = $query->paginate(12)->withQueryString();

        // read status untuk page ini
        $ids = collect($notifications->items())->pluck('id')->values();

        $readIds = NotificationRead::where('user_id', $adminId)
            ->whereIn('notification_id', $ids)
            ->pluck('notification_id')
            ->all();

        // hitung total/unread (untuk chip)
        $totalCount = $this->baseQuery($adminId)->count();

        $unreadCount = $this->baseQuery($adminId)
            ->whereNotIn('id', function ($sub) use ($adminId) {
                $sub->select('notification_id')
                    ->from('notification_reads')
                    ->where('user_id', $adminId);
            })
            ->count();

        return view('admin.notifikasi.index', [
            'pageTitle'      => 'Notifikasi',
            'notifications'  => $notifications,
            'readIds'        => $readIds,
            'totalCount'     => $totalCount,
            'unreadCount'    => $unreadCount,
            'search'         => $search,
            'filter'         => $filter,
        ]);
    }

    public function readOne(Request $request, int $id)
    {
        $adminId = (int) $request->user()->id;

        $notif = $this->baseQuery($adminId)->where('id', $id)->firstOrFail();

        NotificationRead::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $adminId],
            ['read_at' => Carbon::now()]
        );

        return back();
    }

    public function readAll(Request $request)
    {
        $adminId = (int) $request->user()->id;

        $ids = $this->baseQuery($adminId)->pluck('id');
        $now = Carbon::now();

        foreach ($ids as $nid) {
            NotificationRead::updateOrCreate(
                ['notification_id' => $nid, 'user_id' => $adminId],
                ['read_at' => $now]
            );
        }

        return back();
    }

    public function hideOne(Request $request, int $id)
    {
        $adminId = (int) $request->user()->id;

        $notif = $this->baseQuery($adminId)->where('id', $id)->firstOrFail();

        NotificationDelete::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $adminId],
            ['deleted_at' => Carbon::now()]
        );

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function hideAll(Request $request)
    {
        $adminId = (int) $request->user()->id;

        $ids = $this->baseQuery($adminId)->pluck('id');

        foreach ($ids as $nid) {
            NotificationDelete::updateOrCreate(
                ['notification_id' => $nid, 'user_id' => $adminId],
                ['deleted_at' => Carbon::now()]
            );
        }

        return back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }

    /**
     * Klik item: mark read lalu redirect ke halaman terkait.
     * Penting: hanya READ, tidak HIDE.
     */
    public function go(Request $request, int $id)
    {
        $adminId = (int) $request->user()->id;

        $notif = $this->baseQuery($adminId)->where('id', $id)->firstOrFail();

        NotificationRead::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $adminId],
            ['read_at' => Carbon::now()]
        );

        $data = is_array($notif->data) ? $notif->data : (array) $notif->data;
        $key  = strtolower((string)($data['route'] ?? ''));

        return match ($key) {
            'admin_izin_latihan'         => redirect()->route('admin.izin_latihan.index'),
            'admin_transaksi_produk'     => redirect()->route('admin.transaksi_produk.index'),
            'admin_transaksi_membership' => redirect()->route('admin.transaksi_membership.index'),
            default                      => redirect()->route('admin.notifikasi.index'),
        };
    }

    /**
     * REALTIME (tanpa Reverb) via polling:
     * GET /admin/notifikasi/poll?since_id=123
     */
    public function poll(Request $request)
    {
        $adminId = (int) $request->user()->id;
        $sinceId = (int) $request->query('since_id', 0);

        // unread count global
        $unreadCount = $this->baseQuery($adminId)
            ->whereNotIn('id', function ($sub) use ($adminId) {
                $sub->select('notification_id')
                    ->from('notification_reads')
                    ->where('user_id', $adminId);
            })
            ->count();

        // ambil notifikasi (kalau since_id=0 => latest 10 untuk dropdown)
        $q = $this->baseQuery($adminId);

        if ($sinceId > 0) {
            $q->where('id', '>', $sinceId);
        }

        $items = $q->take(10)->get(['id', 'title', 'body', 'type', 'data', 'created_at']);

        $ids = $items->pluck('id')->values();

        $readIds = $ids->isEmpty()
            ? []
            : NotificationRead::where('user_id', $adminId)
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
                'go_url'     => route('admin.notifikasi.go', $n->id),
            ];
        });

        $maxId = $items->max('id') ?? $sinceId;

        return response()->json([
            'success'     => true,
            'unreadCount' => $unreadCount,
            'items'       => $payload,   // <- untuk dropdown
            'maxId'       => $maxId,
            'serverTime'  => now()->toIsoString(),
        ]);
    }
}

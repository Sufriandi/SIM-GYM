<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRead;
use App\Models\NotificationDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * Query notifikasi yang "visible" untuk user tertentu.
     * - target=all: hanya notifikasi broadcast yang dibuat setelah user mendaftar (>= user.created_at)
     * - target=user: notifikasi personal user tsb
     * - exclude: notifikasi yang sudah di-hide (notification_deletes) oleh user tsb
     */
    private function visibleQuery(int $userId, Carbon $userCreatedAt)
    {
        return Notification::query()
            ->where(function ($q) use ($userId, $userCreatedAt) {
                // BROADCAST (ALL) hanya setelah user daftar
                $q->where(function ($all) use ($userCreatedAt) {
                    $all->where('target', 'all')
                        ->where('created_at', '>=', $userCreatedAt);
                })
                // PERSONAL (USER)
                ->orWhere(function ($u) use ($userId) {
                    $u->where('target', 'user')
                      ->where('target_user_id', $userId);
                });
            })
            // exclude yang sudah dihapus (hide) oleh user ini
            ->whereNotIn('id', function ($sub) use ($userId) {
                $sub->select('notification_id')
                    ->from('notification_deletes')
                    ->where('user_id', $userId);
            })
            ->orderByDesc('created_at');
    }

    // GET /api/notifications
    public function index(Request $request)
    {
        $user = $request->user(); // auth:sanctum
        $userId = $user->id;

        // penting: pakai created_at user untuk filter broadcast lama
        $userCreatedAt = Carbon::parse($user->created_at);

        $perPage = $request->integer('per_page', 30);

        $items = $this->visibleQuery($userId, $userCreatedAt)
            ->paginate($perPage);

        $notifIds = collect($items->items())->pluck('id')->values();

        // read status untuk notif yang tampil
        $reads = NotificationRead::where('user_id', $userId)
            ->whereIn('notification_id', $notifIds)
            ->pluck('read_at', 'notification_id');

        $data = collect($items->items())->map(function ($n) use ($reads) {
            return [
                'id'         => $n->id,
                'title'      => $n->title,
                'body'       => $n->body,
                'type'       => $n->type,
                'data'       => $n->data,
                'created_at' => $n->created_at?->toISOString(),
                'is_read'    => $reads->has($n->id),
                'read_at'    => $reads->get($n->id)?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    // POST /api/notifications/{id}/read
    public function markRead(Request $request, $id)
    {
        $user = $request->user();
        $userId = $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        // hanya boleh mark read kalau notif memang visible untuk user ini
        $notif = $this->visibleQuery($userId, $userCreatedAt)
            ->where('id', $id)
            ->firstOrFail();

        NotificationRead::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $userId],
            ['read_at' => Carbon::now()]
        );

        return response()->json(['success' => true]);
    }

    // POST /api/notifications/read-all
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        $notifIds = $this->visibleQuery($userId, $userCreatedAt)
            ->pluck('id');

        $now = Carbon::now();

        foreach ($notifIds as $nid) {
            NotificationRead::updateOrCreate(
                ['notification_id' => $nid, 'user_id' => $userId],
                ['read_at' => $now]
            );
        }

        return response()->json(['success' => true]);
    }

    // DELETE /api/notifications/{id}
    // Hide notifikasi untuk user ini saja (bukan delete global)
    public function deleteOne(Request $request, $id)
    {
        $user = $request->user();
        $userId = $user->id;
        $userCreatedAt = Carbon::parse($user->created_at);

        // hanya boleh hide kalau notif visible untuk user ini
        $notif = $this->visibleQuery($userId, $userCreatedAt)
            ->where('id', $id)
            ->firstOrFail();

        NotificationDelete::updateOrCreate(
            ['notification_id' => $notif->id, 'user_id' => $userId],
            ['deleted_at' => Carbon::now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi dihapus',
        ]);
    }
}

<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public int $adminId;
    public array $payload;

    public function __construct(Notification $notification, int $adminId)
    {
        $this->adminId = $adminId;

        $this->payload = [
            'id'         => $notification->id,
            'title'      => $notification->title,
            'body'       => $notification->body,
            'type'       => $notification->type,
            'data'       => $notification->data,
            'created_at' => optional($notification->created_at)->toISOString(),
        ];
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.' . $this->adminId);
    }

    public function broadcastAs(): string
    {
        return 'admin.notification.created';
    }
}

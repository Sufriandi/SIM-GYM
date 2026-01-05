<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelete extends Model
{
    protected $table = 'notification_deletes';

    protected $fillable = [
        'notification_id',
        'user_id',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}

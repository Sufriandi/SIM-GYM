<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'target',          // all | user
        'target_user_id',  // nullable
        'type',            // produk|coach|izin|absen|membership|transaksi|info
        'data',            // json
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}

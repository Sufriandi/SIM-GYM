<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LatihanHarian extends Model
{
    use HasFactory;

    protected $table = 'latihan_harian';

    protected $fillable = [
        'tanggal',
        'nama',
        'kategori',
        'total',
        'metode_pembayaran',
        'keterangan',
        'created_by',
        'canceled_at',
    ];

    protected $casts = [
        'tanggal'     => 'datetime',
        'total'       => 'integer',
        'canceled_at' => 'datetime',
        'created_by'  => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeNotCanceled($q)
    {
        return $q->whereNull('canceled_at');
    }
}

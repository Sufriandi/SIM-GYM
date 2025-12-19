<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiProduk extends Model
{
    use HasFactory;

    protected $table = 'transaksi_produks';

    protected $fillable = [
        'no_nota',
        'tanggal_transaksi',
        'buyer_member_id',
        'created_by',
        'metode_pembayaran',
        'total',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'total' => 'integer',
    ];

    /**
     * Pembeli (member). Jika Member memakai SoftDeletes dan ingin tetap tampil,
     * tambahkan ->withTrashed() di relasi ini.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'buyer_member_id')
            ->withTrashed(); // aktifkan jika Member pakai SoftDeletes
    }

    /**
     * Petugas yang membuat transaksi (admin/kasir).
     * Jika User pakai SoftDeletes dan ingin tetap tampil, gunakan ->withTrashed().
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')
            ->withTrashed(); // aktifkan jika User pakai SoftDeletes
    }

    /**
     * Item-item dalam transaksi.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransaksiProdukItem::class, 'transaksi_produk_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'canceled_at', // NEW
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'canceled_at'       => 'datetime', // NEW
        'total'             => 'integer',
    ];

    /**
     * Pembeli (member).
     * Jika Member memakai SoftDeletes, kita keep record-nya tetap bisa terbaca.
     */
    public function buyer(): BelongsTo
    {
        $rel = $this->belongsTo(Member::class, 'buyer_member_id');

        return self::modelUsesSoftDeletes(Member::class) ? $rel->withTrashed() : $rel;
    }

    /**
     * Petugas yang membuat transaksi (admin/kasir).
     */
    public function creator(): BelongsTo
    {
        $rel = $this->belongsTo(User::class, 'created_by');

        return self::modelUsesSoftDeletes(User::class) ? $rel->withTrashed() : $rel;
    }

    /**
     * Item-item dalam transaksi.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransaksiProdukItem::class, 'transaksi_produk_id');
    }

    /**
     * Scopes untuk laporan.
     */
    public function scopeActive($q)
    {
        return $q->whereNull('canceled_at');
    }

    public function scopeCanceled($q)
    {
        return $q->whereNotNull('canceled_at');
    }

    public function getIsCanceledAttribute(): bool
    {
        return !is_null($this->canceled_at);
    }

    private static function modelUsesSoftDeletes(string $modelClass): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);
    }
}

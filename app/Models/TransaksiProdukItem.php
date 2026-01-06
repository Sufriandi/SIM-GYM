<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiProdukItem extends Model
{
    use HasFactory;

    protected $table = 'transaksi_produk_items';

    protected $fillable = [
        'transaksi_produk_id',
        'produk_id',
        'qty',
        'harga_satuan',
    ];

    protected $casts = [
        'transaksi_produk_id' => 'integer',
        'produk_id'           => 'integer',
        'qty'                 => 'integer',
        'harga_satuan'        => 'integer',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiProduk::class, 'transaksi_produk_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function getSubtotalAttribute(): int
    {
        return (int) $this->qty * (int) $this->harga_satuan;
    }
}

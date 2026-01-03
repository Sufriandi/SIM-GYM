<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produks';

    protected $fillable = [
        'foto',
        'nama',
        'kategori',
        'harga',
        'stok',
        'deskripsi',
    ];

    protected $casts = [
        'harga'      => 'decimal:0',
        'stok'       => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function penjualan()
    {
        return $this->hasMany(PenjualanProduk::class, 'produk_id');
        // atau TransaksiProdukItem::class kalau struktur Anda pakai transaksi_produk_item
    }

    public function stok()
    {
        return $this->hasMany(StokProduk::class, 'produk_id');
    }
}

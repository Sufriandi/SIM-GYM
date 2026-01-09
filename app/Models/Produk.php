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
        'harga'      => 'integer',   // MINIMAL: rupiah tanpa desimal
        'stok'       => 'integer',
        'deleted_at' => 'datetime',
    ];

    // Relasi yang sesuai dengan transaksi_produk_items
    public function transaksiItems()
    {
        return $this->hasMany(TransaksiProdukItem::class, 'produk_id');
    }

    // Biarkan method lama agar tidak ganggu alur (opsional)
    public function stok()
    {
        return $this->hasMany(StokProduk::class, 'produk_id');
    }
}

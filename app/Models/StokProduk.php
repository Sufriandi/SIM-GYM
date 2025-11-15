<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StokProduk extends Model
{
    use HasFactory;

    protected $table = 'stok_produk';

    protected $fillable = [
        'produk_id',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    /**
     * Relasi ke Produk (Many-to-One)
     * Setiap stok entry milik satu produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}

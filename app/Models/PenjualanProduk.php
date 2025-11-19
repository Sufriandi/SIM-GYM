<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenjualanProduk extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'jumlah',
        'total_harga',
        'metode_pembayaran',
        'keterangan',
        'tanggal_transaksi',
    ];

    // Relasi ke Produk (Many-to-One)
    public function produk()
    {
        // Model Produk
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
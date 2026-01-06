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

    protected $casts = [
        'produk_id' => 'integer',
        'jumlah'    => 'integer', // aman di PHP 64-bit; jika Anda ingin, bisa dihapus saja
        'tanggal'   => 'date',    // SESUAI migration (date)
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}

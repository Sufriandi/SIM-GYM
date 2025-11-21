<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User; // Import Model User untuk relasi member

class PenjualanProduk extends Model
{
    use HasFactory;

    // Pastikan nama tabel diatur jika tidak mengikuti konvensi plural (penjualan_produks)
    // protected $table = 'penjualan_produks'; 

    protected $fillable = [
        'produk_id',
        'member_id', // <-- KOLOM BARU DITAMBAHKAN KE $fillable
        'jumlah',
        'total_harga',
        'metode_pembayaran',
        'keterangan',
        'tanggal_transaksi',
    ];

    /**
     * Relasi ke Produk (Many-to-One)
     * Setiap transaksi penjualan milik satu produk.
     */
    public function produk()
    {
        // Model Produk
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Relasi ke Member (User) (Many-to-One)
     * member_id merujuk ke id di tabel users.
     */
    public function member(): BelongsTo // <-- RELASI MEMBER DITAMBAHKAN
    {
        // Asumsi Model User adalah representasi dari member Anda
        return $this->belongsTo(User::class, 'member_id'); 
    }
}
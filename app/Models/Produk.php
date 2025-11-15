<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kategori',
        'harga',
        'stok',
        'deskripsi', // diperbaiki ejaannya
    ];

    /**
     * Relasi ke PenjualanProduk (One-to-Many)
     */
    public function penjualan()
    {
        return $this->hasMany(PenjualanProduk::class, 'produk_id');
    }

    /**
     * Relasi ke StokProduk (One-to-Many)
     */
    public function stok()
    {
        return $this->hasMany(StokProduk::class, 'produk_id');
    }
}

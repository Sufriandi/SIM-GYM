<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LatihanHarian extends Model
{
    use HasFactory;

    protected $table = 'latihan_harian';

    protected $fillable = [
        'tanggal',
        'nama',
        'kategori',
        'harga',
        'metode_pembayaran',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}

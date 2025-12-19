<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaketMembership extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'tipe',
        'durasi',
        'harga',
        'deskripsi',
    ];

    public function transaksiMemberships()
    {
        return $this->hasMany(TransaksiMembership::class, 'paket_id');
    }
}

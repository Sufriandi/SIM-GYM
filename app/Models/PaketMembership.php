<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
        'durasi',
        'harga',
        'deskripsi',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class, 'paket_id');
    }
}

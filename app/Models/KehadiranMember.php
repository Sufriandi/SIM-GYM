<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KehadiranMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'absensi_periode_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'ip_address',
        'device_info',
        'is_valid',
    ];

    protected $casts = [
        'tanggal'  => 'date',
        'is_valid' => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function absensiPeriode()
    {
        return $this->belongsTo(AbsensiPeriode::class);
    }
}

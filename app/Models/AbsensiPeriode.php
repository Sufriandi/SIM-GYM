<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiPeriode extends Model
{
    use HasFactory;

    protected $table = 'absensi_periodes';

    protected $fillable = [
        'tipe_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'kode_qr',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai'  => 'date',
        'tanggal_selesai'=> 'date',
    ];

    public function kehadiranMembers()
    {
        return $this->hasMany(KehadiranMember::class, 'absensi_periode_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}

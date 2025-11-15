<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Member;

class IzinLatihan extends Model
{
    use HasFactory;

    protected $table = 'izin_latihan';

    protected $fillable = [
        'member_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'bukti_alasan',
        'status',
    ];

    // Relasi: Izin dimiliki oleh 1 member
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    // Accessor: Hitung jumlah hari otomatis
    public function getDurasiAttribute()
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return null;
        }

        return \Carbon\Carbon::parse($this->tanggal_mulai)
            ->diffInDays(\Carbon\Carbon::parse($this->tanggal_selesai)) + 1;
    }
}

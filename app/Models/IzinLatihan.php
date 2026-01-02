<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Member;
use Carbon\Carbon;

class IzinLatihan extends Model
{
    use HasFactory;

    protected $table = 'izin_latihan';

    protected $fillable = [
        'member_id',             // ⬅️ penting
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'bukti_alasan',
        'status',
        'durasi_izin_disetujui',
        'keterangan_admin',
        'tanggal_persetujuan',
    ];

    protected $casts = [
        'tanggal_mulai'       => 'date',
        'tanggal_selesai'     => 'date',
        'tanggal_persetujuan' => 'datetime',
    ];

    // Relasi: izin dimiliki oleh 1 member via member_id
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id')
            ->withTrashed();
    }

    // Accessor: Hitung jumlah hari otomatis
    public function getDurasiAttribute()
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return null;
        }

        return Carbon::parse($this->tanggal_mulai)
            ->diffInDays(Carbon::parse($this->tanggal_selesai)) + 1;
    }
}

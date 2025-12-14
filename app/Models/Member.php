<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal_daftar',
        'tanggal_mulai',
        'tanggal_akhir',
    ];

    /**
     * Relasi: member dimiliki oleh satu user.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Relasi: Member memiliki banyak izin latihan // (pakai member_id, bukan user_id)

    public function izinLatihan()
    {
        return $this->hasMany(\App\Models\IzinLatihan::class, 'member_id', 'id');
    }

    /**
     * Semua baris kehadiran absensi yang dimiliki member ini. 
     */
    public function kehadiranMember()
    {
        return $this->hasMany(\App\Models\KehadiranMember::class, 'member_id');
    }

    /** 
     * Sesi / periode absensi yang pernah diikuti member. 
     */
    public function periodesAbsensi()
    {
        return $this->belongsToMany(
            \App\Models\AbsensiPeriode::class,
            'kehadiran_absensi',
            'member_id',
            'sesi_absensi_id'
        )->withTimestamps()
            ->withPivot(['waktu_absen', 'status', 'device_info', 'keterangan']);
    }

    /** 
     * Relasi ke semua transaksi membership milik member ini. 
     */
    public function memberships()
    {
        return $this->hasMany(\App\Models\Membership::class);
    }

    // ================== HELPER MEMBERSHIP AKTIF ==================

    public function scopeMembershipAktif($query)
    {
        $today = Carbon::today();

        return $query
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_akhir')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_akhir', '>=', $today);
    }

    public function getMembershipAktifAttribute(): bool
    {
        if (!$this->tanggal_mulai || !$this->tanggal_akhir) {
            return false;
        }

        $mulai = Carbon::parse($this->tanggal_mulai)->startOfDay();
        $akhir = Carbon::parse($this->tanggal_akhir)->endOfDay();
        $today = Carbon::today();

        return $today->betweenIncluded($mulai, $akhir);
    }
}

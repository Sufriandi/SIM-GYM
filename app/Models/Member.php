<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\IzinLatihan;
use App\Models\KehadiranMember;
use App\Models\AbsensiPeriode;
use App\Models\Membership;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'alamat',
        'jenis_kelamin',
        'tanggal_daftar',
        'tanggal_mulai',
        'tanggal_akhir',
        'foto',
        'status',
        'qr_code_token',
    ];

    // ================= RELASI UTAMA =================

    // Relasi: Member dimiliki oleh 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Member memiliki banyak izin latihan
    public function izinLatihan()
    {
        return $this->hasMany(IzinLatihan::class, 'user_id', 'user_id');
    }

    /**
     * Semua baris kehadiran absensi yang dimiliki member ini.
     */
    public function kehadiranMember()
    {
        return $this->hasMany(KehadiranMember::class, 'member_id');
    }

    /**
     * Sesi / periode absensi yang pernah diikuti member.
     */
    public function periodesAbsensi()
    {
        return $this->belongsToMany(
            AbsensiPeriode::class,
            'kehadiran_absensi',
            'member_id',
            'sesi_absensi_id'
        )->withTimestamps()
         ->withPivot(['waktu_absen', 'status', 'device_info', 'keterangan']);
    }

    /**
     * Relasi ke semua transaksi membership milik member ini.
     * (kalau sewaktu-waktu perlu dicek riwayatnya).
     */
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    // ================== HELPER MEMBERSHIP AKTIF ==================

    /**
     * Scope: hanya member yang membership-nya sedang aktif hari ini.
     * Dipakai kalau kamu perlu query daftar member aktif.
     */
    public function scopeMembershipAktif($query)
    {
        $today = Carbon::today();

        return $query
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_akhir')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_akhir', '>=', $today);
    }

    /**
     * Accessor: $member->membership_aktif (boolean)
     *
     * TRUE  jika hari ini di antara tanggal_mulai & tanggal_akhir.
     * FALSE jika belum ada membership / sudah lewat.
     *
     * Nilai tanggal_mulai & tanggal_akhir sudah selalu
     * disinkronkan oleh MembershipController.
     */
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

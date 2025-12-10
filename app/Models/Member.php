<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\IzinLatihan;
use App\Models\KehadiranAbsensi;
use App\Models\SesiAbsensi;

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
        'status',
        'qr_code_token',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($member) {
    //         // set tanggal daftar
    //         if (empty($member->tanggal_daftar)) {
    //             $member->tanggal_daftar = now()->toDateString();
    //         }

    //         // hash password dulu
    //         if (!empty($member->password)) {
    //             $member->password = Hash::make($member->password);
    //         }
    //     });

    //     static::created(function ($member) {

    //         // Cek apakah user dengan email ini sudah ada
    //         $existingUser = User::where('email', $member->email)->first();

    //         if ($existingUser) {
    //             $member->updateQuietly([
    //                 'user_id' => $existingUser->id
    //             ]);
    //             return;
    //         }

    //         // Kalau tidak ada, buat user baru
    //         $user = User::create([
    //             'name'     => $member->nama,
    //             'email'    => $member->email,
    //             'password' => $member->password, // sudah di-hash
    //             'role'     => 'user',
    //         ]);

    //         // Update kolom user_id tanpa memicu event lagi
    //         $member->updateQuietly([
    //             'user_id' => $user->id,
    //         ]);
    //     });
    // }
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
     * Sesi absensi yang pernah diikuti member (via tabel kehadiran_absensi).
     */
    public function sesiAbsensi()
    {
        return $this->belongsToMany(
            SesiAbsensi::class,
            'kehadiran_absensi',
            'member_id',
            'sesi_absensi_id'
        )->withTimestamps()
            ->withPivot(['waktu_absen', 'status', 'device_info', 'keterangan']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // Tambahkan ini
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // Tambahkan SoftDeletes di sini
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'no_hp',
        'alamat',
        'jenis_kelamin',
        'foto',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->role !== 'member') return;

            $user->member()->firstOrCreate(
                ['user_id' => $user->id],
                ['tanggal_daftar' => now()->toDateString()]
            );
        });
    }

    /**
     * Relasi: user punya satu member.
     * Ditambahkan withTrashed agar jika User dihapus, data Member tetap bisa diakses lewat User.
     */
    public function member()
    {
        return $this->hasOne(\App\Models\Member::class)->withTrashed();
    }
}

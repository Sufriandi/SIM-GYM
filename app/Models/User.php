<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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


    /**
     * Otomatis buat record members hanya jika role = member.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->role !== 'member') return;

            $user->member()->firstOrCreate(
                ['user_id' => $user->id],
                ['tanggal_daftar' => now()->toDateString()]
            );
        });

        // Opsional: jika suatu saat role berubah jadi member, buat member-nya.
        // static::updated(function (User $user) {
        //     if ($user->wasChanged('role') && $user->role === 'member') {
        //         $user->member()->firstOrCreate(
        //             ['user_id' => $user->id],
        //             ['tanggal_daftar' => now()]
        //         );
        //     }
        // });
    }

    /**
     * Relasi: user punya satu member (hanya untuk role=member).
     */
    public function member()
    {
        return $this->hasOne(\App\Models\Member::class);
    }
}

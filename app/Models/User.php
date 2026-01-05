<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
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
            // tetap aman dipakai
            'password'          => 'hashed',
        ];
    }

    /**
     * Pastikan semua penyimpanan password (termasuk seeder yang plaintext)
     * akan di-hash.
     */
    public function setPasswordAttribute($value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        // Jika belum hash/harus rehash => hash-kan
        if (Hash::needsRehash($value)) {
            $this->attributes['password'] = Hash::make($value);
            return;
        }

        // Jika sudah hash, simpan apa adanya
        $this->attributes['password'] = $value;
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

    public function member()
    {
        return $this->hasOne(\App\Models\Member::class)->withTrashed();
    }
}

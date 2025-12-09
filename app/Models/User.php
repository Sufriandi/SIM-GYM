<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Member;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // WAJIB: Tambahkan HasApiTokens

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'no_hp',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
    
    /**
     * Sinkronisasi: Membuat record Member baru setelah User dibuat.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Menggunakan namespace lengkap untuk Member
            \App\Models\Member::create([ 
                'user_id' => $user->id,
                'nama' => $user->name,
                'tanggal_daftar' => now(),
            ]);
        });
    }

    // Relasi: User memiliki 1 member
    public function member()
    {
        return $this->hasOne(\App\Models\Member::class);
    }
}
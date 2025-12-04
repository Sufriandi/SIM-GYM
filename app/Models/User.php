<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Member;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
            'password' => 'hashed',
        ];
    }
    /**
     * Sinkronisasi: Membuat record Member baru setelah User dibuat.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            Member::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'tanggal_daftar' => now(),
            ]);
        });
    }


    // Relasi: User memiliki 1 member
    public function member()
    {
        return $this->hasOne(Member::class);
    }
}

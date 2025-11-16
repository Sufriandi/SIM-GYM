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
        'email',
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
            // Kolom unik seperti 'no_hp', 'alamat', 'tanggal_mulai', dll., diabaikan di sini
            // dan akan diisi di proses Onboarding.
            Member::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'username' => explode('@', $user->email)[0],
                'email' => $user->email,
                'password' => $user->password,
                'status' => 'aktif',
                'tanggal_daftar' => now()->toDateString(), // Mengisi tanggal daftar default
                // Kolom lain otomatis NULL karena tidak dimasukkan
            ]);
        });
    }

    // Relasi: User memiliki 1 member
    public function member()
    {
        // Terhubung ke 'user_id' di tabel 'members'
        return $this->hasOne(Member::class, 'user_id');
    }
}

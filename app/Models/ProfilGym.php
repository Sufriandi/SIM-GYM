<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilGym extends Model
{
    protected $table = 'profil_gym';

    protected $fillable = [
        'nama',
        'deskripsi',
        'logo',

        'instagram',
        'tiktok',
        'youtube',
        'facebook',
        'whatsapp',

        'lokasi',
        'jam_buka',
        'jam_tutup',

        'email_kontak',
        'favicon',
        'hero_image',
        'maps_url',
    ];
}

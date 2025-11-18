<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coach extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
        'deskripsi',
        'foto',
    ];
}

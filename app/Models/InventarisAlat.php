<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventarisAlat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'foto',
        'deskripsi',
        'kondisi',
    ];
}

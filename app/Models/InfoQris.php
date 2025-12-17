<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfoQris extends Model
{
    use SoftDeletes;

    protected $table = 'info_qris';

    protected $fillable = [
        'nama_qris',
        'path_gambar',
        'keterangan',
    ];
}

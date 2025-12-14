<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfoRekening extends Model
{
    use SoftDeletes;

    protected $table = 'info_rekening';

    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'nama_pemilik',
    ];
}

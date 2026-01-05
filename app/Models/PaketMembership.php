<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class PaketMembership extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'tipe',
        'durasi',
        'harga',
        'deskripsi',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function transaksiMemberships()
    {
        return $this->hasMany(TransaksiMembership::class, 'paket_id');
    }

    /**
     * Paket yang boleh ditampilkan & dibeli oleh member (marketplace).
     */
    public function scopePublic(Builder $q): Builder
    {
        return $q->where('is_public', true);
    }
}

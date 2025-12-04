<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'paket_id',
        'member_id',
        'tanggal_transaksi',
        'metode_pembayaran',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function paket()
    {
        return $this->belongsTo(PaketMembership::class, 'paket_id');
    }

    public function groupMembers()
    {
        return $this->hasMany(MembershipGroup::class);
    }
}

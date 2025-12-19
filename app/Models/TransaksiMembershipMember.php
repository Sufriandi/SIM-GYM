<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiMembershipMember extends Model
{
    use HasFactory;

    protected $table = 'transaksi_membership_members';

    protected $fillable = [
        'transaksi_membership_id',
        'member_id',
        'role',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiMembership::class, 'transaksi_membership_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function scopePrimary($q)
    {
        return $q->where('role', 'primary');
    }
}

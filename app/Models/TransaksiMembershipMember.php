<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TransaksiMembershipMember extends Model
{
    use HasFactory;

    protected $table = 'transaksi_membership_members';

    protected $fillable = [
        'transaksi_membership_id',
        'member_id',
        'role',
        'tanggal_mulai',
        'tanggal_akhir',
    ];

    protected $casts = [
        'transaksi_membership_id' => 'integer',
        'member_id'               => 'integer',
        'tanggal_mulai'           => 'date',
        'tanggal_akhir'           => 'date',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiMembership::class, 'transaksi_membership_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function scopePrimary(Builder $q): Builder
    {
        return $q->where('role', 'primary');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TransaksiMembership extends Model
{
    use HasFactory;

    protected $table = 'transaksi_memberships';

    protected $fillable = [
        'buyer_member_id',
        'created_by',
        'paket_id',
        'tanggal_transaksi',
        'tanggal_mulai',
        'tanggal_akhir',
        'jenis_transaksi',
        'metode_pembayaran',
        'keterangan',
        'canceled_at',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'tanggal_mulai'     => 'date',
        'tanggal_akhir'     => 'date',
        'canceled_at'       => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(Member::class, 'buyer_member_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paket()
    {
        return $this->belongsTo(PaketMembership::class, 'paket_id');
    }

    public function participants()
    {
        return $this->hasMany(TransaksiMembershipMember::class, 'transaksi_membership_id');
    }

    public function scopeNotCanceled($q)
    {
        return $q->whereNull('canceled_at');
    }

    public function getStatusAttribute(): string
    {
        if ($this->canceled_at) return 'canceled';

        $today = Carbon::today();

        if ($today->lt($this->tanggal_mulai)) return 'belum_aktif';
        if ($today->between($this->tanggal_mulai, $this->tanggal_akhir, true)) return 'aktif';
        if ($today->gt($this->tanggal_akhir)) return 'expired';

        return 'unknown';
    }
}

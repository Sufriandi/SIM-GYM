<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'paket_id',
        'tanggal_transaksi',
        'tanggal_mulai',
        'tanggal_akhir',
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

    // ================= RELASI =================

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

    // =============== STATUS DINAMIS ===============

    /**
     * Accessor status membership per transaksi:
     * - belum_aktif : hari ini sebelum tanggal_mulai
     * - aktif       : hari ini di antara tanggal_mulai & tanggal_akhir
     * - expired     : hari ini lewat tanggal_akhir
     * - canceled    : dibatalkan (canceled_at != null)
     */
    public function getStatusAttribute(): string
    {
        if ($this->canceled_at) {
            return 'canceled';
        }

        if (! $this->tanggal_mulai || ! $this->tanggal_akhir) {
            return 'unknown';
        }

        $today = Carbon::today();

        if ($today->lt($this->tanggal_mulai)) {
            return 'belum_aktif';
        }

        if ($today->betweenIncluded($this->tanggal_mulai, $this->tanggal_akhir)) {
            return 'aktif';
        }

        if ($today->gt($this->tanggal_akhir)) {
            return 'expired';
        }

        return 'unknown';
    }

    // (Opsional) scopes untuk query
    public function scopeAktif($query)
    {
        $today = Carbon::today();

        return $query
            ->whereNull('canceled_at')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_akhir', '>=', $today);
    }

    public function scopeBelumAktif($query)
    {
        $today = Carbon::today();

        return $query
            ->whereNull('canceled_at')
            ->whereDate('tanggal_mulai', '>', $today);
    }

    public function scopeExpired($query)
    {
        $today = Carbon::today();

        return $query
            ->whereNull('canceled_at')
            ->whereDate('tanggal_akhir', '<', $today);
    }
}

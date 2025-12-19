<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tanggal_daftar',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $member): void {
            if (empty($member->tanggal_daftar)) {
                $member->tanggal_daftar = now()->toDateString();
            }
        });
    }

    /**
     * Relasi: member dimiliki oleh satu user.
     * withTrashed agar histori transaksi tetap bisa menarik nama User
     * meskipun akun User/Member sudah di-soft delete.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class)->withTrashed();
    }

    public function izinLatihan(): HasMany
    {
        return $this->hasMany(\App\Models\IzinLatihan::class, 'member_id', 'id');
    }

    public function kehadiranMember(): HasMany
    {
        return $this->hasMany(\App\Models\KehadiranMember::class, 'member_id');
    }

    public function periodesAbsensi(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\AbsensiPeriode::class,
            'kehadiran_absensi',
            'member_id',
            'sesi_absensi_id'
        )
            ->withTimestamps()
            ->withPivot(['waktu_absen', 'status', 'device_info', 'keterangan']);
    }

    // =========================================================
    // RELASI MEMBERSHIP BARU (TRANSAKSI + PESERTA)
    // =========================================================

    /**
     * Transaksi membership yang dibeli member ini (sebagai buyer).
     */
    public function transaksiMembershipsAsBuyer(): HasMany
    {
        return $this->hasMany(\App\Models\TransaksiMembership::class, 'buyer_member_id');
    }

    /**
     * Baris peserta transaksi membership (member ini ikut transaksi sebagai peserta).
     */
    public function transaksiMembershipParticipants(): HasMany
    {
        return $this->hasMany(\App\Models\TransaksiMembershipMember::class, 'member_id');
    }

    public function transaksiProduksAsBuyer(): HasMany
    {
        return $this->hasMany(\App\Models\TransaksiProduk::class, 'buyer_member_id');
    }

    /**
     * Query transaksi membership yang sedang aktif untuk member ini,
     * baik dia sebagai buyer maupun peserta.
     *
     * Ini dikembalikan sebagai query builder (bukan relasi Eloquent),
     * karena relasi OR (buyer OR participant) sulit dibuat sebagai hasOne murni.
     */
    public function activeMembershipTransactionQuery()
    {
        $today = Carbon::today();

        return \App\Models\TransaksiMembership::query()
            ->with('paket')
            ->whereNull('canceled_at')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_akhir', '>=', $today)
            ->where(function ($q) {
                $q->where('buyer_member_id', $this->id)
                    ->orWhereHas('participants', function ($p) {
                        $p->where('member_id', $this->id);
                    });
            })
            ->orderByDesc('tanggal_akhir');
    }

    /**
     * Akses cepat: transaksi membership aktif (1 record) atau null.
     */
    public function getActiveMembershipTransactionAttribute()
    {
        return $this->activeMembershipTransactionQuery()->first();
    }

    /**
     * Akses cepat: paket aktif saat ini (model) atau null.
     */
    public function getActivePaketMembershipAttribute()
    {
        return $this->active_membership_transaction?->paket;
    }

    /**
     * Akses cepat: nama paket aktif saat ini (string).
     */
    public function getNamaPaketAktifAttribute(): ?string
    {
        return $this->active_paket_membership?->nama;
    }

    /**
     * Status membership untuk member ini (global):
     * - aktif       : ada transaksi aktif (sebagai buyer/peserta)
     * - expired     : pernah ada transaksi tapi tidak ada yang aktif saat ini
     * - belum_aktif : belum pernah ada transaksi sama sekali
     */
    public function getStatusMembershipAttribute(): string
    {
        $today = Carbon::today();

        $hasEver = \App\Models\TransaksiMembership::query()
            ->whereNull('canceled_at')
            ->where(function ($q) {
                $q->where('buyer_member_id', $this->id)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $this->id));
            })
            ->exists();

        if (! $hasEver) {
            return 'belum_aktif';
        }

        $isActive = \App\Models\TransaksiMembership::query()
            ->whereNull('canceled_at')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_akhir', '>=', $today)
            ->where(function ($q) {
                $q->where('buyer_member_id', $this->id)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $this->id));
            })
            ->exists();

        return $isActive ? 'aktif' : 'expired';
    }

    public function getTotalTransaksiProdukAttribute(): int
    {
        return $this->transaksiProduksAsBuyer()->count();
    }

    /**
     * Boolean helper: apakah member aktif sekarang.
     */
    public function getMembershipAktifAttribute(): bool
    {
        return $this->status_membership === 'aktif';
    }
}

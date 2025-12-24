<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    // =========================================================
    // RELASI DASAR
    // =========================================================

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
    // RELASI MEMBERSHIP (TRANSAKSI + PESERTA)
    // =========================================================

    public function transaksiMembershipsAsBuyer(): HasMany
    {
        return $this->hasMany(\App\Models\TransaksiMembership::class, 'buyer_member_id');
    }

    public function transaksiMembershipParticipants(): HasMany
    {
        return $this->hasMany(\App\Models\TransaksiMembershipMember::class, 'member_id');
    }

    public function transaksiProduksAsBuyer(): HasMany
    {
        return $this->hasMany(\App\Models\TransaksiProduk::class, 'buyer_member_id');
    }

    // =========================================================
    // HELPERS MEMBERSHIP (1 sumber logika)
    // =========================================================

    /**
     * Normalisasi input tanggal menjadi string Y-m-d.
     */
    protected function normalizeDate($date): string
    {
        if ($date instanceof Carbon) {
            return $date->toDateString();
        }

        return Carbon::parse($date)->toDateString();
    }

    /**
     * Query transaksi membership yang "milik" member ini (buyer atau participant).
     */
    protected function membershipOwnershipQuery(Builder $q): Builder
    {
        return $q->where(function ($qq) {
            $qq->where('buyer_member_id', $this->id)
                ->orWhereHas('participants', fn($p) => $p->where('member_id', $this->id));
        });
    }

    /**
     * Apakah member ini pernah punya transaksi membership (selain canceled).
     */
    public function hasEverMembership(): bool
    {
        $q = \App\Models\TransaksiMembership::query()
            ->whereNull('canceled_at');

        $this->membershipOwnershipQuery($q);

        return $q->exists();
    }

    /**
     * Apakah member aktif pada tanggal tertentu.
     * Ini yang dipakai untuk "gate" absensi.
     */
    public function hasActiveMembershipOn($date): bool
    {
        $d = $this->normalizeDate($date);

        $q = \App\Models\TransaksiMembership::query()
            ->whereNull('canceled_at')
            ->whereDate('tanggal_mulai', '<=', $d)
            ->whereDate('tanggal_akhir', '>=', $d);

        $this->membershipOwnershipQuery($q);

        return $q->exists();
    }

    /**
     * Query transaksi membership aktif pada tanggal tertentu (default: hari ini).
     */
    public function activeMembershipTransactionQuery($date = null): Builder
    {
        $d = $this->normalizeDate($date ?: Carbon::today());

        $q = \App\Models\TransaksiMembership::query()
            ->with('paket')
            ->whereNull('canceled_at')
            ->whereDate('tanggal_mulai', '<=', $d)
            ->whereDate('tanggal_akhir', '>=', $d);

        $this->membershipOwnershipQuery($q);

        return $q->orderByDesc('tanggal_akhir');
    }

    // =========================================================
    // ACCESSORS (tetap kompatibel)
    // =========================================================

    public function getActiveMembershipTransactionAttribute()
    {
        return $this->activeMembershipTransactionQuery(Carbon::today())->first();
    }

    public function getActivePaketMembershipAttribute()
    {
        return $this->active_membership_transaction?->paket;
    }

    public function getNamaPaketAktifAttribute(): ?string
    {
        return $this->active_paket_membership?->nama;
    }

    public function getStatusMembershipAttribute(): string
    {
        if (! $this->hasEverMembership()) {
            return 'belum_aktif';
        }

        return $this->hasActiveMembershipOn(Carbon::today()) ? 'aktif' : 'expired';
    }

    public function getTotalTransaksiProdukAttribute(): int
    {
        return $this->transaksiProduksAsBuyer()->count();
    }

    /**
     * Boolean helper: apakah member aktif sekarang.
     * (tetap ada agar kompatibel)
     */
    public function getMembershipAktifAttribute(): bool
    {
        return $this->hasActiveMembershipOn(Carbon::today());
    }
}

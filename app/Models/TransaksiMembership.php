<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TransaksiMembership extends Model
{
    use HasFactory;

    protected $table = 'transaksi_memberships';

    /**
     * Enum jenis_transaksi
     */
    public const JENIS_PEMBAYARAN  = 'pembayaran';
    public const JENIS_KOMPENSASI  = 'kompensasi';

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

    /**
     * Relationships
     */
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

    /**
     * Scopes
     */
    public function scopeNotCanceled(Builder $q): Builder
    {
        return $q->whereNull('canceled_at');
    }

    public function scopeValid(Builder $q): Builder
    {
        // alias: valid = tidak dibatalkan
        return $q->whereNull('canceled_at');
    }

    public function scopePembayaran(Builder $q): Builder
    {
        return $q->where('jenis_transaksi', self::JENIS_PEMBAYARAN);
    }

    public function scopeKompensasi(Builder $q): Builder
    {
        return $q->where('jenis_transaksi', self::JENIS_KOMPENSASI);
    }

    /**
     * Scope: transaksi yang MELIBATKAN member tertentu (buyer atau participant)
     */
    public function scopeMelibatkanMember(Builder $q, int $memberId): Builder
    {
        return $q->where(function ($w) use ($memberId) {
            $w->where('buyer_member_id', $memberId)
                ->orWhereHas('participants', function ($p) use ($memberId) {
                    $p->where('member_id', $memberId);
                });
        });
    }

    /**
     * Helper: End date terakhir untuk member (truth masa aktif individu)
     * - mempertimbangkan member sebagai buyer atau participant
     */
    public static function endDateTerakhirUntukMember(int $memberId): ?Carbon
    {
        $end = static::query()
            ->valid()
            ->melibatkanMember($memberId)
            ->max('tanggal_akhir'); // string 'YYYY-MM-DD' atau null

        return $end ? Carbon::parse($end)->startOfDay() : null;
    }

    /**
     * Helper: Transaksi PEMBAYARAN terakhir untuk member (untuk "current paket" & paket_id kompensasi)
     * - mempertimbangkan member sebagai buyer atau participant
     */
    public static function pembayaranTerakhirUntukMember(int $memberId): ?self
    {
        return static::query()
            ->valid()
            ->pembayaran()
            ->melibatkanMember($memberId)
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Helper: apakah membership individu sedang aktif pada tanggal tertentu?
     * Dipakai untuk validasi "izin latihan hanya boleh jika aktif".
     */
    public static function isAktifUntukMember(int $memberId, ?Carbon $tanggal = null): bool
    {
        $d = ($tanggal ?? Carbon::today())->startOfDay();

        return static::query()
            ->valid()
            ->melibatkanMember($memberId)
            ->whereDate('tanggal_mulai', '<=', $d->toDateString())
            ->whereDate('tanggal_akhir', '>=', $d->toDateString())
            ->exists();
    }

    /**
     * Helper opsional: ambil transaksi yang sedang aktif (kalau butuh detailnya)
     */
    public static function transaksiAktifUntukMember(int $memberId, ?Carbon $tanggal = null): ?self
    {
        $d = ($tanggal ?? Carbon::today())->startOfDay();

        return static::query()
            ->valid()
            ->melibatkanMember($memberId)
            ->whereDate('tanggal_mulai', '<=', $d->toDateString())
            ->whereDate('tanggal_akhir', '>=', $d->toDateString())
            ->orderByDesc('tanggal_akhir')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Accessor: status transaksi ini (rentang tanggal transaksi)
     */
    public function getStatusAttribute(): string
    {
        if ($this->canceled_at) {
            return 'canceled';
        }

        $today = Carbon::today();

        if ($this->tanggal_mulai && $today->lt($this->tanggal_mulai)) {
            return 'belum_aktif';
        }

        if (
            $this->tanggal_mulai && $this->tanggal_akhir
            && $today->between($this->tanggal_mulai, $this->tanggal_akhir, true)
        ) {
            return 'aktif';
        }

        if ($this->tanggal_akhir && $today->gt($this->tanggal_akhir)) {
            return 'expired';
        }

        return 'unknown';
    }
}

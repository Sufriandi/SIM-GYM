<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TransaksiMembership extends Model
{
    use HasFactory;

    protected $table = 'transaksi_memberships';

    public const JENIS_PEMBAYARAN = 'pembayaran';
    public const JENIS_KOMPENSASI = 'kompensasi';

    protected $fillable = [
        'no_nota',
        'total',
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
        'total'             => 'integer',
    ];

    /**
     * Relationships
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'buyer_member_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketMembership::class, 'paket_id');
    }

    public function participants(): HasMany
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
        return $this->scopeNotCanceled($q);
    }

    public function scopePembayaran(Builder $q): Builder
    {
        return $q->where('jenis_transaksi', self::JENIS_PEMBAYARAN);
    }

    public function scopeKompensasi(Builder $q): Builder
    {
        return $q->where('jenis_transaksi', self::JENIS_KOMPENSASI);
    }

    public function scopeRevenue(Builder $q): Builder
    {
        return $q->notCanceled()
            ->pembayaran()
            ->whereNotNull('metode_pembayaran');
    }

    /**
     * Helpers canonical
     */
    public static function endDateTerakhirUntukMember(int $memberId): ?Carbon
    {
        $end = TransaksiMembershipMember::query()
            ->join('transaksi_memberships as tm', 'tm.id', '=', 'transaksi_membership_members.transaksi_membership_id')
            ->whereNull('tm.canceled_at')
            ->where('transaksi_membership_members.member_id', $memberId)
            ->max('transaksi_membership_members.tanggal_akhir');

        return $end ? Carbon::parse($end)->startOfDay() : null;
    }

    public static function isAktifUntukMember(int $memberId, $date = null): bool
    {
        $d = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        return TransaksiMembershipMember::query()
            ->join('transaksi_memberships as tm', 'tm.id', '=', 'transaksi_membership_members.transaksi_membership_id')
            ->whereNull('tm.canceled_at')
            ->where('transaksi_membership_members.member_id', $memberId)
            ->whereDate('transaksi_membership_members.tanggal_mulai', '<=', $d)
            ->whereDate('transaksi_membership_members.tanggal_akhir', '>=', $d)
            ->exists();
    }

    public function getStatusAttribute(): string
    {
        if ($this->canceled_at) {
            return 'canceled';
        }

        // Ambil periode PRIMARY dari pivot jika ada
        $primary = null;

        if ($this->relationLoaded('participants')) {
            $primary = $this->participants->firstWhere('role', 'primary');
        }

        if (!$primary) {
            $primary = $this->participants()->where('role', 'primary')->first();
        }

        $mulai = $primary?->tanggal_mulai ?? $this->tanggal_mulai;
        $akhir = $primary?->tanggal_akhir ?? $this->tanggal_akhir;

        if (!$mulai || !$akhir) {
            return 'unknown';
        }

        $m = Carbon::parse($mulai)->startOfDay();
        $a = Carbon::parse($akhir)->startOfDay();
        $t = Carbon::today()->startOfDay();

        if ($t->lt($m)) {
            return 'belum_aktif';
        }

        if ($t->between($m, $a, true)) {
            return 'aktif';
        }

        if ($t->gt($a)) {
            return 'expired';
        }

        return 'unknown';
    }


    /**
     * Auto-generate no_nota + default total
     */
    protected static function booted(): void
    {
        static::creating(function (self $trx) {
            if (empty($trx->tanggal_transaksi)) {
                $trx->tanggal_transaksi = now();
            }

            // no_nota: TM-YYMMDD-XXXXXX
            if (empty($trx->no_nota)) {
                $tanggal = Carbon::parse($trx->tanggal_transaksi);
                $date = $tanggal->format('ymd');

                for ($i = 0; $i < 50; $i++) {
                    $rand = Str::upper(Str::random(6));
                    $code = "TM-{$date}-{$rand}";

                    if (!self::where('no_nota', $code)->exists()) {
                        $trx->no_nota = $code;
                        break;
                    }
                }

                if (empty($trx->no_nota)) {
                    $trx->no_nota = "TM-{$date}-" . Str::upper(Str::random(6));
                }
            }

            if ($trx->total === null) {
                $trx->total = 0;
            }
        });
    }
}

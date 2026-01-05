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

    public const JENIS_PEMBAYARAN  = 'pembayaran';
    public const JENIS_KOMPENSASI  = 'kompensasi';

    protected $fillable = [
        'buyer_member_id',
        'created_by',
        'paket_id',
        'tanggal_transaksi',
        'tanggal_mulai',   // summary header (MIN start)
        'tanggal_akhir',   // summary header (MAX end)
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
        // participants di sini adalah pivot rows (TransaksiMembershipMember)
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
     * Scope: transaksi yang melibatkan member tertentu (untuk kebutuhan list/filter transaksi).
     * Catatan: BUKAN sumber kebenaran masa aktif individu.
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
     * Helper canonical: End date terakhir untuk member.
     * Sumber kebenaran di transaksi_membership_members (pivot), join ke transaksi untuk valid().
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

    /**
     * Helper canonical: Transaksi pembayaran terakhir untuk member
     * (untuk "current paket" & referensi paket_id saat kompensasi).
     */
    public static function pembayaranTerakhirUntukMember(int $memberId): ?self
    {
        $trxId = TransaksiMembershipMember::query()
            ->join('transaksi_memberships as tm', 'tm.id', '=', 'transaksi_membership_members.transaksi_membership_id')
            ->whereNull('tm.canceled_at')
            ->where('tm.jenis_transaksi', self::JENIS_PEMBAYARAN)
            ->where('transaksi_membership_members.member_id', $memberId)
            ->orderByDesc('tm.tanggal_transaksi')
            ->orderByDesc('tm.id')
            ->value('tm.id');

        return $trxId ? static::query()->find($trxId) : null;
    }

    /**
     * Helper canonical: apakah membership individu sedang aktif pada tanggal tertentu?
     * Dipakai untuk validasi izin latihan, dsb.
     */
    public static function isAktifUntukMember(int $memberId, ?Carbon $tanggal = null): bool
    {
        $d = ($tanggal ?? Carbon::today())->startOfDay()->toDateString();

        return TransaksiMembershipMember::query()
            ->join('transaksi_memberships as tm', 'tm.id', '=', 'transaksi_membership_members.transaksi_membership_id')
            ->whereNull('tm.canceled_at')
            ->where('transaksi_membership_members.member_id', $memberId)
            ->whereDate('transaksi_membership_members.tanggal_mulai', '<=', $d)
            ->whereDate('transaksi_membership_members.tanggal_akhir', '>=', $d)
            ->exists();
    }

    /**
     * Helper canonical: ambil transaksi yang sedang aktif untuk member (kalau butuh detail).
     */
    public static function transaksiAktifUntukMember(int $memberId, ?Carbon $tanggal = null): ?self
    {
        $d = ($tanggal ?? Carbon::today())->startOfDay()->toDateString();

        $trxId = TransaksiMembershipMember::query()
            ->join('transaksi_memberships as tm', 'tm.id', '=', 'transaksi_membership_members.transaksi_membership_id')
            ->whereNull('tm.canceled_at')
            ->where('transaksi_membership_members.member_id', $memberId)
            ->whereDate('transaksi_membership_members.tanggal_mulai', '<=', $d)
            ->whereDate('transaksi_membership_members.tanggal_akhir', '>=', $d)
            ->orderByDesc('transaksi_membership_members.tanggal_akhir')
            ->orderByDesc('tm.id')
            ->value('tm.id');

        return $trxId ? static::query()->find($trxId) : null;
    }

    /**
     * Accessor: status transaksi ini (berdasarkan summary header).
     * Catatan: status ini status "transaksi", bukan status tiap member.
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

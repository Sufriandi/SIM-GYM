<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SesiAbsensi extends Model
{
    use HasFactory;

    protected $table = 'sesi_absensi';

    protected $fillable = [
        'kode_qr',
        'nama_sesi',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'jam_mulai'   => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    /* =========================================================
     |  RELATIONSHIPS
     |========================================================= */

    /**
     * Admin (user) yang membuat sesi absensi.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Semua kehadiran member pada sesi ini.
     */
    public function kehadiran()
    {
        return $this->hasMany(KehadiranMember::class, 'sesi_absensi_id');
    }

    /* =========================================================
     |  SCOPES
     |========================================================= */

    /**
     * Scope: hanya sesi yang status = aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope: filter berdasarkan tanggal.
     */
    public function scopeUntukTanggal(Builder $query, $tanggal): Builder
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    /**
     * Scope: sesi yang sedang berjalan sekarang (by tanggal & jam).
     * Opsional, bisa dipakai saat validasi scan QR.
     */
    public function scopeSedangBerjalan(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->whereDate('tanggal', $now->toDateString())
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('jam_mulai')
                  ->orWhere('jam_mulai', '<=', $now->format('H:i:s'));
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('jam_selesai')
                  ->orWhere('jam_selesai', '>=', $now->format('H:i:s'));
            })
            ->where('status', 'aktif');
    }

    /* =========================================================
     |  HELPERS
     |========================================================= */

    /**
     * Cek apakah sesi masih aktif & berada dalam rentang waktu.
     */
    public function isActiveNow(): bool
    {
        if ($this->status !== 'aktif') {
            return false;
        }

        $now     = Carbon::now();
        $tanggal = $this->tanggal instanceof Carbon
            ? $this->tanggal
            : Carbon::parse($this->tanggal);

        if ($tanggal->isSameDay($now) === false) {
            return false;
        }

        // Validasi jam mulai
        if ($this->jam_mulai) {
            $start = Carbon::parse($this->tanggal->toDateString() . ' ' . $this->jam_mulai->format('H:i:s'));
            if ($now->lt($start)) {
                return false;
            }
        }

        // Validasi jam selesai
        if ($this->jam_selesai) {
            $end = Carbon::parse($this->tanggal->toDateString() . ' ' . $this->jam_selesai->format('H:i:s'));
            if ($now->gt($end)) {
                return false;
            }
        }

        return true;
    }
}

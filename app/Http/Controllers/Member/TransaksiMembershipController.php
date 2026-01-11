<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PaketMembership;
use App\Models\TransaksiMembership;
use App\Models\TransaksiMembershipMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiMembershipController extends Controller
{
    /**
     * POST /member/paket-membership/{paketMembership}/checkout
     */
    public function store(Request $request, PaketMembership $paketMembership)
    {
        abort_unless((bool) $paketMembership->is_public, 404);

        $validated = $request->validate([
            'metode_pembayaran' => ['required', 'in:transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],

            // opsional untuk paket group
            'group_member_ids'   => ['array'],
            'group_member_ids.*' => ['nullable', 'distinct', 'exists:members,id'],
        ]);

        $user = $request->user();

        $buyer = Member::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Cegah transaksi pending/submitted yang masih aktif
        $hasActivePending = TransaksiMembership::query()
            ->where('buyer_member_id', $buyer->id)
            ->whereNull('canceled_at')
            ->whereIn('payment_status', [TransaksiMembership::PAY_PENDING, TransaksiMembership::PAY_SUBMITTED])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasActivePending) {
            return back()->withErrors([
                'metode_pembayaran' => 'Masih ada transaksi membership yang menunggu pembayaran/verifikasi. Selesaikan dulu transaksi sebelumnya.',
            ]);
        }

        $trx = DB::transaction(function () use ($validated, $paketMembership, $buyer, $user) {
            $now = now();

            // WAJIB: hitung periode supaya pivot tidak NULL (sesuai schema kamu)
            [$mulaiPrimary, $akhirPrimary] = $this->computePeriodeUntukMember(
                memberId: (int) $buyer->id,
                durasiHari: (int) $paketMembership->durasi,
                tanggalTransaksi: Carbon::parse($now)
            );

            $trx = TransaksiMembership::create([
                'buyer_member_id'   => $buyer->id,
                'created_by'        => $user->id,
                'paket_id'          => $paketMembership->id,
                'tanggal_transaksi' => $now,
                'jenis_transaksi'   => TransaksiMembership::JENIS_PEMBAYARAN,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'keterangan'        => $validated['keterangan'] ?? null,
                'total'             => (int) $paketMembership->harga,

                // payment flow aman
                'payment_status'    => TransaksiMembership::PAY_PENDING,
                'expires_at'        => $now->copy()->addMinutes(30),

                // fallback periode di header trx
                'tanggal_mulai'     => $mulaiPrimary,
                'tanggal_akhir'     => $akhirPrimary,
            ]);

            // Primary participant (ini yang dulu error kalau NULL)
            TransaksiMembershipMember::create([
                'transaksi_membership_id' => $trx->id,
                'member_id'               => $buyer->id,
                'role'                    => 'primary',
                'tanggal_mulai'           => $mulaiPrimary,
                'tanggal_akhir'           => $akhirPrimary,
            ]);

            // Group participant kalau double/triple
            $tipe = (string) $paketMembership->tipe;

            if (in_array($tipe, ['double', 'triple'], true)) {
                $groupIds = collect($validated['group_member_ids'] ?? [])
                    ->filter()
                    ->map(fn($v) => (int) $v)
                    ->unique()
                    ->values();

                $maxPersons = $tipe === 'double' ? 2 : 3;
                $maxAdditional = max(0, $maxPersons - 1);

                if ($groupIds->count() > $maxAdditional) {
                    abort(422, "Maksimal anggota tambahan untuk paket {$tipe} adalah {$maxAdditional} orang.");
                }

                if ($groupIds->contains((int) $buyer->id)) {
                    abort(422, 'Member utama tidak boleh dimasukkan lagi sebagai anggota tambahan.');
                }

                foreach ($groupIds as $gid) {
                    [$mulai, $akhir] = $this->computePeriodeUntukMember(
                        memberId: (int) $gid,
                        durasiHari: (int) $paketMembership->durasi,
                        tanggalTransaksi: Carbon::parse($now)
                    );

                    TransaksiMembershipMember::create([
                        'transaksi_membership_id' => $trx->id,
                        'member_id'               => (int) $gid,
                        'role'                    => 'secondary',
                        'tanggal_mulai'           => $mulai,
                        'tanggal_akhir'           => $akhir,
                    ]);
                }
            }

            return $trx;
        });

        return redirect()->route('member.membership.transaksi.show', $trx->id);
    }

    /**
     * GET /member/membership/transaksi/{trx}
     */
    public function show(Request $request, TransaksiMembership $trx)
    {
        $user = $request->user();

        $buyer = Member::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        abort_unless((int) $trx->buyer_member_id === (int) $buyer->id, 403);

        $this->expireIfNeeded($trx);

        $trx->load(['paket', 'participants.member']);

        // Rekening + QRIS (mengikuti pola modal produk, fallback aman)
        $rekenings = collect();
        $qris = null;
        $qrisImg = null;

        if (class_exists(\App\Models\RekeningBank::class)) {
            $rekenings = \App\Models\RekeningBank::query()
                ->where('is_active', true)
                ->orderBy('nama_bank')
                ->get();
        }

        if (class_exists(\App\Models\Qris::class)) {
            $qris = \App\Models\Qris::query()->first();
            if ($qris && !empty($qris->gambar)) {
                $qrisImg = $this->resolveStorageUrl($qris->gambar);
            }
        }

        $waAdmin = (string) config('gym.wa_admin', '6281234567890');

        $remainingSeconds = 0;
        if ($trx->expires_at && in_array((string)$trx->payment_status, [TransaksiMembership::PAY_PENDING, TransaksiMembership::PAY_SUBMITTED], true)) {
            $remainingSeconds = max(0, now()->diffInSeconds($trx->expires_at, false));
        }

        return view('member.membership_transaksi.status', [
            'trx'              => $trx,
            'rekenings'        => $rekenings,
            'qris'             => $qris,
            'qrisImg'          => $qrisImg,
            'waAdmin'          => $waAdmin,
            'orderId'          => $trx->no_nota ?: ('TM-' . $trx->id),
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * POST /member/membership/transaksi/{trx}/upload-bukti
     */
    public function uploadBukti(Request $request, TransaksiMembership $trx)
    {
        $user = $request->user();

        $buyer = Member::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        abort_unless((int) $trx->buyer_member_id === (int) $buyer->id, 403);

        $this->expireIfNeeded($trx);

        if (!in_array((string) $trx->payment_status, [TransaksiMembership::PAY_PENDING, TransaksiMembership::PAY_SUBMITTED], true)) {
            return back()->withErrors(['bukti_bayar' => 'Transaksi ini sudah diproses / tidak dapat diupload lagi.']);
        }

        $validated = $request->validate([
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $file = $validated['bukti_bayar'];

        $path = $file->store('payment_proofs/membership', 'public');

        $trx->payment_proof_path     = $path;
        $trx->payment_proof_original = $file->getClientOriginalName();
        $trx->payment_proof_mime     = $file->getClientMimeType();
        $trx->payment_proof_size     = (int) $file->getSize();

        // Di data model kamu ada paid_at; aman di-set saat bukti dikirim
        $trx->paid_at        = now();
        $trx->payment_status = TransaksiMembership::PAY_SUBMITTED;

        $trx->save();

        return back()->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
    }

    // =========================
    // Helpers
    // =========================

    private function computePeriodeUntukMember(int $memberId, int $durasiHari, Carbon $tanggalTransaksi): array
    {
        $transDay = $tanggalTransaksi->copy()->startOfDay();
        $today = Carbon::today()->startOfDay();

        $baseStart = $transDay->lt($today) ? $today : $transDay;

        $lastEnd = TransaksiMembership::endDateTerakhirUntukMember($memberId);

        if ($lastEnd && $lastEnd->gte($baseStart)) {
            $mulai = $lastEnd->copy()->addDay();
        } else {
            $mulai = $baseStart->copy();
        }

        $akhir = $mulai->copy()->addDays(max(1, $durasiHari) - 1);

        return [$mulai, $akhir];
    }

    private function expireIfNeeded(TransaksiMembership $trx): void
    {
        if (!$trx->expires_at) return;

        if (!in_array((string)$trx->payment_status, [TransaksiMembership::PAY_PENDING, TransaksiMembership::PAY_SUBMITTED], true)) return;

        if (now()->greaterThanOrEqualTo($trx->expires_at)) {
            $trx->payment_status = TransaksiMembership::PAY_EXPIRED;
            if (!$trx->canceled_at) {
                $trx->canceled_at = now();
            }
            $trx->save();
        }
    }

    private function resolveStorageUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) return url('/' . $path);
        if (str_starts_with($path, 'public/')) $path = substr($path, 7);

        return Storage::disk('public')->url($path);
    }
}

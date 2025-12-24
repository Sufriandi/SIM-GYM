<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use App\Models\Member;
use App\Models\TransaksiMembership;
use App\Models\TransaksiMembershipMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class IzinLatihanController extends Controller
{
    public function __construct()
    {
        $izinPending = IzinLatihan::where('status', 'pending')->count();
        View::share('izinPending', $izinPending);
    }

    public function index(Request $request)
    {
        $pageTitle = 'Permintaan Izin Baru';

        $query = IzinLatihan::with(['member.user'])
            ->where('status', 'pending');

        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($q) use ($search) {
                $q->whereHas('member.user', function ($u) use ($search) {
                    $u->where('name', 'like', $search . '%')
                        ->orWhere('username', 'like', $search . '%');
                });
            });
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'days_max':
                $query->orderBy('jumlah_hari', 'desc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'days_min':
                $query->orderBy('jumlah_hari', 'asc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $daftar_izin = $query->paginate(20)->withQueryString();

        $membersForSelect = Member::query()
            ->with(['user:id,name,username,role'])
            ->whereHas('user', fn($q) => $q->where('role', 'member'))
            ->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'members.user_id')
                    ->limit(1)
            )
            ->get(['id', 'user_id']);

        return view('admin.izin_latihan.index', compact(
            'daftar_izin',
            'pageTitle',
            'membersForSelect',
        ));
    }

    /**
     * Admin menambahkan izin manual (langsung disetujui) + membuat transaksi kompensasi.
     * Aturan bisnis (opsi A):
     * - Member harus punya transaksi PEMBAYARAN sebelumnya
     * - Member harus sedang AKTIF pada tanggal_mulai izin (atau minimal aktif pada tanggal itu)
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validateWithBag(
            'izin_manual',
            [
                'member_id'     => 'required|exists:members,id',
                'jumlah_hari'   => 'required|integer|min:1|max:30',
                'tanggal_mulai' => 'required|date',
                'bukti_alasan'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
                'alasan'        => 'nullable|string|max:5000',
            ],
            [
                'member_id.required'     => 'Silakan pilih member terlebih dahulu.',
                'member_id.exists'       => 'Member yang dipilih tidak ditemukan.',
                'jumlah_hari.required'   => 'Jumlah hari wajib diisi.',
                'jumlah_hari.integer'    => 'Jumlah hari harus berupa angka.',
                'jumlah_hari.min'        => 'Jumlah hari minimal 1 hari.',
                'jumlah_hari.max'        => 'Jumlah hari maksimal 30 hari.',
                'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
                'tanggal_mulai.date'     => 'Format tanggal mulai tidak valid.',
                'bukti_alasan.max'       => 'Ukuran file maksimal 2MB.',
                'bukti_alasan.mimes'     => 'Format file harus JPG, JPEG, PNG, PDF, DOC, atau DOCX.',
            ]
        );

        $memberId   = (int) $validated['member_id'];
        $jumlahHari = (int) $validated['jumlah_hari'];

        $tanggalMulaiIzin = Carbon::parse($validated['tanggal_mulai'])->startOfDay();

        // 1) Wajib punya transaksi pembayaran (untuk paket_id kompensasi + aturan bisnis)
        $lastPaidTx = $this->getLastPaidTransactionForMember($memberId);
        if (! $lastPaidTx) {
            return back()
                ->withErrors(['member_id' => 'Member ini belum pernah melakukan transaksi membership (pembayaran). Tidak bisa membuat izin/kompensasi.'])
                ->withInput();
        }

        // 2) Wajib aktif di tanggal_mulai izin (opsi A)
        if (! $this->isMembershipActiveAtDate($memberId, $tanggalMulaiIzin)) {
            return back()
                ->withErrors(['tanggal_mulai' => 'Member tidak memiliki membership aktif pada tanggal mulai izin. Izin tidak bisa dibuat.'])
                ->withInput();
        }

        $tanggalSelesaiIzin = $tanggalMulaiIzin->copy()->addDays($jumlahHari - 1);

        $buktiPath = null;
        if ($request->hasFile('bukti_alasan')) {
            $buktiPath = $request->file('bukti_alasan')->store('uploads/bukti_izin', 'public');
        }

        $alasanText = isset($validated['alasan']) ? trim($validated['alasan']) : '';

        DB::transaction(function () use (
            $memberId,
            $jumlahHari,
            $tanggalMulaiIzin,
            $tanggalSelesaiIzin,
            $buktiPath,
            $alasanText,
            $lastPaidTx
        ) {
            // A) simpan izin (langsung disetujui)
            $izin = IzinLatihan::create([
                'member_id'             => $memberId,
                'jumlah_hari'           => $jumlahHari,
                'tanggal_mulai'         => $tanggalMulaiIzin->toDateString(),
                'tanggal_selesai'       => $tanggalSelesaiIzin->toDateString(),
                'status'                => 'disetujui',
                'durasi_izin_disetujui' => $jumlahHari,
                'bukti_alasan'          => $buktiPath,
                'alasan'                => $alasanText !== '' ? $alasanText : '[Ditambahkan manual oleh admin]',
                'keterangan_admin'      => 'Izin manual ditambahkan dan langsung disetujui oleh Admin.',
                'tanggal_persetujuan'   => now(),
            ]);

            // B) buat transaksi kompensasi (INDIVIDU)
            [$mulaiKomp, $akhirKomp] = $this->buildKompensasiPeriodIndividu($memberId, $jumlahHari);

            $trx = TransaksiMembership::create([
                'buyer_member_id'   => $memberId,
                'created_by'        => auth()->id(),
                'paket_id'          => $lastPaidTx->paket_id,
                'tanggal_transaksi' => now(),
                'tanggal_mulai'     => $mulaiKomp,
                'tanggal_akhir'     => $akhirKomp,
                'jenis_transaksi'   => TransaksiMembership::JENIS_KOMPENSASI,
                'metode_pembayaran' => null,
                'keterangan'        => "Kompensasi izin latihan (manual) {$jumlahHari} hari. izin_id={$izin->id}",
                'canceled_at'       => null,
            ]);

            // konsisten: selalu buat primary participant
            TransaksiMembershipMember::create([
                'transaksi_membership_id' => $trx->id,
                'member_id'               => $memberId,
                'role'                    => 'primary',
            ]);
        });

        return redirect()
            ->route('admin.izin_latihan.index')
            ->with('success', 'Izin manual berhasil ditambahkan dan disetujui. Masa aktif membership diperpanjang melalui transaksi kompensasi.');
    }

    public function history(Request $request)
    {
        $pageTitle = 'Riwayat Persetujuan Izin';

        $query = IzinLatihan::with(['member.user'])
            ->whereIn('status', ['disetujui', 'ditolak']);

        $sort = $request->input('sort', 'processed_newest');

        $processedAtExpr = "COALESCE(tanggal_persetujuan, updated_at, created_at)";
        $approvedDaysExpr = "CASE
            WHEN status = 'disetujui' THEN COALESCE(durasi_izin_disetujui, 0)
            ELSE 0
        END";

        switch ($sort) {
            case 'processed_oldest':
                $query->orderByRaw("$processedAtExpr ASC")->orderBy('id', 'ASC');
                break;

            case 'approved_max':
                $query->orderByRaw("$approvedDaysExpr DESC")
                    ->orderByRaw("$processedAtExpr DESC")
                    ->orderBy('id', 'DESC');
                break;

            case 'approved_min':
                $query->orderByRaw("$approvedDaysExpr ASC")
                    ->orderByRaw("$processedAtExpr DESC")
                    ->orderBy('id', 'DESC');
                break;

            case 'requested_max':
                $query->orderBy('jumlah_hari', 'DESC')
                    ->orderByRaw("$processedAtExpr DESC")
                    ->orderBy('id', 'DESC');
                break;

            case 'requested_min':
                $query->orderBy('jumlah_hari', 'ASC')
                    ->orderByRaw("$processedAtExpr DESC")
                    ->orderBy('id', 'DESC');
                break;

            case 'processed_newest':
            default:
                $query->orderByRaw("$processedAtExpr DESC")->orderBy('id', 'DESC');
                break;
        }

        $riwayat_izin = $query->paginate(15)->withQueryString();

        return view('admin.izin_latihan.history', compact('riwayat_izin', 'pageTitle'));
    }

    public function show($id)
    {
        $pageTitle = 'Detail Izin Member';
        $izin = IzinLatihan::with(['member.user'])->findOrFail($id);

        return view('admin.izin_latihan.detail', compact('izin', 'pageTitle'));
    }

    public function approveForm(IzinLatihan $izinLatihan)
    {
        if ($izinLatihan->status !== 'pending') {
            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', 'Izin sudah diproses.');
        }

        $pageTitle = 'Formulir Persetujuan Izin';
        $izin = $izinLatihan->load(['member.user']);

        return view('admin.izin_latihan.approve_form', compact('izin', 'pageTitle'));
    }

    /**
     * Approve izin:
     * - Update izin -> disetujui + durasi_izin_disetujui
     * - Jika approved_days > 0 -> buat transaksi kompensasi (INDIVIDU)
     *
     * Aturan bisnis (opsi A):
     * - Member wajib punya transaksi pembayaran (pembayaran terakhir dipakai untuk paket_id)
     * - Member wajib aktif pada tanggal_mulai izin (atau minimal aktif pada tanggal itu)
     */
    public function approveIzin(Request $request, IzinLatihan $izinLatihan)
    {
        $request->validate([
            'approved_days'    => 'required|integer|min:0|max:' . $izinLatihan->jumlah_hari,
            'keterangan_admin' => 'nullable|string|max:1000',
        ], [
            'approved_days.max' => 'Hari yang disetujui tidak boleh melebihi durasi permintaan member (' . $izinLatihan->jumlah_hari . ' hari).',
        ]);

        $approvedDays = (int) $request->approved_days;

        $member = $izinLatihan->member?->load('user');
        if (! $member) {
            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', 'Member tidak ditemukan.');
        }

        $memberId   = (int) $member->id;
        $memberName = optional($member->user)->name ?? 'Member';

        try {
            DB::transaction(function () use ($izinLatihan, $approvedDays, $request, $memberId) {

                // Lock izin agar tidak double approve
                $izin = IzinLatihan::whereKey($izinLatihan->id)->lockForUpdate()->firstOrFail();

                if ($izin->status !== 'pending') {
                    throw new \RuntimeException('Izin sudah diproses.');
                }

                // aturan opsi A: aktif pada tanggal_mulai izin
                $izinStart = Carbon::parse($izin->tanggal_mulai)->startOfDay();
                if (! $this->isMembershipActiveAtDate($memberId, $izinStart)) {
                    throw new \RuntimeException('Membership member tidak aktif pada tanggal mulai izin. Izin tidak dapat disetujui.');
                }

                // Jika disetujui > 0, wajib ada transaksi pembayaran (untuk paket_id kompensasi)
                $lastPaidTx = null;
                if ($approvedDays > 0) {
                    $lastPaidTx = $this->getLastPaidTransactionForMember($memberId, true);
                    if (! $lastPaidTx) {
                        throw new \RuntimeException('Member belum pernah melakukan transaksi membership (pembayaran). Tidak bisa membuat kompensasi.');
                    }
                }

                $izin->update([
                    'status'                => 'disetujui',
                    'durasi_izin_disetujui' => $approvedDays,
                    'keterangan_admin'      => $request->keterangan_admin,
                    'tanggal_persetujuan'   => now(),
                ]);

                if ($approvedDays > 0) {
                    [$mulaiKomp, $akhirKomp] = $this->buildKompensasiPeriodIndividu($memberId, $approvedDays);

                    $trx = TransaksiMembership::create([
                        'buyer_member_id'   => $memberId,
                        'created_by'        => auth()->id(),
                        'paket_id'          => $lastPaidTx->paket_id,
                        'tanggal_transaksi' => now(),
                        'tanggal_mulai'     => $mulaiKomp,
                        'tanggal_akhir'     => $akhirKomp,
                        'jenis_transaksi'   => TransaksiMembership::JENIS_KOMPENSASI,
                        'metode_pembayaran' => null,
                        'keterangan'        => $request->keterangan_admin
                            ? "Kompensasi izin (izin_id={$izin->id}). {$request->keterangan_admin}"
                            : "Kompensasi izin (izin_id={$izin->id}) {$approvedDays} hari.",
                        'canceled_at'       => null,
                    ]);

                    TransaksiMembershipMember::create([
                        'transaksi_membership_id' => $trx->id,
                        'member_id'               => $memberId,
                        'role'                    => 'primary',
                    ]);
                }
            });

            $msg = $approvedDays > 0
                ? "Izin {$memberName} disetujui. Masa aktif membership diperpanjang {$approvedDays} hari (kompensasi)."
                : "Izin {$memberName} disetujui. Tanpa perpanjangan membership (0 hari).";

            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('success', $msg);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses persetujuan. Terjadi kesalahan sistem.');
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'keterangan_admin' => 'nullable|string|max:1000',
        ]);

        $izin = IzinLatihan::with(['member.user'])->findOrFail($id);

        if ($izin->status === 'pending') {
            $izin->status = 'ditolak';
            $izin->keterangan_admin = $request->input('keterangan_admin') ?: null;
            $izin->tanggal_persetujuan = now();
            $izin->save();

            $memberName = optional($izin->member?->user)->name ?? '';

            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('success', 'Izin member ' . $memberName . ' telah DITOLAK.');
        }

        return redirect()
            ->route('admin.izin_latihan.index')
            ->with('info', 'Izin latihan ini sudah diproses sebelumnya.');
    }

    /**
     * TRANSAKSI PEMBAYARAN terakhir yang relevan untuk individu:
     * - melibatkan member sebagai buyer ATAU participant
     * - diurutkan berdasarkan tanggal_akhir terjauh (lebih aman untuk kasus backdate)
     */
    private function getLastPaidTransactionForMember(int $memberId, bool $lock = false): ?TransaksiMembership
    {
        $q = TransaksiMembership::query()
            ->valid()
            ->pembayaran()
            ->where(function ($w) use ($memberId) {
                $w->where('buyer_member_id', $memberId)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $memberId));
            })
            ->orderByDesc('tanggal_akhir')
            ->orderByDesc('id');

        if ($lock) {
            $q->lockForUpdate();
        }

        return $q->first();
    }

    /**
     * Cek membership aktif pada tanggal tertentu (opsi A).
     * Aktif = ada transaksi valid (pembayaran/kompensasi) yang periodenya mencakup tanggal tsb
     * dan member terlibat sebagai buyer atau participant.
     */
    private function isMembershipActiveAtDate(int $memberId, Carbon $date): bool
    {
        $d = $date->toDateString();

        return TransaksiMembership::query()
            ->valid()
            ->whereDate('tanggal_mulai', '<=', $d)
            ->whereDate('tanggal_akhir', '>=', $d)
            ->where(function ($w) use ($memberId) {
                $w->where('buyer_member_id', $memberId)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $memberId));
            })
            ->exists();
    }

    /**
     * Periode kompensasi untuk INDIVIDU (inklusif):
     * start = (end_terakhir >= today) ? end_terakhir + 1 : today
     * end   = start + (days - 1)
     *
     * end_terakhir dihitung dari transaksi valid yang melibatkan member (buyer/participant),
     * sehingga paket double/triple tetap "masa aktif per individu".
     */
    private function buildKompensasiPeriodIndividu(int $memberId, int $days): array
    {
        $today = now()->startOfDay();

        $lastEnd = TransaksiMembership::query()
            ->valid()
            ->where(function ($w) use ($memberId) {
                $w->where('buyer_member_id', $memberId)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $memberId));
            })
            ->max('tanggal_akhir'); // string YYYY-MM-DD atau null

        $start = $today->copy();

        if ($lastEnd) {
            $end = Carbon::parse($lastEnd)->startOfDay();
            $start = $end->gte($today) ? $end->copy()->addDay() : $today->copy();
        }

        $finish = $start->copy()->addDays($days - 1);

        return [$start->toDateString(), $finish->toDateString()];
    }
}

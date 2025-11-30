<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\IzinLatihan as IzinLatihanModel;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IzinLatihan extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // STATE LIST
    public $search = '';
    public $sort   = 'newest';

    // STATE CREATE (di-handle Livewire)
    public $member_id;
    public $jumlah_hari = 1;
    public $tanggal_mulai;
    public $alasan;
    public $bukti_alasan;
    // STATE APPROVE
    public $approve_id;
    public $approved_days;
    public $keterangan_admin;

    protected $rules = [
        'member_id'     => 'required|exists:members,id',
        'jumlah_hari'   => 'required|integer|min:1|max:30',
        'tanggal_mulai' => 'required|date',
        'alasan'        => 'nullable|string|max:5000',
        'bukti_alasan'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
    ];

    protected $messages = [
        'member_id.required'     => 'Silakan pilih member terlebih dahulu.',
        'member_id.exists'       => 'Member yang dipilih tidak ditemukan.',
        'jumlah_hari.required'   => 'Jumlah hari wajib diisi.',
        'jumlah_hari.integer'    => 'Jumlah hari harus berupa angka.',
        'jumlah_hari.min'        => 'Jumlah hari minimal 1 hari.',
        'jumlah_hari.max'        => 'Jumlah hari maksimal 30 hari.',
        'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
        'tanggal_mulai.date'     => 'Format tanggal mulai tidak valid.',
        'bukti_alasan.mimes'     => 'Format file harus JPG, JPEG, PNG, PDF, DOC, atau DOCX.',
        'bukti_alasan.max'       => 'Ukuran file maksimal 2MB.',
    ];

    public function mount()
    {
        $this->tanggal_mulai = now()->toDateString();
        $this->jumlah_hari   = 1;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSort()
    {
        $this->resetPage();
    }

    /* =========================
     *  CREATE MANUAL (Livewire)
     * ========================= */

    public function saveManual()
    {
        $data = $this->validate();

        $member = Member::findOrFail($data['member_id']);

        // SIMPAN FILE
        $buktiPath = null;
        if ($this->bukti_alasan) {
            $buktiPath = $this->bukti_alasan->store('uploads/bukti_izin', 'public');
        }

        $jumlahHari    = (int) $data['jumlah_hari'];
        $tanggalMulai   = Carbon::parse($data['tanggal_mulai'])->startOfDay();
        $tanggalSelesai = (clone $tanggalMulai)->addDays($jumlahHari);

        IzinLatihanModel::create([
            'user_id'               => $member->user_id,
            'jumlah_hari'           => $jumlahHari,
            'tanggal_mulai'         => $tanggalMulai->toDateString(),
            'tanggal_selesai'       => $tanggalSelesai->toDateString(),
            'status'                => 'disetujui',
            'durasi_izin_disetujui' => $jumlahHari,
            'bukti_alasan'          => $buktiPath,
            'alasan'                => $data['alasan'] ?: '[Ditambahkan manual oleh admin]',
            'keterangan_admin'      => 'Izin manual ditambahkan dan langsung disetujui oleh Admin.',
            'tanggal_persetujuan'   => now(),
        ]);

        // UPDATE MEMBERSHIP
        if ($member->tanggal_akhir) {
            $akhirLama = Carbon::parse($member->tanggal_akhir)->startOfDay();
            $akhirBaru = $akhirLama->addDays($jumlahHari);
        } else {
            $akhirBaru = (clone $tanggalSelesai);
        }

        $member->update([
            'tanggal_akhir' => $akhirBaru->toDateString(),
        ]);

        // RESET FORM (STATE LIVEWIRE)
        $this->reset(['member_id', 'jumlah_hari', 'tanggal_mulai', 'alasan', 'bukti_alasan']);
        $this->jumlah_hari   = 1;
        $this->tanggal_mulai = now()->toDateString();

        // KASIH EVENT KE ALPINE BUAT NUTUP MODAL
        $this->dispatch('izin-manual-saved');

        session()->flash('success', 'Izin manual berhasil ditambahkan dan langsung disetujui, membership ikut diperpanjang.');
    }

    /* =========================
     *  APPROVE (Livewire)
     * ========================= */

    public function openApproveModal($id)
    {
        $izin = IzinLatihanModel::with('member')->findOrFail($id);

        $this->approve_id       = $izin->id;
        $this->approved_days    = $izin->jumlah_hari;
        $this->keterangan_admin = null;
    }

    public function approve()
    {
        $this->validate([
            'approved_days'    => 'required|integer|min:0',
            'keterangan_admin' => 'nullable|string|max:1000',
        ], [
            'approved_days.required' => 'Jumlah hari yang disetujui wajib diisi.',
            'approved_days.integer'  => 'Jumlah hari yang disetujui harus berupa angka.',
            'approved_days.min'      => 'Jumlah hari yang disetujui minimal 0.',
        ]);

        $izin   = IzinLatihanModel::with('member')->findOrFail($this->approve_id);
        $member = $izin->member;

        if ($izin->status !== 'pending') {
            session()->flash('error', 'Izin ini sudah diproses sebelumnya.');
            return;
        }

        DB::transaction(function () use ($izin, $member) {
            $approved = (int) $this->approved_days;

            $izin->update([
                'status'                => 'disetujui',
                'durasi_izin_disetujui' => $approved,
                'keterangan_admin'      => $this->keterangan_admin,
                'tanggal_persetujuan'   => now(),
            ]);

            if ($approved > 0 && $member) {
                $currentEnd = Carbon::parse($member->tanggal_akhir ?? now());
                $member->update([
                    'tanggal_akhir' => $currentEnd->addDays($approved)->toDateString(),
                ]);
            }
        });

        session()->flash('success', 'Izin berhasil disetujui.');
    }

    /* =========================
     *  RENDER
     * ========================= */

    public function render()
    {
        $query = IzinLatihanModel::with('member')
            ->where('status', 'pending');

        if ($this->search) {
            $search = $this->search;
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('nama', 'like', $search.'%')
                  ->orWhere('username', 'like', $search.'%');
            });
        }

        switch ($this->sort) {
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

        $members = Member::with('user')
            ->whereHas('user', fn ($q) => $q->where('role', 'user'))
            ->orderBy('nama')
            ->get(['id', 'nama', 'username', 'user_id']);

        return view('livewire.admin.izin-latihan', [
            'daftar_izin' => $query->paginate(15),
            'members'     => $members,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilGym;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilGymController extends Controller
{
    public function index()
    {
        $profil = ProfilGym::first();
        return view('admin.profil_gym.index', compact('profil'));
    }

    public function create()
    {
        $profil = ProfilGym::first();
        if ($profil) {
            return redirect()->route('admin.profil_gym.edit', $profil->id);
        }

        return view('admin.profil_gym.create');
    }

    public function store(Request $request)
    {
        $section = $request->input('_section', 'info');
        $profil  = ProfilGym::first();

        // Kalau data sudah ada, amankan: treat sebagai update profil pertama
        if ($profil) {
            return $this->update($request, $profil);
        }

        // Kalau belum ada profil tapi user submit section kontak, tolak
        if ($section === 'kontak') {
            return back()
                ->withInput()
                ->with('error', 'Silakan simpan Informasi Utama terlebih dahulu (minimal Nama Gym) sebelum mengisi Kontak & Sosial Media.');
        }

        $data = $this->validateBySection($request, 'info');

        // Upload hanya untuk section info
        $data = $this->handleUploads($request, $data, null);

        ProfilGym::create($data);

        return redirect()
            ->route('admin.profil_gym.index')
            ->with('success', 'Profil gym berhasil dibuat.');
    }

    public function show(ProfilGym $profilGym)
    {
        return view('admin.profil_gym.show', compact('profilGym'));
    }

    public function edit(ProfilGym $profilGym)
    {
        return view('admin.profil_gym.edit', compact('profilGym'));
    }

    public function update(Request $request, ProfilGym $profilGym)
    {
        $section = $request->input('_section', 'info');

        $data = $this->validateBySection($request, $section);

        // Upload hanya untuk section info
        if ($section === 'info') {
            $data = $this->handleUploads($request, $data, $profilGym);
        }

        $profilGym->update($data);

        return redirect()
            ->route('admin.profil_gym.index')
            ->with('success', 'Profil gym berhasil diperbarui.');
    }

    public function destroy(ProfilGym $profilGym)
    {
        foreach (['logo', 'favicon', 'hero_image'] as $field) {
            if ($profilGym->$field) {
                ImageUploadService::delete($profilGym->$field);
            }
        }

        $profilGym->delete();

        return redirect()
            ->route('admin.profil_gym.index')
            ->with('success', 'Profil gym berhasil dihapus.');
    }

    /**
     * Validasi berdasarkan section form yang disubmit.
     */
    private function validateBySection(Request $request, string $section): array
    {
        $section = in_array($section, ['info', 'kontak'], true) ? $section : 'info';

        $rulesInfo = [
            'nama'       => 'required|string|max:255',
            'deskripsi'  => 'nullable|string',
            'lokasi'     => 'nullable|string',
            'jam_buka'   => 'nullable',
            'jam_tutup'  => 'nullable',

            // file (opsional)
            'logo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'favicon'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ];

        $rulesKontak = [
            'instagram'    => 'nullable|string|max:255',
            'tiktok'       => 'nullable|string|max:255',
            'youtube'      => 'nullable|string|max:255',
            'facebook'     => 'nullable|string|max:255',
            'whatsapp'     => 'nullable|string|max:30',
            'email_kontak' => 'nullable|email|max:255',
            'maps_url'     => 'nullable|string|max:500',
        ];

        $rules = $section === 'kontak' ? $rulesKontak : $rulesInfo;

        return $request->validate($rules);
    }

    /**
     * Handle upload file + kompres WebP + hapus file lama jika update.
     */
    private function handleUploads(Request $request, array $data, ?ProfilGym $profilGym): array
    {
        $uploadSettings = [
            'logo'       => ['folder' => 'profil_gym/logo', 'maxDim' => 800, 'quality' => 85],
            'favicon'    => ['folder' => 'profil_gym/favicon', 'maxDim' => 256, 'quality' => 90],
            'hero_image' => ['folder' => 'profil_gym/hero', 'maxDim' => 1920, 'quality' => 85],
        ];

        foreach ($uploadSettings as $field => $config) {
            if ($request->hasFile($field)) {
                $oldPath = $profilGym ? $profilGym->$field : null;
                $data[$field] = ImageUploadService::uploadAsWebp(
                    $request->file($field),
                    $config['folder'],
                    $oldPath,
                    $config['maxDim'],
                    $config['quality']
                );
            } else {
                // jangan timpa path lama dengan null
                unset($data[$field]);
            }
        }

        return $data;
    }
}

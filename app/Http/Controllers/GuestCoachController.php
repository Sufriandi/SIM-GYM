<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\ProfilGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuestCoachController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->query('q', '');

        $coaches = Coach::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Siapkan data siap render (tanpa membuka privasi alamat ke publik)
        $coaches->getCollection()->transform(function (Coach $coach) {
            $coach->slug = $this->makeSlug($coach->id, $coach->nama);

            $coach->foto_url = $this->resolvePublicImageUrl(
                $coach->foto,
                'https://placehold.co/900x1100/111827/FACC15?text=' . urlencode($coach->nama ?? 'COACH') . '&font=raleway'
            );

            // WhatsApp link langsung ke Coach pribadi (tanpa perantara gym)
            $coach->wa_direct_url = $this->makeCoachWaUrl($coach->no_hp, $coach->nama);

            return $coach;
        });

        return view('coaches.index', [
            'pageTitle' => 'Coaches',
            'search'    => $search,
            'coaches'   => $coaches,
        ]);
    }

    public function show(string $slug)
    {
        $id = $this->extractIdFromSlug($slug);

        /** @var Coach|null $coach */
        $coach = Coach::query()->find($id);
        if (!$coach) {
            abort(404);
        }

        $coach->slug = $this->makeSlug($coach->id, $coach->nama);
        $coach->foto_url = $this->resolvePublicImageUrl(
            $coach->foto,
            'https://placehold.co/1200x1400/111827/FACC15?text=' . urlencode($coach->nama ?? 'COACH') . '&font=raleway'
        );

        // WhatsApp link langsung ke Coach pribadi (tanpa perantara gym)
        $coach->wa_direct_url = $this->makeCoachWaUrl($coach->no_hp, $coach->nama);

        // Jika slug tidak canonical, redirect 301 ke slug yang benar
        if ($slug !== $coach->slug) {
            return redirect()
                ->route('guest.coaches.show', $coach->slug)
                ->setStatusCode(301);
        }

        return view('coaches.show', [
            'pageTitle' => 'Coach Detail',
            'coach'     => $coach,
        ]);
    }

    private function makeSlug(int $id, ?string $name): string
    {
        return $id . '-' . Str::slug($name ?: 'coach');
    }

    private function extractIdFromSlug(string $slug): int
    {
        // slug format: "{id}-{nama}"
        $idPart = explode('-', $slug, 2)[0] ?? '0';
        return (int) $idPart;
    }

    private function makeCoachWaUrl(?string $phone, ?string $coachName): ?string
    {
        $wa = $this->normalizeWa($phone);
        if (!$wa) return null;

        $trimmed = trim((string) $coachName);
        $displayName = Str::startsWith(strtolower($trimmed), 'coach ')
            ? $trimmed
            : 'Coach ' . $trimmed;

        $msg = "Halo {$displayName}, saya member BETA GYM ingin berkonsultasi mengenai program dan jadwal latihan.";
        return "https://wa.me/{$wa}?text=" . rawurlencode($msg);
    }

    private function normalizeWa(?string $phone): ?string
    {
        $phone = $phone ? preg_replace('/\D/', '', $phone) : null;
        if (!$phone) return null;

        // 08xxx -> 628xxx
        if (Str::startsWith($phone, '0')) $phone = '62' . substr($phone, 1);

        // 8xxx -> 628xxx (kalau user input tanpa 0)
        if (Str::startsWith($phone, '8')) $phone = '62' . $phone;

        return $phone;
    }

    private function resolvePublicImageUrl(?string $path, string $fallback): string
    {
        $path = trim((string) $path);
        if ($path === '') return $fallback;

        // kalau sudah URL penuh
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        // kalau DB simpan "storage/coach/xxx.jpg"
        if (Str::startsWith($path, 'storage/')) {
            return url('/' . $path);
        }

        // kalau DB simpan "public/coach/xxx.jpg"
        if (Str::startsWith($path, 'public/')) {
            $path = Str::after($path, 'public/');
        }

        // normal: "coach/xxx.jpg" -> "/storage/coach/xxx.jpg"
        return Storage::url($path);
    }
}


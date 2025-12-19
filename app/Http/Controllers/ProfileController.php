<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'pageTitle' => 'Pengaturan Profil',
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validateWithBag('profile', [
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'regex:/^\+?\d{10,15}$/'],
            'alamat'        => ['nullable', 'string', 'max:500'],
            'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'perempuan'])],
            'foto'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB
        ]);

        
        $data['username'] = $user->username;

        // Jika email berubah, reset verifikasi
        if ($data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        // Upload foto baru (hapus foto lama jika ada)
        if ($request->hasFile('foto')) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }

            $data['foto'] = $request->file('foto')->store('users/foto', 'public');
        }

        $user->fill($data)->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('profile.edit', ['tab' => 'security'])
            ->with('password_success', true)
            ->with('success', 'Password berhasil diperbarui.');
    }
}

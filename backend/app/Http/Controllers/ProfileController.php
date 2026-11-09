<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Menampilkan halaman profil
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    // Menyimpan perubahan profil
    public function update(Request $request)
    {
        $user = $request->user();

        // Validasi data
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'no_hp' => [
                'nullable',
                'regex:/^[0-9]{10,15}$/',
            ],

            'foto_profile' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'no_hp.regex' => 'Nomor HP harus 10 sampai 15 digit angka.',
            'foto_profile.image' => 'File harus berupa gambar.',
            'foto_profile.mimes' => 'Foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto_profile.max' => 'Ukuran foto maksimal 2 MB.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Update nama, email, dan nomor HP
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->no_hp = $validated['no_hp'] ?? null;

        // Update foto profil
        if ($request->hasFile('foto_profile')) {

            // Simpan foto baru terlebih dahulu
            $fotoBaru = $request->file('foto_profile')
                ->store('profiles', 'public');

            // Ambil alamat foto lama
            $fotoLama = $user->foto_profile;

            // Simpan alamat foto baru ke database
            $user->foto_profile = $fotoBaru;

            // Hapus foto lama
            if ($fotoLama) {

                // Hilangkan awalan storage/ jika ada
                $fotoLama = preg_replace(
                    '#^storage/#',
                    '',
                    $fotoLama
                );

                if (Storage::disk('public')->exists($fotoLama)) {
                    Storage::disk('public')->delete($fotoLama);
                }
            }
        }

        // Update password jika diisi
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Simpan perubahan
        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
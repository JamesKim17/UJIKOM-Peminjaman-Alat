<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Memproses login pengguna.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            // Memperbarui session setelah login berhasil.
            $request->session()->regenerate();

            $user = Auth::user();
            $role = strtolower(trim($user->role ?? ''));

            // Mengarahkan pengguna berdasarkan role.
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($role === 'petugas') {
                return redirect()->route('petugas.peminjaman.index');
            }

            if ($role === 'peminjam') {
                return redirect()->route('peminjam.katalog');
            }

            // Logout jika role tidak dikenali.
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Hak akses tidak valid.');
        }

        return back()
            ->withErrors([
                'email' => 'Email atau password salah.',
            ])
            ->onlyInput('email');
    }

    /**
     * Memproses logout pengguna.
     */
    public function logout(Request $request)
    {
        // Mengeluarkan pengguna dari autentikasi.
        Auth::guard('web')->logout();

        // Menghapus session lama dan membuat token CSRF baru.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke halaman login.
        return redirect()->route('login')
            ->with('success', 'Berhasil logout. Silakan login kembali.');
    }
}

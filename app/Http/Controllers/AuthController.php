<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login dan register.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        // Ganti pengecekan session session()->has('user') dengan Auth::check()
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $activeTab = $request->query('tab', 'login');
        $success = $request->query('registered') ? 'Akun berhasil dibuat! Silakan masuk dengan akun baru Anda.' : session('success', '');
        $error = session('error', '');

        return view('login', compact('activeTab', 'success', 'error'));
    }

    /**
     * Proses autentikasi login atau registrasi pengguna.
     */
    public function handle(Request $request): RedirectResponse
    {
        $action = $request->input('action', 'login');

        if ($action === 'register') {
            return $this->register($request);
        }

        return $this->login($request);
    }

    /**
     * Proses login pengguna menggunakan database.
     */
    protected function login(Request $request): RedirectResponse
    {
        $id = trim((string) $request->input('username', ''));
        $pw = (string) $request->input('password', '');

        if (empty($id) || empty($pw)) {
            return redirect('/login')
                ->withInput($request->only('username'))
                ->with('error', 'Username/Email dan kata sandi wajib diisi.')
                ->with('tab', 'login');
        }

        // Tentukan apakah input berupa email atau username
        $fieldType = filter_var($id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Coba autentikasi menggunakan Laravel Auth
        if (Auth::attempt([$fieldType => $id, 'password' => $pw])) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return redirect('/login')
            ->withInput($request->only('username'))
            ->with('error', 'Email / Username atau kata sandi salah.')
            ->with('tab', 'login');
    }

    /**
     * Proses pendaftaran akun pengguna baru ke database.
     */
    protected function register(Request $request): RedirectResponse
    {
        $nama = trim((string) $request->input('nama', ''));
        $uname = trim((string) $request->input('username', ''));
        $email = trim((string) $request->input('email', ''));
        $no_hp = trim((string) $request->input('no_hp', ''));
        $nama_usaha = trim((string) $request->input('nama_usaha', ''));
        $kategori = trim((string) $request->input('kategori', ''));
        $alamat = trim((string) $request->input('alamat', ''));
        $pw = (string) $request->input('password', '');
        $pw2 = (string) $request->input('password2', '');

        $error = '';

        if (! $nama || ! $uname || ! $email || ! $pw) {
            $error = 'Nama lengkap, username, email, dan kata sandi wajib diisi.';
        } elseif ($pw !== $pw2) {
            $error = 'Konfirmasi kata sandi tidak cocok.';
        } elseif (strlen($pw) < 6) {
            $error = 'Kata sandi minimal 6 karakter.';
        } elseif (User::where('username', $uname)->exists()) {
            $error = 'Username sudah terdaftar.';
        } elseif (User::where('email', $email)->exists()) {
            $error = 'Email sudah terdaftar.';
        }

        if ($error !== '') {
            return redirect('/login?tab=register')
                ->withInput($request->except('password', 'password2'))
                ->with('error', $error)
                ->with('tab', 'register');
        }

        // Simpan user baru ke database dengan password yang di-hash
        User::create([
            'nama' => $nama,
            'username' => $uname,
            'email' => $email,
            'no_hp' => $no_hp,
            'nama_usaha' => $nama_usaha,
            'kategori' => $kategori,
            'alamat' => $alamat,
            'password' => Hash::make($pw),
            'foto' => '',
        ]);

        return redirect('/login')
            ->with('success', 'Akun berhasil didaftarkan! Silakan masuk.')
            ->with('tab', 'login');
    }

    /**
     * Proses logout pengguna.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
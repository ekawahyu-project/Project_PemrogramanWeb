<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login dan register.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (session()->has('user')) {
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
     * Proses login pengguna.
     */
    protected function login(Request $request): RedirectResponse
    {
        $id = trim((string) $request->input('username', ''));
        $pw = (string) $request->input('password', '');

        $users = session('users', []);

        foreach ($users as $uname => $data) {
            $emailMatch = strtolower((string) ($data['email'] ?? '')) === strtolower($id);
            $unameMatch = (string) $uname === $id;

            if (($unameMatch || $emailMatch) && (string) ($data['password'] ?? '') === $pw) {
                session([
                    'user' => $uname,
                    'user_nama' => $data['nama'] ?? 'User',
                    'profil' => [
                        'no_hp' => $data['no_hp'] ?? '',
                        'nama_usaha' => $data['nama_usaha'] ?? '',
                        'kategori' => $data['kategori'] ?? '',
                        'alamat' => $data['alamat'] ?? '',
                        'foto' => $data['foto'] ?? '',
                    ],
                ]);

                return redirect('/dashboard');
            }
        }

        return redirect('/login')
            ->withInput($request->only('username'))
            ->with('error', 'Email / Username atau kata sandi salah.')
            ->with('tab', 'login');
    }

    /**
     * Proses pendaftaran akun pengguna baru.
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

        $users = session('users', []);

        if (! $nama || ! $uname || ! $email || ! $pw) {
            $error = 'Nama lengkap, username, email, dan kata sandi wajib diisi.';
        } elseif ($pw !== $pw2) {
            $error = 'Konfirmasi kata sandi tidak cocok.';
        } elseif (strlen($pw) < 6) {
            $error = 'Kata sandi minimal 6 karakter.';
        } elseif (isset($users[$uname])) {
            $error = 'Username sudah terdaftar.';
        } else {
            $error = '';
            foreach ($users as $u) {
                if (strtolower((string) ($u['email'] ?? '')) === strtolower($email)) {
                    $error = 'Email sudah terdaftar.';
                    break;
                }
            }
        }

        if ($error !== '') {
            return redirect('/login?tab=register')
                ->withInput($request->except('password', 'password2'))
                ->with('error', $error)
                ->with('tab', 'register');
        }

        $users[$uname] = [
            'nama' => $nama,
            'username' => $uname,
            'email' => $email,
            'no_hp' => $no_hp,
            'nama_usaha' => $nama_usaha,
            'kategori' => $kategori,
            'alamat' => $alamat,
            'password' => $pw,
            'foto' => '',
        ];

        session(['users' => $users]);

        return redirect('/login')
            ->with('success', 'Akun berhasil didaftarkan! Silakan masuk.')
            ->with('tab', 'login');
    }

    /**
     * Proses logout pengguna.
     */
    public function logout(): RedirectResponse
    {
        session()->forget(['user', 'user_nama']);

        return redirect('/login');
    }
}

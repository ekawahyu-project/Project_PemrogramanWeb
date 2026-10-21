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

        // Panggil berkas koneksi basis data PDO sesuai spesifikasi materi kuliah
        /** @var \PDO $pdo */
        $pdo = require base_path('koneksi.php');

        try {
            // Gunakan PDO Prepared Statement untuk mencegah SQL Injection
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u OR email = :e LIMIT 1');
            $stmt->execute([
                'u' => $id,
                'e' => $id,
            ]);
            $user = $stmt->fetch();

            // Verifikasi password menggunakan password_verify()
            if ($user && password_verify($pw, (string) ($user['password'] ?? ''))) {
                session([
                    'user' => $user['username'],
                    'user_nama' => $user['nama'] ?? 'User',
                    'profil' => [
                        'no_hp' => $user['no_hp'] ?? '',
                        'nama_usaha' => $user['nama_usaha'] ?? '',
                        'kategori' => $user['kategori'] ?? '',
                        'alamat' => $user['alamat'] ?? '',
                        'foto' => $user['foto'] ?? '',
                    ],
                ]);

                // Sinkronkan ke session users untuk kompatibilitas modul lain
                $users = session('users', []);
                $users[$user['username']] = $user;
                session(['users' => $users]);

                return redirect('/dashboard');
            }
        } catch (\PDOException $e) {
            error_log('Database login error: '.$e->getMessage());

            return redirect('/login')
                ->withInput($request->only('username'))
                ->with('error', 'Terjadi kesalahan sistem pada basis data. Silakan coba beberapa saat lagi.')
                ->with('tab', 'login');
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

        // Panggil berkas koneksi basis data PDO
        /** @var \PDO $pdo */
        $pdo = require base_path('koneksi.php');

        $error = '';
        if (! $nama || ! $uname || ! $email || ! $pw) {
            $error = 'Nama lengkap, username, email, dan kata sandi wajib diisi.';
        } elseif ($pw !== $pw2) {
            $error = 'Konfirmasi kata sandi tidak cocok.';
        } elseif (strlen($pw) < 6) {
            $error = 'Kata sandi minimal 6 karakter.';
        } else {
            try {
                // Cek apakah username atau email sudah ada di tabel users
                $checkStmt = $pdo->prepare('SELECT id, username, email FROM users WHERE username = :u OR email = :e LIMIT 1');
                $checkStmt->execute([
                    'u' => $uname,
                    'e' => $email,
                ]);
                $existing = $checkStmt->fetch();

                if ($existing) {
                    if (strtolower((string) $existing['username']) === strtolower($uname)) {
                        $error = 'Username sudah terdaftar.';
                    } else {
                        $error = 'Email sudah terdaftar.';
                    }
                }
            } catch (\PDOException $e) {
                error_log('Database check error: '.$e->getMessage());
                $error = 'Gagal memeriksa ketersediaan akun di basis data.';
            }
        }

        if ($error !== '') {
            return redirect('/login?tab=register')
                ->withInput($request->except('password', 'password2'))
                ->with('error', $error)
                ->with('tab', 'register');
        }

        try {
            // Amankan password dengan password_hash() sebelum disimpan ke tabel users
            $hashedPassword = password_hash($pw, PASSWORD_DEFAULT);

            $insertStmt = $pdo->prepare('
                INSERT INTO users (nama, username, email, password, no_hp, nama_usaha, kategori, alamat, foto)
                VALUES (:nama, :username, :email, :password, :no_hp, :nama_usaha, :kategori, :alamat, :foto)
            ');

            $insertStmt->execute([
                'nama' => $nama,
                'username' => $uname,
                'email' => $email,
                'password' => $hashedPassword,
                'no_hp' => $no_hp,
                'nama_usaha' => $nama_usaha,
                'kategori' => $kategori,
                'alamat' => $alamat,
                'foto' => '',
            ]);

            // Sinkronkan ke session users
            $users = session('users', []);
            $users[$uname] = [
                'nama' => $nama,
                'username' => $uname,
                'email' => $email,
                'password' => $hashedPassword,
                'no_hp' => $no_hp,
                'nama_usaha' => $nama_usaha,
                'kategori' => $kategori,
                'alamat' => $alamat,
                'foto' => '',
            ];
            session(['users' => $users]);
        } catch (\PDOException $e) {
            error_log('Database insert error: '.$e->getMessage());

            return redirect('/login?tab=register')
                ->withInput($request->except('password', 'password2'))
                ->with('error', 'Gagal menyimpan akun baru ke basis data.')
                ->with('tab', 'register');
        }

        return redirect('/login')
            ->with('success', 'Akun berhasil didaftarkan! Silakan masuk.')
            ->with('tab', 'login');
    }

    /**
     * Proses logout pengguna.
     */
    public function logout(Request $request): RedirectResponse
    {
        session()->forget(['user', 'user_nama', 'profil']);

        if (isset($_SESSION)) {
            unset($_SESSION['user'], $_SESSION['user_nama'], $_SESSION['profil']);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

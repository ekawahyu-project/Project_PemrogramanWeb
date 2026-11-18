<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login dan register.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $activeTab = $request->query('tab', session('tab', 'login'));
        $success = $request->query('registered')
            ? 'Akun berhasil dibuat! Silakan masuk dengan akun baru Anda.'
            : session('success', '');
        $error = session('error', '');
        $kategoriList = User::BUSINESS_CATEGORIES;

        return view('login', compact('activeTab', 'success', 'error', 'kategoriList'));
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
     * Proses login pengguna menggunakan model Eloquent User.
     */
    protected function login(Request $request): RedirectResponse
    {
        $identifier = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        $user = User::where('username', $identifier)
            ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect('/dashboard');
        }

        return redirect('/login')
            ->withInput($request->only('username'))
            ->with('error', 'Email / Username atau kata sandi salah.')
            ->with('tab', 'login');
    }

    /**
     * Proses pendaftaran akun pengguna baru ke database melalui model User.
     */
    protected function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'password2' => ['required', 'same:password'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_usaha' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama lengkap, username, email, dan kata sandi wajib diisi.',
            'username.required' => 'Nama lengkap, username, email, dan kata sandi wajib diisi.',
            'username.unique' => 'Username sudah terdaftar.',
            'email.required' => 'Nama lengkap, username, email, dan kata sandi wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Nama lengkap, username, email, dan kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password2.required' => 'Konfirmasi kata sandi tidak cocok.',
            'password2.same' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if ($validator->fails()) {
            return redirect('/login?tab=register')
                ->withInput($request->except('password', 'password2'))
                ->with('error', $validator->errors()->first())
                ->with('tab', 'register');
        }

        $validated = $validator->validated();

        User::create([
            'nama' => trim($validated['nama']),
            'username' => trim($validated['username']),
            'email' => trim($validated['email']),
            'password' => $validated['password'],
            'no_hp' => trim((string) ($validated['no_hp'] ?? '')) ?: null,
            'nama_usaha' => trim((string) ($validated['nama_usaha'] ?? '')) ?: null,
            'kategori_usaha' => trim((string) ($validated['kategori'] ?? '')) ?: null,
            'alamat_usaha' => trim((string) ($validated['alamat'] ?? '')) ?: null,
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

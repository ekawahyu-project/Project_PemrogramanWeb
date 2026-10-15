<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the profile page.
     */
    public function index(Request $request): View
    {
        $usernameLogin = session('user');
        $users = session('users', []);

        /** @var array<string,mixed> $akun */
        $akun = $users[$usernameLogin] ?? [];

        // Inisialisasi profil di session jika belum ada
        if (! session()->has('profil')) {
            session(['profil' => [
                'no_hp' => $akun['no_hp'] ?? '',
                'nama_usaha' => $akun['nama_usaha'] ?? '',
                'kategori' => $akun['kategori'] ?? '',
                'alamat' => $akun['alamat'] ?? '',
                'foto' => $akun['foto'] ?? '',
            ]]);
        }

        $profil = session('profil', []);

        return view('profil', $this->buildViewData($akun, $profil, $usernameLogin));
    }

    /**
     * Handle profile save and password change.
     */
    public function handle(Request $request): RedirectResponse|View
    {
        $usernameLogin = session('user');
        $users = session('users', []);

        /** @var array<string,mixed> $akun */
        $akun = $users[$usernameLogin] ?? [];
        $profil = session('profil', []);

        $action = $request->input('action', 'save');

        if ($action === 'save') {
            return $this->handleSave($request, $usernameLogin, $akun, $profil);
        }

        return redirect('/profil');
    }

    /**
     * Process the "save" profile action.
     *
     * @param  array<string,mixed>  $akun
     * @param  array<string,mixed>  $profil
     */
    private function handleSave(
        Request $request,
        string $usernameLogin,
        array $akun,
        array $profil
    ): RedirectResponse {
        $nama = trim($request->input('nama', ''));
        $email = trim($request->input('email', ''));
        $noHp = trim($request->input('no_hp', ''));
        $namaUsaha = trim($request->input('nama_usaha', ''));
        $kategori = trim($request->input('kategori', ''));
        $alamat = trim($request->input('alamat', ''));

        if (! $nama || ! $email) {
            return redirect('/profil')
                ->with('flash', [
                    'type' => 'error',
                    'message' => 'Nama lengkap dan alamat email wajib diisi.',
                ]);
        }

        // Cek duplikasi email
        $users = session('users', []);

        foreach ($users as $uKey => $uData) {
            if (
                $uKey !== $usernameLogin &&
                strtolower($uData['email'] ?? '') === strtolower($email)
            ) {
                return redirect('/profil')
                    ->with('flash', [
                        'type' => 'error',
                        'message' => 'Alamat email sudah digunakan oleh akun lain.',
                    ]);
            }
        }

        // Proses upload foto profil
        if ($request->hasFile('foto_profil') && $request->file('foto_profil')->isValid()) {
            $file = $request->file('foto_profil');
            $ext = strtolower($file->getClientOriginalExtension());

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (! in_array($ext, $allowedExtensions, true)) {
                return redirect('/profil')
                    ->with('flash', [
                        'type' => 'error',
                        'message' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
                    ]);
            }

            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect('/profil')
                    ->with('flash', [
                        'type' => 'error',
                        'message' => 'Ukuran foto maksimal 2 MB.',
                    ]);
            }

            $safeUsername = preg_replace('/[^a-zA-Z0-9_-]/', '', $usernameLogin);
            $newFileName = 'profile_'.$safeUsername.'_'.bin2hex(random_bytes(8)).'.'.$ext;
            $uploadDir = public_path('uploads');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus foto lama
            $oldFoto = $users[$usernameLogin]['foto'] ?? '';

            if (
                ! empty($oldFoto) &&
                str_starts_with($oldFoto, 'uploads/') &&
                file_exists(public_path($oldFoto))
            ) {
                unlink(public_path($oldFoto));
            }

            $file->move($uploadDir, $newFileName);

            $newFotoPath = 'uploads/'.$newFileName;
            $users[$usernameLogin]['foto'] = $newFotoPath;
            $profil['foto'] = $newFotoPath;
        }

        // Update data akun
        $users[$usernameLogin]['nama'] = $nama;
        $users[$usernameLogin]['email'] = $email;
        $users[$usernameLogin]['no_hp'] = $noHp;
        $users[$usernameLogin]['nama_usaha'] = $namaUsaha;
        $users[$usernameLogin]['kategori'] = $kategori;
        $users[$usernameLogin]['alamat'] = $alamat;

        // Update profil session
        $profil['no_hp'] = $noHp;
        $profil['nama_usaha'] = $namaUsaha;
        $profil['kategori'] = $kategori;
        $profil['alamat'] = $alamat;

        session([
            'users' => $users,
            'user_nama' => $nama,
            'profil' => $profil,
        ]);

        return redirect('/profil')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Profil dan informasi usaha berhasil diperbarui!',
            ]);
    }

    /**
     * Build data array for the profile view.
     *
     * @param  array<string,mixed>  $akun
     * @param  array<string,mixed>  $profil
     * @return array<string,mixed>
     */
    private function buildViewData(array $akun, array $profil, string $usernameLogin): array
    {
        $nama = $akun['nama'] ?? (session('user_nama') ?? '');
        $username = $akun['username'] ?? $usernameLogin;
        $email = $akun['email'] ?? '';
        $noHp = ! empty($akun['no_hp']) ? $akun['no_hp'] : ($profil['no_hp'] ?? '');
        $namaUsaha = ! empty($akun['nama_usaha']) ? $akun['nama_usaha'] : ($profil['nama_usaha'] ?? '');
        $kategori = ! empty($akun['kategori']) ? $akun['kategori'] : ($profil['kategori'] ?? '');
        $alamat = ! empty($akun['alamat']) ? $akun['alamat'] : ($profil['alamat'] ?? '');
        $foto = ! empty($akun['foto']) ? $akun['foto'] : ($profil['foto'] ?? '');

        $fotoUrl = '';

        if (! empty($foto) && file_exists(public_path($foto))) {
            $fotoUrl = asset($foto).'?v='.filemtime(public_path($foto));
        }

        $initial = ! empty($nama) ? strtoupper(mb_substr($nama, 0, 1)) : '?';

        $kategoriOptions = [
            '',
            'Makanan & Minuman',
            'Fashion',
            'Kerajinan',
            'Jasa',
            'Kecantikan',
            'Elektronik',
            'Lainnya',
        ];

        return [
            'currentPage' => 'profil',
            'nama' => $nama,
            'username' => $username,
            'email' => $email,
            'noHp' => $noHp,
            'namaUsaha' => $namaUsaha,
            'kategori' => $kategori,
            'alamat' => $alamat,
            'fotoUrl' => $fotoUrl,
            'initial' => $initial,
            'kategoriOptions' => $kategoriOptions,
        ];
    }
}

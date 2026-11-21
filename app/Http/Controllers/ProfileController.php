<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the profile page.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('profil', $this->buildViewData($user));
    }

    /**
     * Handle profile actions: save, delete photo, delete account.
     */
    public function handle(Request $request): RedirectResponse
    {
        $action = $request->input('action', 'save');

        if ($action === 'save') {
            return $this->handleSave($request);
        }

        if ($action === 'hapus_foto') {
            return $this->handleDeletePhoto($request);
        }

        if ($action === 'hapus_akun') {
            return $this->handleDeleteAccount($request);
        }

        return redirect('/profil');
    }

    /**
     * Hapus foto profil pengguna (file + kolom foto di database).
     */
    private function handleDeletePhoto(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (empty($user->foto)) {
            return redirect('/profil')->with('flash', [
                'type' => 'error',
                'message' => 'Anda belum memiliki foto profil untuk dihapus.',
            ]);
        }

        $user->deletePhotoFile();
        $user->update(['foto' => null]);

        return redirect('/profil')->with('flash', [
            'type' => 'success',
            'message' => 'Foto profil berhasil dihapus.',
        ]);
    }

    /**
     * Hapus akun pengguna secara permanen setelah verifikasi kata sandi.
     */
    private function handleDeleteAccount(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $password = (string) $request->input('password_konfirmasi', '');

        if ($password === '') {
            return redirect('/profil')->with('flash', [
                'type' => 'error',
                'message' => 'Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun.',
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            return redirect('/profil')->with('flash', [
                'type' => 'error',
                'message' => 'Kata sandi salah. Akun tidak dihapus.',
            ]);
        }

        $user->deletePhotoFile();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Akun Anda beserta seluruh datanya berhasil dihapus.')
            ->with('tab', 'login');
    }

    /**
     * Process the "save" profile action and persist to database via User model.
     */
    private function handleSave(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $nama = trim((string) $request->input('nama', ''));
        $email = trim((string) $request->input('email', ''));

        if ($nama === '' || $email === '') {
            return redirect('/profil')->with('flash', [
                'type' => 'error',
                'message' => 'Nama lengkap dan alamat email wajib diisi.',
            ]);
        }

        $validator = Validator::make([
            'nama' => $nama,
            'email' => $email,
            'no_hp' => trim((string) $request->input('no_hp', '')),
            'nama_usaha' => trim((string) $request->input('nama_usaha', '')),
            'kategori' => trim((string) $request->input('kategori', '')),
            'alamat' => trim((string) $request->input('alamat', '')),
            'foto_profil' => $request->file('foto_profil'),
        ], [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_usaha' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan oleh akun lain.',
            'foto_profil.image' => 'File foto profil tidak valid.',
            'foto_profil.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto_profil.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        if ($validator->fails()) {
            return redirect('/profil')->with('flash', [
                'type' => 'error',
                'message' => $validator->errors()->first(),
            ]);
        }

        $validated = $validator->validated();
        $fotoPath = $user->foto;

        if ($request->hasFile('foto_profil') && $request->file('foto_profil')->isValid()) {
            $file = $request->file('foto_profil');
            $user->deletePhotoFile();

            if ($this->shouldStorePhotoAsDataUri()) {
                $mime = $file->getMimeType() ?: 'image/jpeg';
                $contents = file_get_contents($file->getRealPath());

                if ($contents !== false) {
                    $fotoPath = 'data:'.$mime.';base64,'.base64_encode($contents);
                }
            } else {
                $ext = strtolower($file->getClientOriginalExtension());
                $safeUsername = preg_replace('/[^a-zA-Z0-9_-]/', '', $user->username);
                $newFileName = 'profile_'.$safeUsername.'_'.bin2hex(random_bytes(8)).'.'.$ext;
                $uploadDir = public_path('uploads');

                if (! is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file->move($uploadDir, $newFileName);
                $fotoPath = 'uploads/'.$newFileName;
            }
        }

        $user->update([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?: null,
            'nama_usaha' => $validated['nama_usaha'] ?: null,
            'kategori_usaha' => $validated['kategori'] ?: null,
            'alamat_usaha' => $validated['alamat'] ?: null,
            'foto' => $fotoPath,
        ]);

        return redirect('/profil')->with('flash', [
            'type' => 'success',
            'message' => 'Profil dan informasi usaha berhasil diperbarui!',
        ]);
    }

    /**
     * Tentukan apakah foto profil disimpan sebagai Data URI (untuk lingkungan serverless read-only seperti Vercel).
     */
    private function shouldStorePhotoAsDataUri(): bool
    {
        return isset($_ENV['VERCEL'])
            || isset($_SERVER['VERCEL'])
            || ! is_writable(public_path());
    }

    /**
     * Build data array for the profile view.
     *
     * @return array<string, mixed>
     */
    private function buildViewData(User $user): array
    {
        return [
            'currentPage' => 'profil',
            'flash' => session('flash'),
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'noHp' => $user->no_hp ?? '',
            'namaUsaha' => $user->nama_usaha ?? '',
            'kategori' => $user->kategori_usaha ?? '',
            'alamat' => $user->alamat_usaha ?? '',
            'fotoUrl' => $user->photoUrl(),
            'initial' => $user->initials(),
            'kategoriOptions' => array_merge([''], User::BUSINESS_CATEGORIES),
        ];
    }
}

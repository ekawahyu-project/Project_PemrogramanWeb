<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Handle profile save action.
     */
    public function handle(Request $request): RedirectResponse
    {
        $action = $request->input('action', 'save');

        if ($action === 'save') {
            return $this->handleSave($request);
        }

        return redirect('/profil');
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
            $ext = strtolower($file->getClientOriginalExtension());
            $safeUsername = preg_replace('/[^a-zA-Z0-9_-]/', '', $user->username);
            $newFileName = 'profile_'.$safeUsername.'_'.bin2hex(random_bytes(8)).'.'.$ext;
            $uploadDir = public_path('uploads');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (
                ! empty($user->foto) &&
                str_starts_with($user->foto, 'uploads/') &&
                file_exists(public_path($user->foto))
            ) {
                unlink(public_path($user->foto));
            }

            $file->move($uploadDir, $newFileName);
            $fotoPath = 'uploads/'.$newFileName;
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

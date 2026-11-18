<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'username',
    'password',
    'nama',
    'email',
    'no_hp',
    'nama_usaha',
    'kategori_usaha',
    'alamat_usaha',
    'foto',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Daftar kategori usaha yang tersedia.
     *
     * @var array<int, string>
     */
    public const BUSINESS_CATEGORIES = [
        'Makanan & Minuman',
        'Fashion',
        'Kerajinan',
        'Jasa',
        'Kecantikan',
        'Elektronik',
        'Lainnya',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke laporan yang dibuat oleh pengguna.
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Relasi ke catatan hutang & piutang milik pengguna.
     *
     * @return HasMany<Debt, $this>
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    /**
     * Hapus file foto profil dari folder public/uploads jika ada.
     */
    public function deletePhotoFile(): void
    {
        if (
            ! empty($this->foto) &&
            str_starts_with($this->foto, 'uploads/') &&
            file_exists(public_path($this->foto))
        ) {
            unlink(public_path($this->foto));
        }
    }

    /**
     * Ambil URL publik foto profil jika tersedia.
     */
    public function photoUrl(): string
    {
        if (! empty($this->foto) && file_exists(public_path($this->foto))) {
            return asset($this->foto).'?v='.filemtime(public_path($this->foto));
        }

        return '';
    }

    /**
     * Ambil huruf inisial nama pengguna.
     */
    public function initials(): string
    {
        return ! empty($this->nama) ? strtoupper(mb_substr($this->nama, 0, 1)) : '?';
    }
}

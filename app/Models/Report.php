<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'judul',
    'kategori',
    'tanggal_dari',
    'tanggal_sampai',
    'status',
    'catatan',
    'pembuat',
])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    public const CATEGORIES = [
        'Evaluasi Bulanan',
        'Penjualan & Produk',
        'Keuangan & Kas',
        'Operasional & Stok',
    ];

    /**
     * @var array<int, string>
     */
    public const STATUSES = [
        'Selesai',
        'Dalam Tinjauan',
        'Draft',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_dari' => 'date',
            'tanggal_sampai' => 'date',
        ];
    }

    /**
     * Pengguna pembuat laporan.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

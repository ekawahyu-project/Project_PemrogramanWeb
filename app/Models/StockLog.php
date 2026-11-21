<?php

namespace App\Models;

use Database\Factories\StockLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'product_id',
    'tanggal',
    'jenis',
    'jumlah',
    'keterangan',
])]
class StockLog extends Model
{
    /** @use HasFactory<StockLogFactory> */
    use HasFactory;

    public const TYPE_IN = 'Masuk';

    public const TYPE_OUT = 'Keluar';

    /**
     * @var array<int, string>
     */
    public const TYPES = [
        self::TYPE_IN,
        self::TYPE_OUT,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'integer',
        ];
    }

    /**
     * Pengguna pemilik riwayat mutasi stok.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Produk yang mengalami mutasi stok.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isIncoming(): bool
    {
        return $this->jenis === self::TYPE_IN;
    }
}

<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nama',
    'kategori',
    'harga_beli',
    'harga_jual',
    'satuan',
    'stok',
    'stok_min',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    public const CATEGORIES = [
        'Makanan',
        'Minuman',
        'Fashion',
        'Kecantikan',
        'Bahan Baku',
        'Kesehatan & Herbal',
        'Perlengkapan Rumah Tangga',
        'Lainnya',
    ];

    /**
     * @var array<int, string>
     */
    public const UNITS = [
        'pcs',
        'pack',
        'kg',
        'gram',
        'liter',
        'ml',
        'box',
        'lusin',
        'meter',
        'lembar',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_beli' => 'integer',
            'harga_jual' => 'integer',
            'stok' => 'integer',
            'stok_min' => 'integer',
        ];
    }

    /**
     * Relasi transaksi yang terkait dengan produk.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relasi riwayat mutasi stok produk.
     *
     * @return HasMany<StockLog, $this>
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    /**
     * Scope produk dengan stok menipis atau habis.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    #[Scope]
    protected function lowStock(Builder $query): Builder
    {
        return $query->whereColumn('stok', '<=', 'stok_min');
    }

    public function isOutOfStock(): bool
    {
        return $this->stok <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stok <= $this->stok_min;
    }

    public function stockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'Habis';
        }

        if ($this->isLowStock()) {
            return 'Menipis';
        }

        return 'Normal';
    }

    public function recommendedRestockAmount(): int
    {
        return max(0, ($this->stok_min - $this->stok) + ($this->stok_min * 2));
    }

    public function stockProgressPercentage(): float|int
    {
        if ($this->stok_min <= 0) {
            return 100;
        }

        return min(100, ($this->stok / max($this->stok_min * 2, 1)) * 100);
    }
}

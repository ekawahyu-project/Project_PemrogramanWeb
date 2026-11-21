<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'product_id',
    'tanggal',
    'keterangan',
    'jenis',
    'jumlah',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const TYPE_INCOME = 'Pemasukan';

    public const TYPE_EXPENSE = 'Pengeluaran';

    /**
     * @var array<int, string>
     */
    public const TYPES = [
        self::TYPE_INCOME,
        self::TYPE_EXPENSE,
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
     * Pengguna pemilik transaksi.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Produk terkait (opsional).
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope transaksi pemasukan.
     *
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    #[Scope]
    protected function income(Builder $query): Builder
    {
        return $query->where('jenis', self::TYPE_INCOME);
    }

    /**
     * Scope transaksi pengeluaran.
     *
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    #[Scope]
    protected function expense(Builder $query): Builder
    {
        return $query->where('jenis', self::TYPE_EXPENSE);
    }

    public function isIncome(): bool
    {
        return $this->jenis === self::TYPE_INCOME;
    }
}

<?php

namespace App\Models;

use Database\Factories\DebtFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'nama_pihak',
    'no_hp',
    'jenis',
    'jumlah',
    'dibayar',
    'tanggal',
    'jatuh_tempo',
    'status',
    'keterangan',
])]
class Debt extends Model
{
    /** @use HasFactory<DebtFactory> */
    use HasFactory;

    public const TYPE_RECEIVABLE = 'Piutang';

    public const TYPE_PAYABLE = 'Hutang';

    /**
     * @var array<int, string>
     */
    public const TYPES = [
        self::TYPE_RECEIVABLE,
        self::TYPE_PAYABLE,
    ];

    public const STATUS_UNPAID = 'Belum Lunas';

    public const STATUS_PARTIAL = 'Dicicil';

    public const STATUS_PAID = 'Lunas';

    /**
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_UNPAID,
        self::STATUS_PARTIAL,
        self::STATUS_PAID,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'dibayar' => 'integer',
            'tanggal' => 'date',
            'jatuh_tempo' => 'date',
        ];
    }

    /**
     * Pemilik (pengguna yang login) dari catatan hutang / piutang ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hitung status otomatis berdasarkan nominal jumlah dan dibayar.
     */
    public static function resolveStatus(int $jumlah, int $dibayar): string
    {
        if ($dibayar >= $jumlah && $jumlah > 0) {
            return self::STATUS_PAID;
        }

        if ($dibayar > 0) {
            return self::STATUS_PARTIAL;
        }

        return self::STATUS_UNPAID;
    }

    /**
     * Scope piutang pelanggan (kasbon).
     *
     * @param  Builder<Debt>  $query
     * @return Builder<Debt>
     */
    #[Scope]
    protected function receivable(Builder $query): Builder
    {
        return $query->where('jenis', self::TYPE_RECEIVABLE);
    }

    /**
     * Scope hutang supplier.
     *
     * @param  Builder<Debt>  $query
     * @return Builder<Debt>
     */
    #[Scope]
    protected function payable(Builder $query): Builder
    {
        return $query->where('jenis', self::TYPE_PAYABLE);
    }

    public function isReceivable(): bool
    {
        return $this->jenis === self::TYPE_RECEIVABLE;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID || $this->dibayar >= $this->jumlah;
    }

    public function remainingAmount(): int
    {
        return max(0, $this->jumlah - $this->dibayar);
    }

    public function progressPercentage(): float|int
    {
        if ($this->jumlah <= 0) {
            return 100;
        }

        return min(100, round(($this->dibayar / $this->jumlah) * 100, 1));
    }

    public function isOverdue(): bool
    {
        if ($this->isPaid() || ! $this->jatuh_tempo) {
            return false;
        }

        return $this->jatuh_tempo->isPast();
    }

    public function statusBadgeClass(): string
    {
        if ($this->isPaid()) {
            return 'bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30';
        }

        if ($this->status === self::STATUS_PARTIAL) {
            return 'bg-amber-400/15 text-amber-300 border border-amber-400/30';
        }

        return 'bg-rose-500/15 text-rose-300 border border-rose-500/30';
    }
}

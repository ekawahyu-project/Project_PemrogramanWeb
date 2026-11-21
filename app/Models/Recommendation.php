<?php

namespace App\Models;

use Database\Factories\RecommendationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'judul',
    'isi',
    'prioritas',
    'dibuat',
])]
class Recommendation extends Model
{
    /** @use HasFactory<RecommendationFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    public const PRIORITIES = [
        'Tinggi',
        'Sedang',
        'Rendah',
    ];

    /**
     * @var array<string, string>
     */
    public const PRIORITY_BADGE_CLASSES = [
        'Tinggi' => 'bg-rose-500/15 text-rose-300 border border-rose-500/30',
        'Sedang' => 'bg-[#C4FF0D]/15 text-[#C4FF0D] border border-[#C4FF0D]/30',
        'Rendah' => 'bg-white/10 text-[#8E9882] border border-white/10',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dibuat' => 'date',
        ];
    }

    /**
     * Pengguna pemilik catatan rekomendasi.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priorityBadgeClass(): string
    {
        return self::PRIORITY_BADGE_CLASSES[$this->prioritas] ?? 'bg-gray-100 text-gray-500';
    }
}

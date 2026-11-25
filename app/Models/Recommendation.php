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
        'Tinggi' => 'bg-rose-400 text-[#0E1308]',
        'Sedang' => 'bg-[#C4FF0D] text-[#0E1308]',
        'Rendah' => 'bg-[#9AA68E] text-[#0E1308]',
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

<?php

namespace App\Models;

use Database\Factories\AiConsultationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'role',
    'content',
])]
class AiConsultation extends Model
{
    /** @use HasFactory<AiConsultationFactory> */
    use HasFactory;

    public const ROLE_USER = 'user';

    public const ROLE_MODEL = 'model';

    /**
     * Pengguna pemilik sesi konsultasi AI ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}

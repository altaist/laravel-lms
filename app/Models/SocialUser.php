<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'social_id',
        'social_user_id',
        'name',
        'img',
        'json_data'
    ];

    protected $casts = [
        'json_data' => 'array',
        'social_id' => 'integer',
        'user_id' => 'integer'
    ];

    /**
     * Получить пользователя, связанного с социальной записью
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function social(): BelongsTo
    {
        return $this->belongsTo(Social::class);
    }

    /**
     * Получить email из json_data
     */
    public function getEmailAttribute(): ?string
    {
        return $this->json_data['email'] ?? null;
    }

    /**
     * Получить телефон из json_data
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->json_data['phone'] ?? null;
    }
} 
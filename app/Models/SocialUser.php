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
        'email',
        'phone',
        'img',
        'json_data'
    ];

    protected $casts = [
        'json_data' => 'array'
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
} 
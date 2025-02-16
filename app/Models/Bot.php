<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'social_id',
        'name',
        'token',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    /**
     * Получить социальную сеть, к которой принадлежит бот
     */
    public function social(): BelongsTo
    {
        return $this->belongsTo(Social::class);
    }
}
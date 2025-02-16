<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Social extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    public function bots(): HasMany
    {
        return $this->hasMany(Bot::class);
    }

    public function socialUsers(): HasMany
    {
        return $this->hasMany(SocialUser::class);
    }
} 
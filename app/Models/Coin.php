<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = [
        'name',
        'code',
        'icon',
        'is_virtual',
        'short_name'
    ];

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }
} 
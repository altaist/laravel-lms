<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }
} 
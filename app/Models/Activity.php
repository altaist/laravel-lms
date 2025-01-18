<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'team_id',
        'description',
        'info',
        'starting_at',
        'started_at'
    ];

    protected $casts = [
        'info' => 'object',
        'starting_at' => 'datetime',
        'started_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('attached_at')
            ->withTimestamps();
    }
} 
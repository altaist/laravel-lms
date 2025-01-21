<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Activity extends Model
{
    protected $fillable = [
        'team_id',
        'description',
        'info',
        'starting_at',
        'started_at',
        'duration',
    ];

    protected $casts = [
        'info' => 'object',
        'starting_at' => 'datetime',
        'started_at' => 'datetime',
        'duration' => 'integer',
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

    /**
     * Получить время окончания activity
     */
    public function getEndingAtAttribute(): Carbon
    {
        return $this->starting_at->addMinutes($this->duration);
    }

    /**
     * Проверить, активен ли activity в данный момент
     */
    public function isActive(): bool
    {
        $now = now();
        return $now->greaterThanOrEqualTo($this->starting_at) && 
               $now->lessThan($this->ending_at);
    }
} 
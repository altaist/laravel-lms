<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'json_content',
        'json_results',
        'status',
        'starting_at',
        'started_at',
        'finished_at',
        'duration',
    ];

    protected $casts = [
        'json_content' => 'array',
        'json_results' => 'array',
        'status' => 'integer',
        'starting_at' => 'datetime:Y-m-d H:i',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration' => 'integer',
    ];

    // Константы статусов
    const STATUS_PENDING = 0;
    const STATUS_STARTED = 1;
    const STATUS_FINISHED = 2;
    const STATUS_CANCELLED = 3;

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
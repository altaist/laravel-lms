<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'type',
        'description',
        'settings',
        'json_schedule',
        'leader_id'
    ];

    protected $casts = [
        'settings' => 'object',
        'json_schedule' => 'object'
    ];

    /**
     * Получить лидера команды
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Получить задачи команды
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Получить участников команды
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class);
    }

    public function scheduleDays()
    {
        return $this->hasManyThrough(ScheduleDay::class, Schedule::class);
    }
} 
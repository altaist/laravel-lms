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
        'schedule',
        'leader_id'
    ];

    protected $casts = [
        'settings' => 'object',
        'schedule' => 'object'
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
} 
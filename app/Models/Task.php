<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    protected $fillable = [
        'name',
        'topic_id',
        'type_id',
        'content'
    ];

    protected $casts = [
        'content' => 'json'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tasks')
            ->withPivot(['answer', 'result'])
            ->withTimestamps();
    }
} 
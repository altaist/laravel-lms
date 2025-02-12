<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LoginToken extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateFor(User $user): self
    {
        // Мягкое удаление всех старых токенов пользователя
        self::where('user_id', $user->id)->delete();

        // Генерируем уникальный токен
        do {
            $token = Str::random(8);
        } while (self::withTrashed()->where('token', $token)->exists());

        // Создаем новый токен
        return self::create([
            'user_id' => $user->id,
            'token' => $token
        ]);
    }
}
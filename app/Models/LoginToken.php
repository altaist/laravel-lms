<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LoginToken extends Model
{
    protected $fillable = ['user_id', 'token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateFor(User $user): self
    {
        // Удаляем все старые токены пользователя
        self::where('user_id', $user->id)->delete();

        // Генерируем уникальный токен
        do {
            $token = Str::random(8);
        } while (self::where('token', $token)->exists());

        // Создаем новый токен
        return self::create([
            'user_id' => $user->id,
            'token' => $token
        ]);
    }
}
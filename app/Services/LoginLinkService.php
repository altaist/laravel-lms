<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginToken;

class LoginLinkService
{
    /**
     * Генерирует новую ссылку для входа
     */
    public function generateFor(User $user): string
    {
        $token = LoginToken::generateFor($user);
        return route('login.token', $token->token);
    }

    /**
     * Проверяет валидность токена и возвращает пользователя
     */
    public function validateToken(string $token): ?User
    {
        $loginToken = LoginToken::where('token', $token)->first();
        
        if (!$loginToken) {
            return null;
        }

        // Получаем пользователя и удаляем использованный токен
        $user = $loginToken->user;
        $loginToken->delete();

        return $user;
    }

    /**
     * Получает текущую активную ссылку для пользователя
     */
    public function getCurrentLink(User $user): ?string
    {
        $token = LoginToken::where('user_id', $user->id)->first();
        return $token ? route('login.token', $token->token) : null;
    }
} 
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    public function login(string $token): RedirectResponse
    {
        $loginToken = LoginToken::where('token', $token)->first();
        
        if (!$loginToken) {
            return redirect()->route('login')->withErrors([
                'email' => 'Недействительная ссылка для входа.'
            ]);
        }

        // Авторизуем пользователя
        Auth::login($loginToken->user);

        // Удаляем использованный токен
        $loginToken->delete();

        // Перенаправляем на домашнюю страницу в соответствии с ролью
        return redirect()->route($loginToken->user->getHomeRoute());
    }
}
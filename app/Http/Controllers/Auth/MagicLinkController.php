<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    protected $loginLinkService;

    public function __construct(LoginLinkService $loginLinkService)
    {
        $this->loginLinkService = $loginLinkService;
    }

    public function login(string $token): RedirectResponse
    {
        $user = $this->loginLinkService->validateToken($token);
        
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Недействительная ссылка для входа.'
            ]);
        }

        Auth::login($user);
        return redirect()->route($user->getHomeRoute());
    }
}
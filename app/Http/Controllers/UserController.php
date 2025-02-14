<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StudentRequest;
use App\Models\LoginToken;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\LoginLinkService;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected $userService;
    protected $loginLinkService;

    public function __construct(UserService $userService, LoginLinkService $loginLinkService)
    {
        $this->userService = $userService;
        $this->loginLinkService = $loginLinkService;
    }

    public function show(User $user)
    {
        return response()->json($user->load('teams'));
    }

    public function store(StudentRequest $request)
    {
        $data = $request->validated();
        
        // Генерируем email если не указан
        if (empty($data['email'])) {
            $data['email'] = Str::random(10) . '@example.com';
        }
        
        // Генерируем имя пользователя если не указано
        if (empty($data['name'])) {
            $lastName = $data['person']['lastName'] ?? '';
            $firstName = $data['person']['firstName'] ?? '';
            $data['name'] = trim($firstName . ' ' . $lastName);
        }

        // Генерируем пароль если не указан
        if (empty($data['password'])) {
            $randomPassword = Str::random(10); // Генерируем случайный пароль
            $data['password'] = Hash::make($randomPassword);
            // Можно добавить логику отправки пароля пользователю (email, SMS и т.д.)
        }

        $user = $this->userService->createOrUpdate($data);
        return response()->json($user->load('teams'), 201);
    }

    public function update(StudentRequest $request, User $user)
    {
        $data = $request->validated();
        dd($data);
        
        // Сохраняем старый email если новый не указан
        if (empty($data['email'])) {
            $data['email'] = $user->email;
        }
        
        // Сохраняем старое имя если новое не указано
        if (empty($data['name'])) {
            $data['name'] = $user->name;
        }

        // Если пароль не указан, оставляем старый
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userService->createOrUpdate($data, $user);
        return response()->json($user->load('teams'));
    }

    public function generateLoginLink(User $user): JsonResponse
    {
        $this->authorize('generateLoginLinks', User::class);
        // $this->authorize('generateLoginLinkFor', [$user]);

        $link = $this->loginLinkService->generateFor($user);
        
        return response()->json([
            'link' => $link
        ]);
    }

    public function viewLoginLinks(): JsonResponse
    {
        $this->authorize('viewLoginLinks', User::class);
        
        $links = LoginToken::with('user')->get();
        
        return response()->json($links);
    }
} 
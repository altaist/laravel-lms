<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bot;
use App\Models\Social;
use App\Models\SocialUser;
use App\Services\LoginLinkService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    protected $loginLinkService;

    public function __construct(LoginLinkService $loginLinkService)
    {
        $this->loginLinkService = $loginLinkService;
    }

    public function handleCommand(Request $request, $botToken)
    {
        $bot = Bot::where('token', $botToken)
            ->whereHas('social', function ($query) {
                $query->where('code', 'telegram');
            })
            ->firstOrFail();
            
        $telegram = new \Telegram\Bot\Api($bot->token);
        
        $update = $telegram->getWebhookUpdate();
        $message = $update->getMessage();
        $from = $message->getFrom();
        $telegramUserId = $from->getId();
        
        // Проверяем, является ли это командой start с параметром
        if ($message->getText() && preg_match('/^\/start\s+(.+)$/', $message->getText(), $matches)) {
            $startToken = $matches[1];
            $this->handleStartCommand($telegram, $bot, $telegramUserId, $message, $startToken);
        } else {
            $this->handleStartCommand($telegram, $bot, $telegramUserId, $message, null);
        }
    }

    private function handleStartCommand($telegram, Bot $bot, $telegramUserId, $message, ?string $startToken)
    {
        $from = $message->getFrom();
        $chat = $message->getChat();
        
        // Получаем фото профиля пользователя
        $photoUrl = $this->getUserProfilePhoto($telegram, $telegramUserId, $bot->token);

        // Формируем данные о пользователе
        $userData = [
            'telegram_id' => $telegramUserId,
            'first_name' => $from->getFirstName(),
            'last_name' => $from->getLastName(),
            'username' => $from->getUsername(),
            'language_code' => $from->getLanguageCode(),
            'is_premium' => $from->getIsPremium() ?? false,
            'chat' => [
                'id' => $chat->getId(),
                'type' => $chat->getType(),
                'title' => $chat->getTitle(),
                'username' => $chat->getUsername(),
            ],
            'photo_url' => $photoUrl,
            'bot_id' => $bot->id,
        ];

        // Формируем имя пользователя
        $name = trim($from->getFirstName() . ' ' . $from->getLastName());
        if (empty($name)) {
            $name = $from->getUsername() ?? "User{$telegramUserId}";
        }

        // Создаем или находим social_user
        $socialUser = SocialUser::firstOrCreate(
            [
                'social_id' => $bot->social_id,
                'social_user_id' => $telegramUserId,
            ],
            [
                'name' => $name,
                'img' => $photoUrl,
                'json_data' => $userData
            ]
        );

        // Если есть start токен и он валидный
        if ($startToken && ($user = $this->loginLinkService->validateToken($startToken))) {
            // Привязываем социального пользователя к найденному пользователю
            $socialUser->update(['user_id' => $user->id]);
            
            $welcomeMessage = "Аккаунт успешно привязан к Telegram!\n";
            $welcomeMessage .= "Теперь вы можете использовать бота для входа в систему.";
        } else {
            // Если пользователь еще не привязан, создаем нового
            if (!$socialUser->user_id) {
                $user = User::create([
                    'name' => $socialUser->name,
                    'email' => $telegramUserId . '@telegram.com',
                    'password' => bcrypt(Str::random(16))
                ]);
                
                $socialUser->update(['user_id' => $user->id]);
            }

            // Генерируем временную ссылку для входа
            $loginUrl = URL::temporarySignedRoute(
                'telegram.login',
                now()->addMinutes(30),
                ['social_user' => $socialUser->id]
            );

            $welcomeMessage = "Добро пожаловать, {$socialUser->name}!\n" . 
                         ($from->getUsername() ? "@{$from->getUsername()}\n\n" : "\n") .
                         "Используйте эту ссылку для входа:\n{$loginUrl}";
        }

        // Отправляем сообщение
        $telegram->sendMessage([
            'chat_id' => $chat->getId(),
            'text' => $welcomeMessage,
            'parse_mode' => 'HTML'
        ]);
    }

    private function getUserProfilePhoto($telegram, $userId, $botToken)
    {
        try {
            $photos = $telegram->getUserProfilePhotos([
                'user_id' => $userId,
                'limit' => 1
            ]);
            
            if ($photos->getTotalCount() > 0) {
                $photo = $photos->getPhotos()[0][0];
                $file = $telegram->getFile(['file_id' => $photo->getFileId()]);
                return 'https://api.telegram.org/file/bot' . $botToken . '/' . $file->getFilePath();
            }
        } catch (\Exception $e) {
            // Логирование ошибки если нужно
        }
        return null;
    }
} 
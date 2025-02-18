<?php

namespace App\Services\Social;

use App\Models\Bot;
use App\Models\User;
use App\Models\SocialUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Telegram\Bot\Api as TelegramApi;
use App\Services\UserService;

class RegistrationTelegramService
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Получение бота по токену
     */
    public function getBot(string $token): Bot
    {
        return Bot::where('token', $token)->firstOrFail();
    }

    /**
     * Обработка команды start
     */
    public function handleStartCommand(TelegramApi $telegram, Bot $bot, int $telegramUserId, array $message, ?string $startToken = null): void
    {
        $from = $message['from'];
        $chat = $message['chat'];
        
        $photoUrl = $this->getUserProfilePhoto($telegram, $telegramUserId, $bot->token);
        $userData = $this->prepareUserData($from, $chat, $telegramUserId, $photoUrl, $bot->id);
        $name = $this->formatUserName($from, $telegramUserId);

        $socialUser = $this->getOrCreateSocialUser($bot, $telegramUserId, $name, $photoUrl, $userData);
        $messageData = $this->processRegistration($socialUser, $startToken, $from['username'] ?? null);

        $telegram->sendMessage([
            'chat_id' => $chat['id'],
            'text' => $messageData['text'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($messageData['reply_markup']),
            'disable_web_page_preview' => true
        ]);
    }

    /**
     * Получение фото профиля пользователя
     */
    protected function getUserProfilePhoto(TelegramApi $telegram, int $userId, string $botToken): ?string
    {
        try {
            $photos = $telegram->getUserProfilePhotos([
                'user_id' => $userId,
                'limit' => 1
            ]);

            if (isset($photos['photos'][0][0])) {
                $photo = $photos['photos'][0][0];
                $file = $telegram->getFile(['file_id' => $photo['file_id']]);
                return "https://api.telegram.org/file/bot{$botToken}/{$file['file_path']}";
            }
        } catch (\Exception $e) {
            \Log::error('Error getting user profile photo:', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Форматирование имени пользователя
     */
    protected function formatUserName(array $from, int $telegramUserId): string 
    {
        $name = [];
        
        if (isset($from['first_name'])) {
            $name[] = $from['first_name'];
        }
        
        if (isset($from['last_name'])) {
            $name[] = $from['last_name'];
        }
        
        return !empty($name) ? implode(' ', $name) : "User{$telegramUserId}";
    }

    /**
     * Подготовка данных пользователя
     */
    protected function prepareUserData(array $from, array $chat, int $telegramUserId, ?string $photoUrl, int $botId): array
    {
        return [
            'telegram_id' => $telegramUserId,
            'first_name' => $from['first_name'] ?? null,
            'last_name' => $from['last_name'] ?? null,
            'username' => $from['username'] ?? null,
            'language_code' => $from['language_code'] ?? null,
            'chat_id' => $chat['id'] ?? null,
            'chat_type' => $chat['type'] ?? null,
            'photo_url' => $photoUrl,
            'bot_id' => $botId
        ];
    }

    /**
     * Получение или создание пользователя
     */
    protected function getOrCreateSocialUser(Bot $bot, int $telegramUserId, string $name, ?string $photoUrl, array $userData): SocialUser
    {
        \Log::info('Creating social user:', [
            'social_id' => $bot->social_id,
            'social_user_id' => (string) $telegramUserId,
            'name' => $name
        ]);

        $socialUser = SocialUser::firstOrCreate(
            [
                'social_id' => $bot->social_id,
                'social_user_id' => (string) $telegramUserId,
            ],
            [
                'name' => $name,
                'img' => $photoUrl,
                'json_data' => $userData
            ]
        );

        // Если у social_user нет привязанного пользователя и нет стартового токена,
        // создаем нового пользователя через UserService
        if (!$socialUser->user_id && empty($startToken)) {
            // Генерируем email на основе данных из Telegram
            $email = $this->generateEmail($userData);

            // Подготавливаем данные для создания пользователя
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => Str::random(16),
                'person' => [
                    'firstName' => $userData['first_name'] ?? '',
                    'lastName' => $userData['last_name'] ?? '',
                    'photo' => $photoUrl
                ]
            ];

            $user = $this->userService->createOrUpdate($userData);

            $socialUser->update([
                'user_id' => $user->id
            ]);

            \Log::info('Created new user for social user:', [
                'social_user_id' => $socialUser->id,
                'user_id' => $user->id,
                'email' => $email
            ]);
        }

        // Обновляем данные пользователя если они изменились
        if ($socialUser->name !== $name || $socialUser->img !== $photoUrl || $socialUser->json_data !== $userData) {
            $socialUser->update([
                'name' => $name,
                'img' => $photoUrl,
                'json_data' => $userData
            ]);
        }

        return $socialUser;
    }

    /**
     * Генерация email на основе данных из Telegram
     */
    protected function generateEmail(array $userData): string
    {
        // Пробуем использовать username если есть
        if (!empty($userData['username'])) {
            $emailBase = Str::slug($userData['username']);
        } else {
            // Иначе используем имя и фамилию или ID
            $parts = [];
            if (!empty($userData['first_name'])) {
                $parts[] = Str::slug($userData['first_name']);
            }
            if (!empty($userData['last_name'])) {
                $parts[] = Str::slug($userData['last_name']);
            }
            
            $emailBase = !empty($parts) 
                ? implode('.', $parts) 
                : 'user.' . $userData['telegram_id'];
        }

        // Добавляем случайное число для уникальности
        $randomString = Str::random(6);
        
        return $emailBase . '.' . $randomString . '@telegram.user';
    }

    /**
     * Обработка регистрации
     */
    protected function processRegistration(SocialUser $socialUser, ?string $startToken, ?string $username): array
    {
        if ($startToken) {
            // Обработка специального токена
            return $this->processStartToken($socialUser, $startToken);
        }

        return $this->getDefaultWelcomeMessage($socialUser, $username);
    }

    /**
     * Получение приветственного сообщения и кнопок
     */
    protected function getDefaultWelcomeMessage(SocialUser $socialUser, ?string $username): array
    {
        $name = $username ? "@{$username}" : $socialUser->name;
        
        // Генерируем временную ссылку для входа
        $loginUrl = URL::temporarySignedRoute(
            'telegram.login', 
            now()->addMinutes(30), 
            ['social_user' => $socialUser->id]
        );

        // Убеждаемся, что URL начинается с https:// и использует правильный домен
        $appUrl = config('app.url');
        if (!str_starts_with($appUrl, 'https://')) {
            $appUrl = 'https://' . parse_url($appUrl, PHP_URL_HOST);
        }
        
        $loginUrl = str_replace(['http://localhost:8000', 'http://127.0.0.1:8000'], $appUrl, $loginUrl);

        \Log::info('Generated login URL:', ['url' => $loginUrl]); // Для отладки

        return [
            'text' => "👋 Привет, {$name}!\n\n" .
                     "Для входа в систему нажмите на кнопку ниже.\n\n" .
                     "⚠️ Ссылка действительна в течение 30 минут.\n\n" .
                     "Используйте /help для получения списка доступных команд.",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '�� Войти в систему',
                            'url' => "https://t.me"
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Обработка специального токена
     */
    protected function processStartToken(SocialUser $socialUser, string $token): array
    {
        // Генерируем временную ссылку для входа
        $loginUrl = URL::temporarySignedRoute(
            'telegram.login', 
            now()->addMinutes(30), 
            ['social_user' => $socialUser->id, 'token' => $token]
        );

        // Убеждаемся, что URL начинается с https:// и использует правильный домен
        $appUrl = config('app.url');
        if (!str_starts_with($appUrl, 'https://')) {
            $appUrl = 'https://' . parse_url($appUrl, PHP_URL_HOST);
        }
        
        $loginUrl = str_replace(['http://localhost:8000', 'http://127.0.0.1:8000'], $appUrl, $loginUrl);

        \Log::info('Generated login URL with token:', ['url' => $loginUrl]); // Для отладки

        return [
            'text' => "👋 Привет!\n\n" .
                     "Для входа в систему нажмите на кнопку ниже.\n\n" .
                     "⚠️ Ссылка действительна в течение 30 минут.\n\n" .
                     "Используйте /help для получения списка команд.",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '�� Войти в систему',
                            'url' => $loginUrl
                        ]
                    ]
                ]
            ]
        ];
    }
} 
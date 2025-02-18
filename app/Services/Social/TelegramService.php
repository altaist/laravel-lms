<?php

namespace App\Services\Social;

use App\Models\Bot;
use App\Models\User;
use App\Models\SocialUser;
use App\Services\LoginLinkService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Telegram\Bot\Api as TelegramApi;

abstract class TelegramService
{
    protected $loginLinkService;

    public function __construct(LoginLinkService $loginLinkService)
    {
        $this->loginLinkService = $loginLinkService;
    }

    abstract public function handleStartCommand(TelegramApi $telegram, Bot $bot, int $telegramUserId, $message, ?string $startToken): void;

    public function getBot(string $botToken): Bot
    {
        return Bot::where('token', $botToken)
            ->whereHas('social', function ($query) {
                $query->where('code', 'telegram');
            })
            ->firstOrFail();
    }

    protected function getUserProfilePhoto(TelegramApi $telegram, int $userId, string $botToken): ?string
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

    protected function prepareUserData($from, $chat, int $telegramUserId, ?string $photoUrl, int $botId): array
    {
        return [
            'telegram_id' => $telegramUserId,
            'first_name' => $from->getFirstName(),
            'last_name' => $from->getLastName(),
            'username' => $from->getUsername(),
            'language_code' => $from->getLanguageCode(),
            'is_premium' => $from->getIsPremium() ?? false,
            'email' => $from->getUsername() ? $from->getUsername() . '@telegram.com' : null,
            'phone' => null,
            'chat' => [
                'id' => $chat->getId(),
                'type' => $chat->getType(),
                'title' => $chat->getTitle(),
                'username' => $chat->getUsername(),
            ],
            'photo_url' => $photoUrl,
            'bot_id' => $botId,
        ];
    }

    protected function formatUserName($from, int $telegramUserId): string
    {
        $name = trim($from->getFirstName() . ' ' . $from->getLastName());
        if (empty($name)) {
            $name = $from->getUsername() ?? "User{$telegramUserId}";
        }
        return $name;
    }

    protected function getOrCreateSocialUser(Bot $bot, int $telegramUserId, string $name, ?string $photoUrl, array $userData): SocialUser
    {
        return SocialUser::firstOrCreate(
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
    }

    protected function processUserAndGetMessage(SocialUser $socialUser, ?string $startToken, ?string $username): string
    {
        if ($startToken && ($user = $this->loginLinkService->validateToken($startToken))) {
            $socialUser->update(['user_id' => $user->id]);
            return "Аккаунт успешно привязан к Telegram!\nТеперь вы можете использовать бота для входа в систему.";
        }

        if (!$socialUser->user_id) {
            $user = User::create([
                'name' => $socialUser->name,
                'email' => $socialUser->email ?? $socialUser->json_data['telegram_id'] . '@telegram.com',
                'password' => bcrypt(Str::random(16))
            ]);
            
            $socialUser->update(['user_id' => $user->id]);
        }

        $loginUrl = URL::temporarySignedRoute(
            'telegram.login',
            now()->addMinutes(30),
            ['social_user' => $socialUser->id]
        );

        return "Добро пожаловать, {$socialUser->name}!\n" . 
               ($username ? "@{$username}\n\n" : "\n") .
               "Используйте эту ссылку для входа:\n{$loginUrl}";
    }

    protected function sendTelegramMessage(TelegramApi $telegram, int $chatId, string $message): void
    {
        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);
    }
} 
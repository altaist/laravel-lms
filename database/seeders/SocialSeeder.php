<?php

namespace Database\Seeders;

use App\Models\Bot;
use App\Models\Social;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SocialSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем запись для Telegram
        $telegram = Social::firstOrCreate(
            ['code' => 'telegram'],
            [
                'name' => 'Telegram',
                'is_active' => true,
                'settings' => [
                    'webhook_url' => config('app.url') . '/telegram/{botToken}/webhook',
                ]
            ]
        );

        // Получаем конфигурацию всех ботов
        $bots = config('telegram.bots', []);

        // Создаем записи для каждого бота
        foreach ($bots as $code => $botConfig) {
            if (!empty($botConfig['token'])) {
                Bot::firstOrCreate(
                    ['token' => $botConfig['token']],
                    [
                        'social_id' => $telegram->id,
                        'name' => $botConfig['name'] ?? "Telegram Bot ($code)",
                        'type' => 'telegram',
                        'username' => $botConfig['username'] ?? null,
                        'webhook_url' => $botConfig['webhook_url'] ?? config('app.url') . "/telegram/{$botConfig['token']}/webhook",
                        'is_active' => true,
                        'webhook_enabled' => true,
                        'commands' => [
                            'start' => [
                                'description' => 'Начать работу с ботом',
                                'enabled' => true
                            ],
                            'help' => [
                                'description' => 'Получить помощь',
                                'enabled' => true
                            ]
                        ],
                        'settings' => [
                            'parse_mode' => 'HTML',
                            'timeout' => 30,
                            'welcome_message' => 'Добро пожаловать! Я бот для ' . ($botConfig['name'] ?? "Telegram Bot ($code)"),
                            'error_message' => 'Произошла ошибка. Пожалуйста, попробуйте позже.',
                            'notification_enabled' => true,
                            'allowed_updates' => ['message', 'callback_query'],
                            'language' => 'ru',
                            'timezone' => 'Europe/Moscow',
                            'code' => $code,
                        ],
                        'menu_settings' => [
                            'buttons' => [
                                [
                                    'text' => 'Помощь',
                                    'command' => '/help'
                                ],
                                [
                                    'text' => 'Профиль',
                                    'command' => '/profile'
                                ]
                            ],
                            'inline_keyboard' => true,
                            'resize_keyboard' => true
                        ],
                        'description' => 'Многофункциональный бот для взаимодействия с пользователями',
                        'about' => $botConfig['name'] ?? "Telegram Bot ($code)",
                        'avatar' => null
                    ]
                );
            }
        }
    }
} 
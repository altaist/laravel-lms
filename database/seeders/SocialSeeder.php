<?php

namespace Database\Seeders;

use App\Models\Bot;
use App\Models\Social;
use Illuminate\Database\Seeder;

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

        // Создаем тестового бота для Telegram
        if (config('services.telegram.bot_token')) {
            Bot::firstOrCreate(
                ['token' => config('services.telegram.bot_token')],
                [
                    'social_id' => $telegram->id,
                    'name' => 'Test Telegram Bot',
                    'is_active' => true,
                    'settings' => [
                        'webhook_enabled' => true,
                    ]
                ]
            );
        }
    }
} 
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Bot;

class TelegramEmulateMessage extends Command
{
    protected $signature = 'telegram:emulate {message?} {--bot=} {--user-id=} {--username=}';
    protected $description = 'Emulate Telegram message for testing';

    public function handle()
    {
        $botToken = $this->option('bot') ?? Bot::first()->token;
        $userId = $this->option('user-id') ?? rand(100000, 999999);
        $username = $this->option('username') ?? 'test_user';
        $message = $this->argument('message') ?? '/start';

        $updateData = [
            'update_id' => rand(100000, 999999),
            'message' => [
                'message_id' => rand(1, 1000),
                'from' => [
                    'id' => $userId,
                    'is_bot' => false,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => $username,
                    'language_code' => 'ru'
                ],
                'chat' => [
                    'id' => $userId,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => $username,
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => $message
            ]
        ];

        try {
            // Используем локальный URL для тестирования
            $response = Http::post(
                "http://localhost:8000/telegram/{$botToken}/webhook",
                $updateData
            );

            if ($response->successful()) {
                $this->info('Message emulated successfully');
                $this->info('Response: ' . $response->body());
            } else {
                $this->error('Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
} 
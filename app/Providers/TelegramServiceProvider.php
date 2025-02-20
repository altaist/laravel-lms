<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Api as TelegramApi;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TelegramApi::class, function () {
            $telegram = new TelegramApi(config('services.telegram.bot_token'));
            $telegram->addCommands(
                array_values(config('telegram.commands', []))
            );
            return $telegram;
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/telegram.php' => config_path('telegram.php'),
        ], 'telegram-config');
    }
} 
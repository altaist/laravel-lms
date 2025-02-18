<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bots Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your Telegram bots. Each bot should have a unique
    | name and token. You can also specify default settings for all bots.
    |
    */

    'default' => env('TELEGRAM_BOT_DEFAULT', 'main'),

    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'name' => env('TELEGRAM_BOT_NAME', 'Main Bot'),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
        ],
        
        'support' => [
            'token' => env('TELEGRAM_SUPPORT_BOT_TOKEN'),
            'name' => env('TELEGRAM_SUPPORT_BOT_NAME', 'Support Bot'),
            'webhook_url' => env('TELEGRAM_SUPPORT_WEBHOOK_URL'),
        ],
        
        // Можно добавить другие боты по аналогии
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Bot Settings
    |--------------------------------------------------------------------------
    |
    | These settings will be used as defaults for all bots if not overridden
    | in the specific bot configuration above.
    |
    */
    'defaults' => [
        'parse_mode' => 'HTML',
        'timeout' => 30,
    ],
]; 
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api as TelegramApi;
use App\Models\Bot;

class TelegramWebhookSetup extends Command
{
    protected $signature = 'telegram:webhook {action=set}';
    protected $description = 'Set or remove Telegram webhook';

    public function handle()
    {
        $action = $this->argument('action');

        $bots = Bot::whereHas('social', function ($query) {
            $query->where('code', 'telegram');
        })->get();

        // dd($bots);

        foreach ($bots as $bot) {
            $telegram = new TelegramApi($bot->token);

            if ($action === 'remove') {
                $telegram->removeWebhook();
                $this->info("Webhook removed for bot: {$bot->name}");
                continue;
            }

            // Запрашиваем URL туннеля
            $tunnelUrl = $this->ask('Enter your Cloudflare tunnel URL (without trailing slash)');
            $webhookUrl = rtrim($tunnelUrl, '/') . "/telegram/{$bot->token}/webhook";

            try {
                $telegram->setWebhook([
                    'url' => $webhookUrl,
                    'allowed_updates' => ['message', 'callback_query'],
                ]);
                
                $this->info("Webhook set for bot: {$bot->name}");
                $this->info("URL: {$webhookUrl}");
            } catch (\Exception $e) {
                $this->error("Error setting webhook for bot {$bot->name}: " . $e->getMessage());
            }
        }
    }
} 
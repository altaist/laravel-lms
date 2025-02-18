<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bot;
use Telegram\Bot\Api as TelegramApi;

class TelegramWebhookInfo extends Command
{
    protected $signature = 'telegram:webhook-info {--bot=}';
    protected $description = 'Get information about registered webhook';

    public function handle()
    {
        $botToken = $this->option('bot');
        
        $bots = $botToken 
            ? Bot::where('token', $botToken)->get()
            : Bot::all();

        foreach ($bots as $bot) {
            try {
                $telegram = new TelegramApi($bot->token);
                $info = $telegram->getWebhookInfo();
                
                $this->info("\nWebhook info for bot: {$bot->name}");
                $this->info("URL: " . $info['url']);
                $this->info("Has custom certificate: " . ($info['has_custom_certificate'] ? 'Yes' : 'No'));
                $this->info("Pending update count: " . $info['pending_update_count']);
                $this->info("Last error date: " . ($info['last_error_date'] ?? 'None'));
                $this->info("Last error message: " . ($info['last_error_message'] ?? 'None'));
                $this->info("Max connections: " . ($info['max_connections'] ?? 'Default'));
                $this->info("Allowed updates: " . json_encode($info['allowed_updates'] ?? []));
            } catch (\Exception $e) {
                $this->error("Error getting webhook info for bot {$bot->name}: " . $e->getMessage());
            }
        }
    }
} 
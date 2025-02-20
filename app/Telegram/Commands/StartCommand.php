<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Services\Social\RegistrationTelegramService;
use Illuminate\Support\Facades\App;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected array $aliases = ['run'];
    protected string $description = 'Начать работу с ботом';

    public function handle(): void
    {
        $message = $this->getUpdate()->getMessage();
        $telegramUserId = $message->getFrom()->getId();
        
        $params = $this->getArguments();
        $startToken = $params[0] ?? null;

        $registrationService = App::make(RegistrationTelegramService::class);
        $registrationService->handleStartCommand(
            $this->getTelegram(),
            $this->getBot(),
            $telegramUserId,
            $message->toArray(),
            $startToken
        );
    }
} 
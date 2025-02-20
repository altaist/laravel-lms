<?php

namespace App\Services\Social;

use Telegram\Bot\Api as TelegramApi;
use App\Models\Bot;
use Illuminate\Support\Collection;

class TelegramCommandService
{
    public function __construct(
        protected TelegramApi $telegram,
        protected Bot $bot
    ) {}

    public function registerCommands(): void
    {
        $commands = config('telegram.commands', []);
        
        $this->telegram->addCommands($commands);

        $this->setTelegramCommands();
    }

    protected function setTelegramCommands(): void
    {
        $commands = collect($this->telegram->getCommands())
            ->map(fn ($command) => [
                'command' => $command->getName(),
                'description' => $command->getDescription()
            ])
            ->values()
            ->toArray();

        $this->telegram->setMyCommands(['commands' => $commands]);
    }

    public function getBot(): Bot
    {
        return $this->bot;
    }
} 
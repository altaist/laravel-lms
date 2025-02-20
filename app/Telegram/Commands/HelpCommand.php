<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = 'Список доступных команд';

    public function handle(): void
    {
        $commands = $this->telegram->getCommands();
        
        $text = "Доступные команды:\n\n";
        foreach ($commands as $name => $command) {
            $text .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }
} 
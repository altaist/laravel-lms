<?php

namespace App\Http\Controllers;

use App\Services\Social\RegistrationTelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Api as TelegramApi;
use App\Models\Bot;
use App\Services\Social\TelegramCommandService;

class TelegramBotController extends Controller
{
    protected $telegramService;

    public function __construct(RegistrationTelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обработка входящих сообщений через webhook
     */
    public function handleWebhook(string $botToken)
    {
        try {
            $bot = Bot::where('token', $botToken)->firstOrFail();
            $telegram = new TelegramApi($botToken);
            
            // Регистрируем команды
            $commandService = new TelegramCommandService($telegram, $bot);
            $commandService->registerCommands();
            
            // Обрабатываем входящее обновление
            $update = $telegram->getWebhookUpdate();
            
            // Обработка команд
            $telegram->commandsHandler(true);
            
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            \Log::error('Telegram webhook error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обработка входящих сообщений через pull
     */
    public function handlePull(Request $request, $botToken)
    {
        \Log::info('Pull request:', [
            'token' => $botToken,
            'content' => $request->getContent()
        ]);

        return $this->processUpdate($request, $botToken);
    }

    /**
     * Общий метод обработки обновлений
     */
    protected function processUpdate(Request $request, $botToken)
    {
        $bot = $this->telegramService->getBot($botToken);
        $telegram = new TelegramApi($bot->token);

        try {
            // Получаем данные из запроса в зависимости от источника
            $update = $this->parseUpdateData($request);
            
            if (!isset($update['message'])) {
                return response()->json(['status' => 'error', 'message' => 'No message found']);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            
            if (!$chatId) {
                return response()->json(['status' => 'error', 'message' => 'No chat ID found']);
            }

            // Обработка команд
            if (isset($message['text']) && strpos($message['text'], '/') === 0) {
                return $this->handleCommand($telegram, $bot, $message);
            }

            // Обработка обычных сообщений
            return $this->handleMessage($telegram, $chatId);
        } catch (\Exception $e) {
            \Log::error('Telegram processing error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Парсинг данных обновления
     */
    protected function parseUpdateData(Request $request)
    {
        if ($request->getContent()) {
            return json_decode($request->getContent(), true);
        }
        return $request->all();
    }

    /**
     * Обработка команд
     */
    protected function handleCommand(TelegramApi $telegram, $bot, array $message)
    {
        $parts = explode(' ', $message['text']);
        $command = str_replace('/', '', $parts[0]);
        $params = array_slice($parts, 1);
        $chatId = $message['chat']['id'];
        $telegramUserId = $message['from']['id'] ?? null;

        switch ($command) {
            case 'start':
                $startToken = $params[0] ?? null;
                $this->telegramService->handleStartCommand($telegram, $bot, $telegramUserId, $message, $startToken);
                break;

            case 'help':
                $helpText = "Доступные команды:\n"
                    . "/start - Начать работу с ботом\n"
                    . "/help - Показать это сообщение\n"
                    . "/debug - Показать отладочную информацию\n"
                    . "/profile - Показать ваш профиль";
                
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $helpText
                ]);
                break;

            case 'debug':
                $debugInfo = $this->formatDebugInfo($message);
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $debugInfo,
                    'parse_mode' => 'HTML'
                ]);
                break;

            default:
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Неизвестная команда. Используйте /help для получения списка команд.'
                ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Обработка обычных сообщений
     */
    protected function handleMessage(TelegramApi $telegram, $chatId)
    {
        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Используйте команды для взаимодействия с ботом. /help для получения списка команд.'
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Форматирование отладочной информации
     */
    protected function formatDebugInfo(array $message): string
    {
        $debugInfo = "🔍 Debug Information:\n\n";
        $debugInfo .= "Message ID: " . ($message['message_id'] ?? 'N/A') . "\n";
        $debugInfo .= "From User:\n";
        $debugInfo .= "- ID: " . ($message['from']['id'] ?? 'N/A') . "\n";
        $debugInfo .= "- Username: @" . ($message['from']['username'] ?? 'N/A') . "\n";
        $debugInfo .= "- First Name: " . ($message['from']['first_name'] ?? 'N/A') . "\n";
        $debugInfo .= "- Last Name: " . ($message['from']['last_name'] ?? 'N/A') . "\n";
        $debugInfo .= "\nChat:\n";
        $debugInfo .= "- ID: " . ($message['chat']['id'] ?? 'N/A') . "\n";
        $debugInfo .= "- Type: " . ($message['chat']['type'] ?? 'N/A') . "\n";
        $debugInfo .= "\nDate: " . date('Y-m-d H:i:s', $message['date'] ?? time()) . "\n";
        $debugInfo .= "Text: " . ($message['text'] ?? 'N/A') . "\n\n";
        $debugInfo .= "Raw Data:\n";
        $debugInfo .= json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $debugInfo;
    }
} 
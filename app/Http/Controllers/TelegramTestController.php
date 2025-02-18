<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Telegram\Bot\Api as TelegramApi;

class TelegramTestController extends Controller
{
    /**
     * Показ тестового интерфейса
     */
    public function testInterface()
    {
        return view('telegram.test-interface');
    }

    /**
     * Получение обновлений через pull
     */
    public function startPolling(Request $request)
    {
        $bot = Bot::where('is_active', true)->first();
        
        if (!$bot) {
            return response()->json(['error' => 'No active bot found']);
        }

        try {
            $telegram = new TelegramApi($bot->token);
            
            // Получаем обновления
            $updates = $telegram->getUpdates([
                'offset' => $request->input('offset', -1),
                'limit' => 10,
                'timeout' => 5
            ]);

            \Log::info('Polling updates:', [
                'updates_count' => count($updates),
                'offset' => $request->input('offset', -1)
            ]);

            $processedUpdates = [];
            $lastUpdateId = $request->input('offset', -1);

            foreach ($updates as $update) {
                $updateData = json_decode(json_encode($update), true);
                $lastUpdateId = max($lastUpdateId, $updateData['update_id']);

                // Создаем запрос для обработки через основной контроллер
                $pullRequest = Request::create(
                    '/telegram/' . $bot->token . '/pull',
                    'POST',
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    json_encode($updateData)
                );

                $response = app(TelegramBotController::class)->handlePull(
                    $pullRequest,
                    $bot->token
                );

                $processedUpdates[] = [
                    'update_id' => $updateData['update_id'],
                    'message' => $updateData['message'] ?? null,
                    'response' => json_decode($response->getContent(), true)
                ];
            }

            return response()->json([
                'success' => true,
                'updates' => $processedUpdates,
                'next_offset' => empty($processedUpdates) ? $lastUpdateId : $lastUpdateId + 1
            ]);

        } catch (\Exception $e) {
            \Log::error('Polling error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Восстановление webhook
     */
    public function restoreWebhook()
    {
        $bot = Bot::where('is_active', true)->first();
        
        if (!$bot) {
            return response()->json(['error' => 'No active bot found']);
        }

        try {
            $telegram = new TelegramApi($bot->token);
            
            $telegram->setWebhook([
                'url' => $bot->webhook_url
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function singlePoll(Request $request)
    {
        $bot = Bot::where('is_active', true)->first();
        
        if (!$bot) {
            return response()->json(['error' => 'No active bot found']);
        }

        try {
            $telegram = new TelegramApi($bot->token);
            
            // Получаем одно обновление
            $updates = $telegram->getUpdates([
                'offset' => $request->input('offset', -1),
                'limit' => 1, // Получаем только одно сообщение
                'timeout' => 5
            ]);

            \Log::info('Single poll request:', [
                'updates_count' => count($updates),
                'offset' => $request->input('offset', -1)
            ]);

            $processedUpdates = [];
            $lastUpdateId = $request->input('offset', -1);

            if (!empty($updates)) {
                $update = $updates[0];
                $updateData = json_decode(json_encode($update), true);
                $lastUpdateId = $updateData['update_id'];

                // Создаем запрос для обработки через основной контроллер
                $pullRequest = Request::create(
                    '/telegram/' . $bot->token . '/pull',
                    'POST',
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    json_encode($updateData)
                );

                $response = app(TelegramBotController::class)->handlePull(
                    $pullRequest,
                    $bot->token
                );

                $processedUpdates[] = [
                    'update_id' => $updateData['update_id'],
                    'message' => $updateData['message'] ?? null,
                    'response' => json_decode($response->getContent(), true)
                ];
            }

            return response()->json([
                'success' => true,
                'updates' => $processedUpdates,
                'next_offset' => empty($processedUpdates) ? $lastUpdateId : $lastUpdateId + 1
            ]);

        } catch (\Exception $e) {
            \Log::error('Single poll error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
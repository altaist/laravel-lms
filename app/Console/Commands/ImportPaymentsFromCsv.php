<?php

namespace App\Console\Commands;

use App\Enums\CoinEnum;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Payment;

class ImportPaymentsFromCsv extends Command
{
    protected $signature = 'payments:import {file=payments.csv : Путь к CSV файлу}';
    protected $description = 'Импорт платежей из CSV файла';

    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $inputFile = $this->argument('file');
        $defaultPath = database_path('data');
        
        // Если путь не абсолютный, ищем файл в папке по умолчанию
        $file = str_starts_with($inputFile, '/') 
            ? $inputFile 
            : $defaultPath . '/' . $inputFile;
        
        if (!file_exists($file)) {
            $this->error("Файл {$file} не найден");
            return 1;
        }

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);
        $row = 0;
        $errors = [];
        $successCount = 0;
        $skippedCount = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            try {
                [$date, $name, $amount, $comment] = $data;

                // Проверяем и преобразуем формат даты (из dd.mm.yyyy в Y-m-d)
                if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
                    throw new \Exception("Неверный формат даты. Ожидается DD.MM.YYYY");
                }

                $dateTime = Carbon::createFromFormat('d.m.Y', $date);
                if (!$dateTime) {
                    throw new \Exception("Ошибка преобразования даты");
                }

                // Поиск пользователя по имени
                $user = User::where('name', $name)->first();
                
                if (!$user) {
                    throw new \Exception("Пользователь {$name} не найден");
                }

                // Проверка на существующий платеж
                $existingPayment = Payment::where('user_id', $user->id)
                    ->whereDate('payment_at', $dateTime->format('Y-m-d'))
                    ->where('amount', (float) $amount)
                    ->first();

                if ($existingPayment) {
                    $skippedCount++;
                    $this->warn("Строка {$row}: Платёж для {$name} уже существует (пропущен)");
                    continue;
                }

                $paymentData = [
                    'user_id' => $user->id,
                    'author_id' => 1,
                    'coin_id' => CoinEnum::RUBLE->value,
                    'amount' => (float) $amount,
                    'description' => $comment,
                    'pay_from' => 'csv_import',
                    'payment_at' => $dateTime->format('Y-m-d H:i:s')
                ];

                $this->paymentService->create($paymentData);
                $this->info("Строка {$row}: Платёж для {$name} успешно импортирован");
                $successCount++;

            } catch (\Exception $e) {
                $errors[] = "Строка {$row}: " . $e->getMessage();
                $this->error("Ошибка в строке {$row}: " . $e->getMessage());
            }
        }

        fclose($handle);

        // Вывод итогов
        $this->newLine();
        $this->info('=== Итоги импорта ===');
        $this->info("Всего обработано строк: {$row}");
        $this->info("Успешно импортировано: {$successCount}");
        $this->warn("Пропущено дубликатов: {$skippedCount}");
        
        if (count($errors) > 0) {
            $this->error("Количество ошибок: " . count($errors));
            $this->newLine();
            $this->error('=== Список ошибок ===');
            foreach ($errors as $error) {
                $this->error($error);
            }
        } else {
            $this->info("Ошибок нет");
        }

        $this->newLine();
        $this->info('Импорт завершён');
        return count($errors) ? 1 : 0;
    }
} 
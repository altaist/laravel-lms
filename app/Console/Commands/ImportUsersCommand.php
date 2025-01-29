<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportUsersCommand extends Command
{
    protected $signature = 'app:import-users 
        {--sync : Удалить записи, которых нет в CSV}
        {--file=import.csv : Название файла для импорта}';
        
    protected $description = 'Импорт пользователей и команд из CSV файла';

    private ?int $studentRoleId = null;

    public function handle(): void
    {
        $filename = $this->option('file');
        $filepath = base_path("database/data/{$filename}");
        
        if (!file_exists($filepath)) {
            $this->error("Файл не найден: database/data/{$filename}");
            return;
        }
        
        $csvFile = fopen($filepath, "r");
        if ($csvFile === false) {
            $this->error("Не удалось открыть файл: database/data/{$filename}");
            return;
        }
        
        // Пропускаем заголовок
        $headers = fgetcsv($csvFile);
        if ($headers === false) {
            $this->error("Файл пуст или имеет неверный формат");
            fclose($csvFile);
            return;
        }
        
        // Массив для хранения обработанных ID
        $processedIds = [];
        $rowCount = 0;
        
        $this->info("Начало импорта из файла: {$filename}");
        
        while (($row = fgetcsv($csvFile)) !== false) {
            // Создаем массив данных из строки CSV
            $data = array_combine($headers, $row);
            
            // Обрабатываем пользователя
            $user = $this->processUser($data);
            
            // Сохраняем ID в список обработанных
            $processedIds[] = $user->id;
            
            // Обрабатываем команду
            $this->processTeam($data, $user);
            
            $rowCount++;
        }
        
        fclose($csvFile);

        // Если включена синхронизация, удаляем записи, которых нет в CSV
        if ($this->option('sync')) {
            $deletedCount = User::whereNotIn('id', $processedIds)
                ->where('id', '>', 10)
                ->delete();
                
            $this->info("Удалено пользователей: {$deletedCount}");
        }
        
        $this->info("Импорт завершен. Обработано строк: {$rowCount}");
    }

    // ... остальные методы остаются без изменений ...
} 
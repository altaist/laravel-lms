<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        

        $this->call([
            DictSeeder::class,          // Справочники (роли, монеты, причины)
            SystemUsersSeeder::class,    // Системные пользователи
            SystemTeamsSeeder::class,    // Системные группы
        ]);

        // Проверяем, нужно ли сидировать тестовые данные
        if ($this->command->confirm('Хотите добавить тестовые данные?', false)) {
            $this->call(TestSeeder::class);
        }

        // Спрашиваем про импорт пользователей
        if ($this->command->confirm('Хотите импортировать пользователей из import.csv?', false)) {
            $this->command->info('Начинаем импорт пользователей...');
            
            try {
                Artisan::call('app:import-users', [
                    '--clear' => true,
                    '--file' => 'import.csv'
                ], $this->command->getOutput());
                
                $this->command->info('Импорт пользователей завершен');

                // После успешного импорта пользователей спрашиваем про платежи
                if ($this->command->confirm('Хотите импортировать платежи из payments.csv?', false)) {
                    $this->command->info('Начинаем импорт платежей...');
                    
                    Artisan::call('payments:import', [
                        'file' => 'payments.csv'
                    ], $this->command->getOutput());
                    
                    $this->command->info('Импорт платежей завершен');
                }
            } catch (\Exception $e) {
                $this->command->error('Ошибка при импорте: ' . $e->getMessage());
            }
        }
    }
}

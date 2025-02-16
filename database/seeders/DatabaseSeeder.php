<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}

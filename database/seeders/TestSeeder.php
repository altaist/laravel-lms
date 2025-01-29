<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TestUsersSeeder::class,      // Тестовые пользователи
            TestTeamsSeeder::class,      // Тестовые команды
            TestActivitySeeder::class,   // Тестовые активности
        ]);
    }
} 
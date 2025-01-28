<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = fopen(base_path("database/data/import-users.csv"), "r");
        
        // Пропускаем заголовок
        $headers = fgetcsv($csvFile);
        
        while (($row = fgetcsv($csvFile)) !== false) {
            // Создаем массив данных из строки CSV
            $data = array_combine($headers, $row);
            
            // Формируем JSON для поля person
            $person = [
                'fio' => $data['person.fio'],
                'parent_tel' => $data['person.parent_tel'],
                'parent_fio' => $data['person.parent_fio'],
                'age' => (int)$data['person.age'],
                'gender' => $data['person.gender']
            ];
            
            // Генерируем email если не задан
            $email = !empty($data['email']) 
                ? $data['email'] 
                : Str::slug($data['name']) . '@example.fakeemail';
            
            // Генерируем пароль если не задан
            $password = !empty($data['password']) 
                ? $data['password'] 
                : '12345678';

            $studentRoleId = Role::where('name', 'student')->first()->id;
            // Создаем пользователя
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $studentRoleId,
                'status' => $data['status'] ?? 1,
                'person' => $person,
                'key' => Str::random(32),
                'settings' => json_encode([
                    'theme' => 'light',
                    'notifications' => true
                ]),
                'statistics' => json_encode([
                    'last_login' => now(),
                    'login_count' => 0
                ])
            ]);
            
            // Проверяем существование команды
            $team = Team::firstOrCreate(
                ['name' => $data['team_name']],
                [
                    'type' => $data['team_type'],
                    'description' => $data['team_description'],
                ]
            );
            
            // Привязываем пользователя к команде
            $team->users()->attach($user->id);
        }
        
        fclose($csvFile);
    }
} 
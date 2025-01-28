<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
//use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    private array $firstNames = [
        'Александр', 'Дмитрий', 'Максим', 'Сергей', 'Андрей', 
        'Алексей', 'Артём', 'Илья', 'Кирилл', 'Михаил',
        'Анна', 'Мария', 'Елена', 'Дарья', 'Алина'
    ];

    private array $lastNames = [
        'Иванов', 'Смирнов', 'Кузнецов', 'Попов', 'Васильев',
        'Петров', 'Соколов', 'Михайлов', 'Новиков', 'Федоров',
        'Иванова', 'Смирнова', 'Кузнецова', 'Попова', 'Васильева'
    ];

    private array $middleNames = [
        'Александрович', 'Дмитриевич', 'Максимович', 'Сергеевич', 'Андреевич',
        'Алексеевич', 'Артёмович', 'Ильич', 'Кириллович', 'Михайлович',
        'Александровна', 'Дмитриевна', 'Максимовна', 'Сергеевна', 'Андреевна'
    ];

    private array $schools = [
        'Школа №1', 'Гимназия №5', 'Лицей №3', 'Школа №10', 'Школа №15'
    ];

    private function generatePhone(): string
    {
        return '+7' . rand(900, 999) . sprintf('%07d', rand(0, 9999999));
    }

    private function generateAddress(): string
    {
        $streets = ['Ленина', 'Пушкина', 'Гагарина', 'Мира', 'Советская'];
        return "ул. {$streets[array_rand($streets)]}, д. " . rand(1, 100);
    }

    public function run()
    {
        // Очищаем таблицы
        User::truncate();
//        Person::truncate();

        // Создаем администратора (id = 1)
        $this->createUser([
            'id' => 1,
            'name' => 'Администратор',
            'email' => 'admin@example.fakeemail',
            'role_id' => Role::where('slug', 'admin')->first()->id,
            'status' => 1,
            'person' => [
                'first_name' => 'Иван',
                'last_name' => 'Администраторов',
                'birth_date' => '1990-01-01',
                'gender' => 'male',
                'shift' => 'first',
                'parent_fio' => 'Петр Петрович Администраторов',
                'parent_tel' => $this->generatePhone()
            ]
        ]);

        // Создаем менеджера (id = 2)
        $this->createUser([
            'id' => 2,
            'name' => 'Менеджер',
            'email' => 'manager@example.fakeemail',
            'role_id' => Role::where('slug', 'moderator')->first()->id,
            'status' => 1,
            'person' => [
                'first_name' => 'Петр',
                'last_name' => 'Менеджеров',
                'birth_date' => '1992-02-02',
                'gender' => 'male',
                'shift' => 'second',
                'parent_fio' => 'Иван Иванович Менеджеров',
                'parent_tel' => $this->generatePhone()
            ]
        ]);

        // Создаем учителя (id = 3)
        $this->createUser([
            'id' => 3,
            'name' => 'Учитель',
            'email' => 'alexp@robot04.ru',
            'role_id' => Role::where('slug', 'teacher')->first()->id,
            'status' => 1,
            'person' => [
                'first_name' => 'Мария',
                'last_name' => 'Учителева',
                'birth_date' => '1995-03-03',
                'gender' => 'female',
                'shift' => 'first',
                'parent_fio' => 'Сергей Сергеевич Учителев',
                'parent_tel' => $this->generatePhone()
            ]
        ]);

        // Создаем 10 учеников (id > 10)
        for ($i = 0; $i < 10; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $parentFio = $this->firstNames[array_rand($this->firstNames)] . ' ' . 
                        $this->middleNames[array_rand($this->middleNames)] . ' ' . 
                        $this->lastNames[array_rand($this->lastNames)];

            $this->createUser([
                'id' => 11 + $i,
                'name' => "{$firstName} {$lastName}",
                'email' => "student{$i}@example.fakeemail",
                'role_id' => Role::where('slug', 'student')->first()->id,
                'status' => 1,
                'person' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'birth_date' => fake()->date('Y-m-d', '-20 years'),
                    'gender' => $gender,
                    'shift' => rand(0, 1) ? 'first' : 'second',
                    'parent_fio' => $parentFio,
                    'parent_tel' => $this->generatePhone()
                ]
            ]);
        }
    }

    private function createUser(array $data): User
    {
        return User::create([
            'id' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('12345678'),
            'role_id' => $data['role_id'],
            'status' => $data['status'] ?? 1,
            'key' => \Str::random(32),
            'person' => json_encode($data['person']),
            'settings' => json_encode([
                'theme' => 'light',
                'notifications' => true
            ]),
            'statistics' => json_encode([
                'last_login' => now(),
                'login_count' => 0
            ])
        ]);
    }
} 
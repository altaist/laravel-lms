<?php

namespace Database\Seeders\Traits;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

trait UserSeederTrait
{
    protected array $firstNames = [
        'Александр', 'Дмитрий', 'Максим', 'Сергей', 'Андрей', 
        'Алексей', 'Артём', 'Илья', 'Кирилл', 'Михаил',
        'Анна', 'Мария', 'Елена', 'Дарья', 'Алина'
    ];

    protected array $lastNames = [
        'Иванов', 'Смирнов', 'Кузнецов', 'Попов', 'Васильев',
        'Петров', 'Соколов', 'Михайлов', 'Новиков', 'Федоров',
        'Иванова', 'Смирнова', 'Кузнецова', 'Попова', 'Васильева'
    ];

    protected array $middleNames = [
        'Александрович', 'Дмитриевич', 'Максимович', 'Сергеевич', 'Андреевич',
        'Алексеевич', 'Артёмович', 'Ильич', 'Кириллович', 'Михайлович',
        'Александровна', 'Дмитриевна', 'Максимовна', 'Сергеевна', 'Андреевна'
    ];

    protected function generatePhone(): string
    {
        return '+7' . rand(900, 999) . sprintf('%07d', rand(0, 9999999));
    }

    protected function generateRandomName(string $gender = null): array
    {
        $gender = $gender ?? (rand(0, 1) ? 'male' : 'female');
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        
        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => "{$firstName} {$lastName}",
            'gender' => $gender
        ];
    }

    protected function generateParentFio(): string
    {
        return $this->firstNames[array_rand($this->firstNames)] . ' ' . 
               $this->middleNames[array_rand($this->middleNames)] . ' ' . 
               $this->lastNames[array_rand($this->lastNames)];
    }

    protected function createUser(array $data): User
    {
        return User::create([
            'id' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('12345678'),
            'role_id' => $data['role_id'],
            'status' => $data['status'] ?? 1,
            'key' => Str::random(32),
            'person' => $data['person']

        ]);
    }
} 
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
//use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\Traits\UserSeederTrait;

class TestUsersSeeder extends Seeder
{
    use UserSeederTrait;

    private array $schools = [
        'Школа №1', 'Гимназия №5', 'Лицей №3', 'Школа №10', 'Школа №15'
    ];

    public function run()
    {
        // Создаем 10 учеников (id > 10)
        for ($i = 0; $i < 10; $i++) {
            $studentName = $this->generateRandomName();
            
            $this->createUser([
                'id' => 11 + $i,
                'name' => $studentName['full_name'],
                'email' => "student{$i}@example.fakeemail",
                'role_id' => Role::where('slug', 'student')->first()->id,
                'status' => 1,
                'person' => [
                    'first_name' => $studentName['first_name'],
                    'last_name' => $studentName['last_name'],
                    'birth_date' => fake()->date('Y-m-d', '-20 years'),
                    'gender' => $studentName['gender'],
                    'shift' => rand(0, 1) ? 'first' : 'second',
                    'parent_fio' => $this->generateParentFio(),
                    'parent_tel' => $this->generatePhone()
                ]
            ]);
        }
    }

    private function generatePhone(): string
    {
        return '+7' . rand(900, 999) . sprintf('%07d', rand(0, 9999999));
    }

    private function generateAddress(): string
    {
        $streets = ['Ленина', 'Пушкина', 'Гагарина', 'Мира', 'Советская'];
        return "ул. {$streets[array_rand($streets)]}, д. " . rand(1, 100);
    }
} 
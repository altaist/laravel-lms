<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['id' => 1, 'name' => 'Администратор', 'slug' => 'admin'],
            ['id' => 2, 'name' => 'Модератор', 'slug' => 'moderator'],
            ['id' => 3, 'name' => 'Учитель', 'slug' => 'teacher'],
            ['id' => 4, 'name' => 'Методист', 'slug' => 'methodist'],
            ['id' => 10, 'name' => 'Ученик', 'slug' => 'student'],
            ['id' => 100, 'name' => 'Пользователь', 'slug' => 'user'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
} 
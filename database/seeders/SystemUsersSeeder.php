<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\UserSeederTrait;

class SystemUsersSeeder extends Seeder
{
    use UserSeederTrait;

    public function run(): void
    {
        // Очищаем системных пользователей
        User::where('id', '<', 10)->delete();

        // Создаем администратора (id = 1)
        $adminName = $this->generateRandomName('male');
        $this->createUser([
            'id' => 1,
            'name' => 'Администратор',
            'email' => 'admin@example.fakeemail',
            'role_id' => Role::where('slug', 'admin')->first()->id,
            'status' => 1,
            'person' => [
                'fio' => $adminName['full_name'],
                'parent_tel' => $this->generatePhone(),
                'parent_fio' => $this->generateParentFio(),
                'age' => 30,
                'gender' => $adminName['gender']
            ]
        ]);

        // Создаем менеджера (id = 2)
        $managerName = $this->generateRandomName('male');
        $this->createUser([
            'id' => 2,
            'name' => 'Менеджер',
            'email' => 'manager@example.fakeemail',
            'role_id' => Role::where('slug', 'moderator')->first()->id,
            'status' => 1,
            'person' => [
                'fio' => $managerName['full_name'],
                'parent_tel' => $this->generatePhone(),
                'parent_fio' => $this->generateParentFio(),
                'age' => 28,
                'gender' => $managerName['gender']
            ]
        ]);

        // Создаем учителя (id = 3)
        $teacherName = $this->generateRandomName('female');
        $this->createUser([
            'id' => 3,
            'name' => 'Учитель',
            'email' => 'teacher@robot04.ru',
            'role_id' => Role::where('slug', 'teacher')->first()->id,
            'status' => 1,
            'person' => [
                'fio' => $teacherName['full_name'],
                'parent_tel' => $this->generatePhone(),
                'parent_fio' => $this->generateParentFio(),
                'age' => 25,
                'gender' => $teacherName['gender']
            ]
        ]);
    }
} 
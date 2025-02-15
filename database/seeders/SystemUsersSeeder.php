<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\UserSeederTrait;

class SystemUsersSeeder extends Seeder
{
    use UserSeederTrait;

    public function run(): void
    {
        // Очищаем системных пользователей
        User::where('id', '<', User::SYSTEM_USERS_MAX_ID)->delete();

        // Создаем администратора (id = 1)
        $adminName = $this->generateRandomName('male');
        $this->createUser([
            'id' => 1,
            'name' => 'Администратор',
            'email' => 'admin@example.fakeemail',
            'role_id' => UserRoleEnum::ADMIN->value,
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
            'role_id' => UserRoleEnum::MODERATOR->value,
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
            'role_id' => UserRoleEnum::TEACHER->value,
            'status' => 1,
            'person' => [
                'fio' => $teacherName['full_name'],
                'parent_tel' => $this->generatePhone(),
                'parent_fio' => $this->generateParentFio(),
                'age' => 25,
                'gender' => $teacherName['gender']
            ]
        ]);

        // Создаем учителя (id = 100)
        $teacherName = $this->generateRandomName('female');
        $this->createUser([
            'id' => User::SYSTEM_USERS_MAX_ID,
            'name' => 'Учитель',
            'email' => 'teacher100@robot04.ru',
            'role_id' => UserRoleEnum::TEACHER->value,
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
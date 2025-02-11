<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Coin;
use App\Models\Reason;
use Illuminate\Database\Seeder;
use App\Enums\CreditReasonEnum;
use Illuminate\Support\Facades\DB;

class DictSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем таблицы
        Role::truncate();
        Coin::truncate();
        Reason::truncate();

        // Роли
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

        // Монеты
        $coins = [
            [
                'id' => 1,
                'name' => 'Рубль',
                'short_name' => 'р',
                'code' => 'rub',
                'icon' => '',
                'is_virtual' => false
            ],
            [
                'id' => 2,
                'name' => 'Занятие',
                'short_name' => 'зан',
                'code' => 'lesson',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 3,
                'name' => 'Майнкрафтик',
                'short_name' => 'майн',
                'code' => 'mine',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 4,
                'name' => 'Знание',
                'short_name' => 'зн',
                'code' => 'knowledge',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 5,
                'name' => 'Умение',
                'short_name' => 'ум',
                'code' => 'skill',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 6,
                'name' => 'Дисциплина',
                'short_name' => 'дисц',
                'code' => 'discipline',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 7,
                'name' => 'Креатив',
                'short_name' => 'кр',
                'code' => 'creative',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 8,
                'name' => 'Дружба',
                'short_name' => 'др',
                'code' => 'friendship',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 9,
                'name' => 'Урок',
                'short_name' => 'ур',
                'code' => 'class',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 10,
                'name' => 'Дом',
                'short_name' => 'дом',
                'code' => 'home',
                'icon' => '',
                'is_virtual' => true
            ],
            [
                'id' => 11,
                'name' => 'Экстра',
                'short_name' => 'экс',
                'code' => 'extra',
                'icon' => '',
                'is_virtual' => true
            ],
        ];

        foreach ($coins as $coin) {
            Coin::create($coin);
        }

        // Причины
        DB::table('reasons')->insertOrIgnore([
            [
                'id' => CreditReasonEnum::LESSON->value,
                'name' => 'Занятие',
                'description' => 'Посещение занятия',
            ],
            [
                'id' => CreditReasonEnum::ILLNESS->value,
                'name' => 'Болезнь',
                'description' => 'Отсутствие по болезни',
            ],
            [
                'id' => CreditReasonEnum::LESSON_ACHIEVEMENT->value,
                'name' => 'Достижение на уроке',
                'description' => 'Достижения полученные во время занятия',
            ],
            [
                'id' => CreditReasonEnum::ADDITIONAL_ACHIEVEMENT->value,
                'name' => 'Дополнительное достижение',
                'description' => 'Дополнительные достижения вне занятий',
            ],
            [
                'id' => CreditReasonEnum::PAYMENT->value,
                'name' => 'Платеж',
                'description' => 'Начисление за платеж',
            ],
            [
                'id' => CreditReasonEnum::MANUAL_ADJUSTMENT->value,
                'name' => 'Ручная корректировка',
                'description' => 'Ручная корректировка баланса',
            ],
        ]);
    }
} 